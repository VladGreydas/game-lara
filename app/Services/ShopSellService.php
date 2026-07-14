<?php

namespace App\Services;

use App\Models\CargoWagon;
use App\Models\CityResource;
use App\Models\Player;
use App\Models\Wagon;
use App\Models\Weapon;
use App\Models\WeaponWagon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Throwable;

class ShopSellService
{
    /**
     * Продає вагон гравця.
     *
     * @param Player $player Поточний гравець.
     * @param Wagon $wagon Вагон, який продається.
     * @param string|null $destinationWagonId Вагон, у який переміститься зброя/вантажі
     * @throws Throwable
     */
    public function sellWagon(Player $player, Wagon $wagon, ?string $destinationWagonId = null): void
    {
        // Перевірка 1: Чи належить вагон гравцеві?
        // Це вже перевіряється в контролері, але повторна перевірка не зашкодить.
        if ($wagon->train->player_id !== $player->id) {
            throw new AuthorizationException('This wagon does not belong to your train.');
        }

        DB::transaction(function () use ($player, $wagon, $destinationWagonId) {
            $moneyEarned = $wagon->price / 2; // Ціна продажу - половина початкової ціни вагона

            // Логіка для WeaponWagon (якщо вагон типу 'weapon')
            switch ($wagon->type) {
                case 'weapon': {
                    /** @var WeaponWagon|null $weaponWagonData */
                    $weaponWagonData = $wagon->weapon_wagon;

                    // Перевіряємо, чи існує пов'язаний WeaponWagon і чи на ньому є зброя
                    if ($weaponWagonData && $weaponWagonData->weapons->isNotEmpty()) {
                        $attachedWeapons = $weaponWagonData->weapons;
                        $numAttachedWeapons = $attachedWeapons->count();

                        if ($destinationWagonId) { // Якщо вказано цільовий вагон для переміщення зброї
                            /** @var WeaponWagon|null $destinationWagon */
                            $destinationWagon = WeaponWagon::where('id', $destinationWagonId)->first();

                            if (!$destinationWagon) {
                                throw new InvalidArgumentException('Destination weapon wagon not found.');
                            }
                            if ($destinationWagon->wagon->train->player_id !== $player->id) {
                                throw new AuthorizationException('Destination weapon wagon does not belong to you.');
                            }
                            if ($destinationWagon->slots_available < $numAttachedWeapons) {
                                throw new InvalidArgumentException('Not enough free slots on the destination wagon to transfer all weapons.');
                            }

                            // Переміщуємо зброю на новий вагон
                            foreach ($attachedWeapons as $weapon) {
                                $weapon->weapon_wagon_id = $destinationWagon->id;
                                $weapon->save();
                            }
                            // Оновлюємо кількість слотів
                            $destinationWagon->slots_available -= $numAttachedWeapons;
                            $destinationWagon->save();
                        } else { // Якщо цільовий вагон не вказано, продаємо зброю разом з вагоном
                            foreach ($attachedWeapons as $weapon) {
                                $moneyEarned += $weapon->price / 2; // Додаємо половину ціни зброї
                                $weapon->delete(); // Видаляємо зброю
                            }
                        }
                    }
                    break;
                }
                case 'cargo': {
                    /** @var \App\Models\CargoWagon|null $cargoWagonData */
                    $cargoWagonData = $wagon->cargo_wagon;

                    if ($cargoWagonData && $cargoWagonData->resources->isNotEmpty()) {

                        // СЦЕНАРІЙ А: Гравець вибрав цільовий вагон для перенесення вантажу
                        if (!empty($destinationWagonId)) {
                            $targetWagon = Wagon::where('id', $destinationWagonId)->with('cargo_wagon')->first();
                            $destinationCargo = $targetWagon?->cargo_wagon;

                            if (!$destinationCargo) {
                                throw new InvalidArgumentException('Цільовий вантажний вагон не знайдено.');
                            }
                            if ($targetWagon->train->player_id !== $player->id) {
                                throw new AuthorizationException('Цільовий вагон вам не належить.');
                            }

                            // Рахуємо об'єми
                            $totalResourcesToMove = $cargoWagonData->resources->sum('quantity');
                            $destinationCurrentLoad = $destinationCargo->resources()->sum('quantity');
                            $destinationFreeSpace = $destinationCargo->capacity - $destinationCurrentLoad;

                            if ($destinationFreeSpace < $totalResourcesToMove) {
                                throw new InvalidArgumentException(
                                    "Недостатньо місця у цільовому вагоні! Потрібно слотів: {$totalResourcesToMove}, вільно: {$destinationFreeSpace}."
                                );
                            }

                            // Пересипаємо ресурси
                            foreach ($cargoWagonData->resources as $cargoResource) {
                                $existingResource = $destinationCargo->resources()
                                    ->where('resource_id', $cargoResource->resource_id)
                                    ->first();

                                if ($existingResource) {
                                    // Плюсуємо кількість, якщо такий ресурс вже є
                                    $existingResource->increment('quantity', $cargoResource->quantity);
                                    $cargoResource->delete();
                                } else {
                                    // Змінюємо прив'язку до вагона, якщо ресурсу ще немає
                                    $cargoResource->cargo_wagon_id = $destinationCargo->id;
                                    $cargoResource->save();
                                }
                            }
                        } else {
                            // СЦЕНАРІЙ Б: Цільовий вагон не вказано — стара логіка продажу місту
                            foreach ($cargoWagonData->resources as $cargoResource) {
                                $cityResource = CityResource::where('city_id', $player->city->id)
                                    ->where('resource_id', $cargoResource->resource_id)
                                    ->first();

                                if ($cityResource) {
                                    $sellPricePerUnit = $cityResource->getCurrentSellPrice();
                                    $moneyEarned += $sellPricePerUnit * $cargoResource->quantity;
                                    $cityResource->increment('quantity', $cargoResource->quantity);
                                } else {
                                    Log::info("Resource {$cargoResource->resource->name} (ID: {$cargoResource->resource_id}) from cargo wagon {$wagon->id} was lost as it cannot be sold in city {$player->city->name}.");
                                }
                                $cargoResource->delete();
                            }
                        }
                    }
                    break;
                }
                default: {
                    throw new InvalidArgumentException('Attempted to sell unsupported wagon type: ' . $wagon->type);
                }
            }

            // Зарахування грошей гравцеві
            $player->addMoney($moneyEarned);

            // Видалення вагона
            // Важливо: переконайтеся, що WeaponWagon та CargoWagon (якщо вони окремі таблиці)
            // налаштовані на CASCADE ON DELETE у міграціях,
            // щоб вони автоматично видалялися разом з основним Wagon.
            $wagon->delete();
        });
    }

    /**
     * Продає зброю гравця.
     *
     * @param Player $player Поточний гравець.
     * @param Weapon $weapon Зброя, яка продається.
     * @throws AuthorizationException Якщо зброя не належить гравцеві.
     * @throws \Exception У випадку непередбаченої помилки транзакції.
     */
    public function sellWeapon(Player $player, Weapon $weapon): void
    {
        // Перевірка 1: Чи належить зброя гравцеві?
        // Це вже перевіряється в контролері.
        if ($weapon->weapon_wagon->wagon->train->player_id !== $player->id) {
            throw new AuthorizationException('This weapon does not belong to you.');
        }

        DB::transaction(function () use ($player, $weapon) {
            $moneyEarned = $weapon->price / 2; // Ціна продажу - половина початкової ціни зброї

            // Якщо зброя була прикріплена до WeaponWagon, звільняємо слот
            if ($weapon->weapon_wagon_id) {
                $weaponWagon = $weapon->weapon_wagon;
                $weaponWagon->slots_available++; // Звільняємо один слот
                $weaponWagon->save();
            }

            // Зарахування грошей гравцеві
            $player->addMoney($moneyEarned);

            // Видалення зброї
            $weapon->delete();
        });
    }
}

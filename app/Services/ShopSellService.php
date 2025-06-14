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
    use InvalidArgumentException; // Додайте цей імпорт для помилок аргументів

class ShopSellService
{
    /**
     * Продає вагон гравця.
     *
     * @param Player $player Поточний гравець.
     * @param Wagon $wagon Вагон, який продається.
     * @param string|null $destinationWeaponWagonUuid UUID WeaponWagon, куди перемістити зброю (якщо вагон типу 'weapon').
     * @throws AuthorizationException Якщо вагон не належить гравцеві.
     * @throws InvalidArgumentException Якщо виникають проблеми з переміщенням зброї або невідомий тип вагона.
     * @throws \Exception У випадку непередбаченої помилки транзакції.
     */
    public function sellWagon(Player $player, Wagon $wagon, ?string $destinationWeaponWagonId = null): void
    {
        // Перевірка 1: Чи належить вагон гравцеві?
        // Це вже перевіряється в контролері, але повторна перевірка не зашкодить.
        if ($wagon->train->player_id !== $player->id) {
            throw new AuthorizationException('This wagon does not belong to your train.');
        }

        DB::transaction(function () use ($player, $wagon, $destinationWeaponWagonId) {
            $moneyEarned = $wagon->price / 2; // Ціна продажу - половина початкової ціни вагона

            // Логіка для WeaponWagon (якщо вагон типу 'weapon')
            if ($wagon->type === 'weapon') {
                /** @var WeaponWagon|null $weaponWagonData */
                $weaponWagonData = $wagon->weapon_wagon;

                // Перевіряємо, чи існує пов'язаний WeaponWagon і чи на ньому є зброя
                if ($weaponWagonData && $weaponWagonData->weapons->isNotEmpty()) {
                    $attachedWeapons = $weaponWagonData->weapons;
                    $numAttachedWeapons = $attachedWeapons->count();

                    if ($destinationWeaponWagonId) {
                        // Якщо вказано цільовий вагон для переміщення зброї
                        /** @var WeaponWagon|null $destinationWagon */
                        $destinationWagon = WeaponWagon::where('id', $destinationWeaponWagonId)->first();

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
                    } else {
                        // Якщо цільовий вагон не вказано, продаємо зброю разом з вагоном
                        foreach ($attachedWeapons as $weapon) {
                            $moneyEarned += $weapon->price / 2; // Додаємо половину ціни зброї
                            $weapon->delete(); // Видаляємо зброю
                        }
                    }
                }
            } elseif ($wagon->type === 'cargo') {
                /** @var CargoWagon|null $cargoWagonData */
                $cargoWagonData = $wagon->cargo_wagon; // Зв'язок має бути завантажений

                // Перевіряємо, чи існує пов'язаний CargoWagon і чи на ньому є ресурси
                if ($cargoWagonData && $cargoWagonData->resources->isNotEmpty()) {
                    foreach ($cargoWagonData->resources as $cargoResource) {
                        // Знаходимо відповідний CityResource, щоб отримати ціну продажу
                        // Потрібно перевірити, чи місто гравця купує цей ресурс
                        $cityResource = CityResource::where('city_id', $player->city->id)
                            ->where('resource_id', $cargoResource->resource_id)
                            ->first();

                        if ($cityResource) {
                            // Якщо ресурс можна продати в цьому місті, додаємо гроші
                            $sellPricePerUnit = $cityResource->getCurrentSellPrice();
                            $moneyEarned += $sellPricePerUnit * $cargoResource->quantity;

                            // Збільшуємо кількість ресурсів у місті (зворотна операція до купівлі)
                            $cityResource->increment('quantity', $cargoResource->quantity);
                            // price_multiplier оновиться автоматично
                        } else {
                            // Якщо ресурс не можна продати в цьому місті, він просто "зникає"
                            // Можна додати лог або повідомлення, якщо це неочікувана поведінка
                            \Log::info("Resource {$cargoResource->resource->name} (ID: {$cargoResource->resource_id}) from cargo wagon {$wagon->id} was lost as it cannot be sold in city {$player->city->name}.");
                        }
                        // Видаляємо запис ресурсу з вагона, незалежно від того, чи він був проданий
                        $cargoResource->delete();
                    }
                }
            } else {
                // Якщо тип вагона невідомий або не підтримується для продажу
                throw new InvalidArgumentException('Attempted to sell unsupported wagon type: ' . $wagon->type);
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

<?php

namespace App\Services;

use App\Models\CargoWagon;
use App\Models\CargoWagonResource;
use App\Models\CityResource;
use App\Models\CrafterWagon;
use App\Models\ExplorerWagon;
use App\Models\Locomotive;
use App\Models\PassengerWagon;
use App\Models\Player;
use App\Models\Resource;
use App\Models\ScrapperWagon;
use App\Models\Wagon;
use App\Models\Weapon;
use App\Models\WeaponWagon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class ShopBuyService
{
    /**
     * Купівля локомотиву з магазину.
     *
     * Старий локомотив замінюється, гравець платить різницю в ціні.
     *
     * @param Player $player Гравець, який купує.
     * @param string $uuid UUID локомотиву в сесії магазину.
     * @return Locomotive Новий локомотив.
     * @throws \Exception Якщо локомотив не знайдено або недостатньо грошей.
     */
    public function buyLocomotive(Player $player, string $uuid): Locomotive
    {
        /** @var Locomotive|null $shopLoco */
        $shopLoco = Session::get("shop.locomotives.$uuid");

        if (!$shopLoco) {
            throw new \Exception('Locomotive not available.');
        }

        /** @var Locomotive|null $currentLoco */
        $currentLoco = $player->train->locomotive;

        $currentValue = $currentLoco?->price ?? 0;
        $priceDifference = $shopLoco->price - $currentValue;

        // Apply city shop discount
        $discount = $player->city ? $player->city->getShopDiscount() : 0;
        $priceDifference = (int) round($priceDifference * (1 - $discount));

        if ($player->money < $priceDifference) {
            throw new \Exception('Not enough money, even after trading in your current locomotive.');
        }

        DB::transaction(function () use ($player, $shopLoco, $currentLoco, $priceDifference, $uuid) {
            // Видалення старого локомотива
            if ($currentLoco) {
                $currentLoco->delete();
            }

            // Купівля нового
            $newLoco = new Locomotive($shopLoco->toArray());
            $newLoco->train_id = $player->train->id;
            $newLoco->save();

            // Віднімання різниці в ціні
            $player->decrement('money', $priceDifference);

            // Видалення із сесії
            Session::forget("shop.locomotives.$uuid");
        });

        return $newLoco;
    }

    /**
     * Купівля вагона з магазину.
     *
     * @param Player $player Гравець, який купує.
     * @param string $uuid UUID вагона в сесії магазину.
     * @return Wagon Новий вагон.
     * @throws \Exception Якщо вагон не знайдено або недостатньо грошей.
     */
    public function buyWagon(Player $player, string $uuid): Wagon
    {
        /** @var Wagon|null $wagon */
        $wagon = Session::get("shop.wagons.$uuid");

        if (!$wagon) {
            throw new \Exception('Wagon not found in shop.');
        }

        // Apply city shop discount
        $discount = $player->city ? $player->city->getShopDiscount() : 0;
        $price = (int) round($wagon->price * (1 - $discount));

        if ($player->money < $price) {
            throw new \Exception('Not enough money.');
        }

        return DB::transaction(function () use ($player, $wagon, $uuid) {
            // Створення основного запису
            $wagonModel = Wagon::create([
                'train_id' => $player->train->id,
                'name' => $wagon->name,
                'type' => $wagon->type,
                'weight' => $wagon->weight,
                'armor' => $wagon->armor,
                'max_armor' => $wagon->max_armor,
                'lvl' => 1,
                'price' => $wagon->price,
                'upgrade_cost' => $wagon->upgrade_cost,
            ]);

            // Дочірній тип
            if ($wagon->type === 'cargo' && isset($wagon->cargo_data)) {
                CargoWagon::create([
                    'wagon_id' => $wagonModel->id,
                    'capacity' => $wagon->cargo_data['capacity'],
                ]);
            }

            if ($wagon->type === 'weapon' && isset($wagon->weapon_data)) {
                WeaponWagon::create([
                    'wagon_id' => $wagonModel->id,
                    'slots_available' => $wagon->weapon_data['slots_available'],
                ]);
            }

            if ($wagon->type === 'passenger') {
                PassengerWagon::create([
                    'wagon_id' => $wagonModel->id,
                ]);
            }

            if ($wagon->type === 'explorer') {
                ExplorerWagon::create([
                    'wagon_id' => $wagonModel->id,
                ]);
            }

            if ($wagon->type === 'scrapper') {
                ScrapperWagon::create([
                    'wagon_id' => $wagonModel->id,
                ]);
            }

            if ($wagon->type === 'crafter' && isset($wagon->crafter_data)) {
                CrafterWagon::create([
                    'wagon_id' => $wagonModel->id,
                    'craft_slots' => $wagon->crafter_data['craft_slots'],
                ]);
            }

            // Зняти кошти
            $player->decrement('money', $price);

            // Видалення із сесії
            Session::forget("shop.wagons.$uuid");

            return $wagonModel;
        });
    }

    /**
     * Купівля зброї з магазину.
     *
     * @param Player $player Гравець, який купує.
     * @param string $shopUuid UUID зброї в сесії магазину.
     * @param int $weaponWagonId ID збройного вагона для встановлення.
     * @return Weapon Нова зброя.
     * @throws AuthorizationException Якщо вагон не належить гравцеві.
     * @throws \Exception Якщо зброя не знайдена або недостатньо грошей.
     */
    public function buyWeapon(Player $player, string $shopUuid, int $weaponWagonId): Weapon
    {
        $weaponData = Session::get("shop.weapons.$shopUuid");

        if (!$weaponData) {
            throw new \Exception('Weapon not found in shop.');
        }

        $weaponWagon = WeaponWagon::with('wagon')->findOrFail($weaponWagonId);

        // Перевірка, чи цей вагон належить потягу гравця
        if ($weaponWagon->wagon->train_id !== $player->train->id) {
            throw new AuthorizationException('You do not own this weapon wagon.');
        }

        // Apply city shop discount
        $discount = $player->city ? $player->city->getShopDiscount() : 0;
        $price = (int) round($weaponData['price'] * (1 - $discount));

        if ($player->money < $price) {
            throw new \Exception('Not enough money.');
        }

        return DB::transaction(function () use ($player, $weaponData, $weaponWagon, $shopUuid, $price) {
            // Покупка
            $player->decrement('money', $price);

            // Створення нової зброї
            $weapon = Weapon::create([
                'name' => $weaponData['name'],
                'type' => $weaponData['type'],
                'damage' => $weaponData['damage'],
                'weight' => $weaponData['weight'],
                'price' => $weaponData['price'],
                'lvl' => 1,
                'upgrade_cost' => $weaponData['upgrade_cost'],
                'weapon_wagon_id' => $weaponWagon->id,
            ]);

            $weaponWagon->decrement('slots_available');

            // Видалення із сесії
            Session::forget("shop.weapons.$shopUuid");

            return $weapon;
        });
    }

    /**
     * Купівля ресурсів у місті.
     *
     * @param Player $player Гравець, який купує.
     * @param int $cityResourceId ID запису city_resources.
     * @param int $quantity Кількість для покупки.
     * @throws AuthorizationException Якщо ресурс не доступний у поточному місті.
     * @throws \Exception Якщо недостатньо ресурсів, грошей або місця.
     */
    public function buyResource(Player $player, int $cityResourceId, int $quantity, string $resourceSlug): void
    {
        $city = $player->city;

        $cityResource = CityResource::with('resource')->findOrFail($cityResourceId);

        // Перевірка, чи CityResource належить поточному місту гравця
        if ($cityResource->city_id !== $city->id) {
            throw new AuthorizationException('This resource is not available in your current city.');
        }

        // Перевірка, чи ресурс в URL відповідає ресурсу з city_resource_id
        if ($cityResource->resource->slug !== $resourceSlug) {
            throw new \Exception('Mismatched resource ID and slug.');
        }

        // Перевірка наявності достатньої кількості в місті
        if ($quantity > $cityResource->quantity) {
            throw new \Exception('Not enough ' . $cityResource->resource->name . ' available in the city.');
        }

        // Розрахунок вартості
        $totalCost = $cityResource->getCurrentBuyPrice() * $quantity;

        // Apply city shop discount
        $discount = $city->getShopDiscount();
        $totalCost = (int) round($totalCost * (1 - $discount));

        // Перевірка грошей гравця
        if ($player->money < $totalCost) {
            throw new \Exception('You do not have enough money to buy this amount of ' . $cityResource->resource->name . '.');
        }

        // Перевірка вільного місця у вантажних вагонах
        $availableCargoSpace = $this->getAvailableCargoSpace($player);

        if ($quantity > $availableCargoSpace) {
            throw new \Exception('Not enough free cargo space in your train for this amount of ' . $cityResource->resource->name . '.');
        }

        // Виконання транзакції
        DB::transaction(function () use ($player, $cityResource, $quantity, $totalCost) {
            // Зняття грошей
            $player->decrement('money', $totalCost);

            // Зменшення кількості ресурсу в місті з оновленням ціни
            $cityResource->adjustQuantity(-$quantity);

            // Додавання ресурсів до вантажних вагонів гравця
            $this->addResourcesToCargo($player, $cityResource->resource->id, $quantity);
        });
    }

    /**
     * Продаж ресурсів у місті.
     *
     * @param Player $player Гравець, який продає.
     * @param Resource $resource Ресурс для продажу.
     * @param int $quantity Кількість для продажу.
     * @throws \Exception Якщо ресурс не можна продати або недостатньо ресурсів.
     */
    public function sellResource(Player $player, Resource $resource, int $quantity): float
    {
        $city = $player->city;

        // Знаходимо CityResource для поточного міста та даного ресурсу
        $cityResource = CityResource::where('city_id', $city->id)
            ->where('resource_id', $resource->id)
            ->first();

        if (!$cityResource) {
            throw new \Exception('This resource cannot be sold in this city.');
        }

        // Перевіряємо, чи достатньо ресурсів у гравця
        $playerResourceQuantity = $this->getPlayerResourceQuantity($player, $resource->id);

        if ($quantity > $playerResourceQuantity) {
            throw new \Exception('You do not have ' . $quantity . ' ' . $resource->name . ' to sell.');
        }

        // Розрахунок вартості продажу
        $totalSellPrice = $cityResource->getCurrentSellPrice() * $quantity;

        // Виконання транзакції
        DB::transaction(function () use ($player, $cityResource, $quantity, $totalSellPrice, $resource) {
            // Додавання грошей гравцеві
            $player->increment('money', $totalSellPrice);

            // Збільшення кількості ресурсу в місті з оновленням ціни
            $cityResource->adjustQuantity($quantity);

            // Зменшення кількості ресурсів у вантажних вагонах гравця
            $this->removeResourcesFromCargo($player, $resource->id, $quantity);
        });

        return $totalSellPrice;
    }

    // -----------------------------------------------------------
    // Приватні допоміжні методи
    // -----------------------------------------------------------

    /**
     * Розраховує вільне місце у всіх вантажних вагонах гравця.
     */
    protected function getAvailableCargoSpace(Player $player): int
    {
        $availableCargoSpace = 0;

        if ($player->train) {
            foreach ($player->train->wagons as $wagon) {
                if ($wagon->isCargo() && $wagon->cargo_wagon) {
                    $availableCargoSpace += $wagon->cargo_wagon->getRemainingCapacity();
                }
            }
        }

        return $availableCargoSpace;
    }

    /**
     * Розраховує загальну кількість конкретного ресурсу у всіх вагонах гравця.
     */
    protected function getPlayerResourceQuantity(Player $player, int $resourceId): int
    {
        $totalQuantity = 0;

        if ($player->train) {
            foreach ($player->train->wagons as $wagon) {
                if ($wagon->isCargo() && $wagon->cargo_wagon) {
                    foreach ($wagon->cargo_wagon->resources as $cargoWagonResource) {
                        if ($cargoWagonResource->resource_id === $resourceId) {
                            $totalQuantity += $cargoWagonResource->quantity;
                        }
                    }
                }
            }
        }

        return $totalQuantity;
    }

    /**
     * Додає ресурси до вантажних вагонів гравця, розподіляючи по вільному місцю.
     */
    protected function addResourcesToCargo(Player $player, int $resourceId, int $quantity): void
    {
        $remainingQuantity = $quantity;

        if ($player->train) {
            $player->train->load('wagons.cargo_wagon.resources');

            foreach ($player->train->wagons as $wagon) {
                if ($wagon->isCargo() && $wagon->cargo_wagon && $remainingQuantity > 0) {
                    $cargoWagon = $wagon->cargo_wagon;
                    $wagonFreeSpace = $cargoWagon->getRemainingCapacity();

                    if ($wagonFreeSpace > 0) {
                        $quantityToAdd = min($remainingQuantity, $wagonFreeSpace);

                        $existingCargoResource = $cargoWagon->resources()
                            ->where('resource_id', $resourceId)
                            ->first();

                        if ($existingCargoResource) {
                            $existingCargoResource->increment('quantity', $quantityToAdd);
                        } else {
                            CargoWagonResource::create([
                                'cargo_wagon_id' => $cargoWagon->id,
                                'resource_id' => $resourceId,
                                'quantity' => $quantityToAdd,
                            ]);
                        }
                        $remainingQuantity -= $quantityToAdd;
                    }
                }
            }
        }

        if ($remainingQuantity > 0) {
            throw new \Exception('Failed to fully add resources to train cargo. Remaining: ' . $remainingQuantity);
        }
    }

    /**
     * Видаляє ресурси з вантажних вагонів гравця.
     */
    protected function removeResourcesFromCargo(Player $player, int $resourceId, int $quantity): void
    {
        $remainingQuantityToSell = $quantity;
        $playerCargoWagonResourcesForThisType = collect();

        if ($player->train) {
            $player->train->load(['wagons.cargo_wagon.resources' => function ($query) use ($resourceId) {
                $query->where('resource_id', $resourceId);
            }]);

            foreach ($player->train->wagons as $wagon) {
                if ($wagon->isCargo() && $wagon->cargo_wagon) {
                    foreach ($wagon->cargo_wagon->resources as $cargoWagonResource) {
                        $playerCargoWagonResourcesForThisType->push($cargoWagonResource);
                    }
                }
            }
        }

        foreach ($playerCargoWagonResourcesForThisType as $cargoWagonResource) {
            if ($remainingQuantityToSell <= 0) {
                break;
            }

            $quantityToDecrement = min($remainingQuantityToSell, $cargoWagonResource->quantity);
            $cargoWagonResource->decrement('quantity', $quantityToDecrement);
            $remainingQuantityToSell -= $quantityToDecrement;

            if ($cargoWagonResource->quantity <= 0) {
                $cargoWagonResource->delete();
            }
        }

        if ($remainingQuantityToSell > 0) {
            throw new \Exception('Failed to fully sell resources from train cargo. Remaining: ' . $remainingQuantityToSell);
        }
    }
}

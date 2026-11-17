<?php

namespace App\Services;

use App\Factories\WagonShopFactory;
use App\Models\Locomotive;
use App\Models\Weapon;
use Illuminate\Support\Str;

class ShopSetupService
{
    /**
     * Перевіряє, чи є інвентар магазину в сесії.
     *
     * @return bool
     */
    public function hasSessionInventory(): bool
    {
        // Перевіряємо наявність будь-яких ключів, що починаються з shop.
        return session()->has('shop.locomotives') ||
            session()->has('shop.wagons') ||
            session()->has('shop.weapons');
    }

    public function generateInventory(): void
    {
        $this->clearSessionInventory();
        foreach (Locomotive::factory()->makeMultipleShopLocomotives() as $locomotive) {
            $uuid = (string) Str::uuid();
            $locomotive->shop_uuid = $uuid;
            session()->put("shop.locomotives.{$locomotive->shop_uuid}", $locomotive);
        }

        foreach (WagonShopFactory::makeShopWagons() as $wagon) {
            $uuid = (string) Str::uuid();
            $wagon->shop_uuid = $uuid;
            session()->put("shop.wagons.{$wagon->shop_uuid}", $wagon);
        }


        foreach (Weapon::factory()->generateShopWeapons() as $weapon) {
            session()->put("shop.weapons.{$weapon->shop_uuid}", $weapon);
        }
    }
    /**
     * Очищає інвентар магазину з сесії.
     * Допоміжний метод, що використовується generateInventory().
     *
     * @return void
     */
    protected function clearSessionInventory(): void
    {
        session()->forget('shop.locomotives');
        session()->forget('shop.wagons');
        session()->forget('shop.weapons');
    }


    public function getInventory(): array
    {
        $locomotives = collect(session('shop.locomotives', []))->values();

        $wagons = collect(session('shop.wagons', []))->values();

        $weapons = collect(session('shop.weapons', []))->values();

        return [
            'locomotives' => $locomotives,
            'wagons' => $wagons,
            'weapons' => $weapons,
        ];
    }
}

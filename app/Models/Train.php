<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Train extends Model
{
    use HasFactory;

    public function firstCreation(): void
    {
        $this->locomotive()->create();

        $this->createFirstCargoWagon();
        $this->createFirstWeaponWagon();
    }

    public function createFirstCargoWagon(): void
    {
        $cargo_stats = CargoWagon::getFirstCargoWagonData();
        $cargo_stats['train_id'] = $this->id;
        $cargo = CargoWagon::create(['name' => $cargo_stats['name'], 'capacity' => CargoWagon::CARGO_FIRST_CAPACITY]);
        $cargo->wagon()->create($cargo_stats);
    }

    public function createFirstWeaponWagon(): void
    {
        $weapon_stats = WeaponWagon::getFirstWeaponWagonData();
        $weapon_stats['train_id'] = $this->id;
        $weapon = WeaponWagon::create();
        $weapon->wagon()->create($weapon_stats);
        $weapon = WeaponWagon::get()->last();
        $weapon->addFirstWeapon();
    }

    public function addWagon($wagon_data): array
    {
        $response = [];
        unset($wagon_data['id']);
        if($this->player->checkIfEnough($wagon_data['price'])) {
            switch ($wagon_data['type']) {
                case 'Cargo':
                {
                    $wagon_data['train_id'] = $this->id;
                    $capacity = $wagon_data['capacity'];

                    unset($wagon_data['capacity']);
                    unset($wagon_data['type']);

                    $cargo = CargoWagon::create(['name' => $wagon_data['name'], 'capacity' => $capacity]);
                    $cargo->wagon()->create($wagon_data);

                    $response['status'] = 'success';
                    $response['message'] = 'Successfully bought '.$wagon_data['name'];
                    break;
                }
                case 'Weapon':
                {
                    $wagon_data['train_id'] = $this->id;
                    $slots = $wagon_data['slots_available'];

                    unset($wagon_data['slots_available']);
                    unset($wagon_data['type']);

                    $weapon = WeaponWagon::create(['slots_available' => $slots]);
                    $weapon->wagon()->create($wagon_data);

                    $response['status'] = 'success';
                    $response['message'] = 'Successfully bought '.$wagon_data['name'];
                    break;
                }
            }
        } else {
            $response['status'] = 'failed';
            $response['message'] = 'Not enough money to buy';
        }
        return $response;
    }

    public function getWeaponWagons()
    {
        $weapon_wagons = collect();
        $wagons = $this->wagon;
        foreach ($wagons as $wagon) {
            $wagonable = $wagon->wagonable;
            if ($wagonable instanceof WeaponWagon && $wagonable->slots_available > 0) {
                $weapon_wagons->push($wagonable);
            }
        }
        return $weapon_wagons;
    }

    /*
     * Relations
     */
    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function locomotive(): HasOne
    {
        return $this->hasOne(Locomotive::class);
    }

    public function wagon(): HasMany
    {
        return $this->hasMany(Wagon::class)->with(['wagonable']);
    }
}

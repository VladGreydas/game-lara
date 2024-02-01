<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Weapon extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'damage',
        'type',
        'price',
        'lvl',
        'upgrade_cost'
    ];

    public static function getFirstWeaponData(): array
    {
        return [
            'name' => 'Milly',
            'price' => 250,
            'damage' => 100,
            'type' => 'LMG'
        ];
    }

    public function weapon_wagon(): BelongsTo
    {
        return $this->belongsTo(WeaponWagon::class);
    }
}

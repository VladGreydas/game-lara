<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class CargoWagon extends Model
{
    use HasFactory;

    const CARGO_FIRST_CAPACITY = 10;

    protected $fillable = [
        'name',
        'capacity'
    ];

    public static function getFirstCargoWagonData(): array
    {
        return [
            'name' => 'Cargo Wagon',
            'weight' => 125,
            'price' => 250,
            'train_id' => 1,
            'armor' => 500,
            'max_armor' => 500
        ];
    }

    public function wagon(): MorphOne
    {
        return $this->morphOne(Wagon::class, 'wagonable');
    }
}

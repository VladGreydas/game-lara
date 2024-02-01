<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Locomotive extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'weight',
        'type',
        'power',
        'armor',
        'armor_cap',
        'fuel',
        'fuel_cap',
        'price',
        'lvl',
        'upgrade_cost'
    ];

    public function getWagonCap(): int
    {
        return floor($this->power / 100);
    }

    public function train(): BelongsTo
    {
        return $this->belongsTo(Train::class);
    }
}

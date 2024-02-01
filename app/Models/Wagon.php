<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Wagon extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'train_id',
        'weight',
        'armor',
        'max_armor',
        'price',
        'lvl',
        'upgrade_cost'
    ];

    public function train(): BelongsTo
    {
        return $this->belongsTo(Train::class);
    }

    public function wagonable(): MorphTo
    {
        return $this->morphTo();
    }
}

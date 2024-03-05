<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Player extends Model
{
    use HasFactory;

    protected $fillable = [
        'nickname',
        'money',
        'exp',
        'max_exp',
        'lvl',
        'current_town_id'
    ];

    public function checkIfEnough($cost)
    {
        return $this->money >= $cost;
    }

    public function createTrain(): void
    {
        $this->train()->create();
        $this->train->firstCreation();
    }

    public function travel($town_id, $cost): bool
    {
        $current_fuel = $this->train->locomotive->fuel;
        if ($current_fuel >= $cost) {
            $this->update(['current_town_id' => $town_id]);
            $this->train->locomotive->update(['fuel' => $current_fuel - $cost]);
            return true;
        }
        else {
            return false;
        }
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function train() : HasOne
    {
        return $this->hasOne(Train::class);
    }

    public function town(): BelongsTo
    {
        return $this->belongsTo(Town::class, 'current_town_id');
    }
}

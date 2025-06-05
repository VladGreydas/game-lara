<?php

namespace App\Models;

use App\Services\PlayerSetupService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property string $nickname Player's nickname
 * @property int $id Player ID
 * @property int $money Amount of money a player has
 * @property int $exp Current experience points
 * @property int $max_exp Maximum experience points needed for level up
 * @property int $lvl Current player level
 * @property Train $train Player's train
 * @property User $user Player's User
 * @property City $city Player's current city
 * @property int $city_id Player's current city ID
 */
class Player extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'nickname',
        'money',
        'exp',
        'max_exp',
        'lvl',
        'city_id'
    ];

    protected static function booted(): void
    {
        static::created(function (Player $player) {
            app(PlayerSetupService::class)->setupInitialAssets($player);
        });
    }

    public function checkIfEnough($cost)
    {
        return $this->money >= $cost;
    }

    public function addMoney($money)
    {
        if ($money > 0) {
            $this->update(['money' => $this->money + $money]);
        }
    }

    public function canLevelUp(): bool
    {
        return $this->exp >= $this->max_exp;
    }


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function train() : HasOne
    {
        return $this->hasOne(Train::class);
    }

    public function city() : BelongsTo
    {
        return $this->belongsTo(City::class);
    }
}

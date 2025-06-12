<?php

namespace App\Models;

use App\Services\PlayerSetupService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property int $user_id
 * @property int $city_id
 * @property int|null $current_location_id
 * @property int $money
 * @property string $name
 * @property int $lvl
 * @property int $experience
 * @property int $hp
 * @property int $max_hp
 * @property-read User $user
 * @property-read City $city
 * @property-read Location|null $currentLocation
 * @property-read Train $train
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
        'city_id',
        'current_location_id'
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

    public function currentLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'current_location_id');
    }
}

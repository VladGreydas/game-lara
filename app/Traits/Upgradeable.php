<?php

namespace App\Traits;

use App\Models\Player;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $lvl
 * @property int $upgrade_cost
 * @property int $price
 * @property int $max_armor
 * @property int $max_fuel
 */
trait Upgradeable
{
    /**
     * @param callable $playerResolver fn(self $model): Player
     * @param array $scaleFactors [attr => perLvlFactor]
     * @param int $maxLvl
     */
    public function lvlUpWith(callable $playerResolver, array $scaleFactors, int $maxLvl = 10): bool
    {
        $player = $playerResolver($this);

        if ($player->money < $this->upgrade_cost || $this->lvl >= $maxLvl) {
            return false;
        }

        $newLvl = $this->lvl + 1;
        $player->decrement('money', $this->upgrade_cost);

        $updates = ['lvl' => $newLvl];

        foreach ($scaleFactors as $field => $factor) {
            $updates[$field] = $this->$field + ($factor * $newLvl);
        }

        $updates['upgrade_cost'] = $this->upgrade_cost + 100 * $newLvl;
        $updates['price'] = $this->price + $this->upgrade_cost;

        $this->update($updates);

        return true;
    }

    /**
     * @param callable $playerResolver fn(self $model): Player
     * @param Model $newModel
     * @param array $cloneFields
     */
    public function purchaseWith(callable $playerResolver, Model $newModel, array $cloneFields): bool
    {
        /** @var Player $player */
        $player = $playerResolver($this);
        $diff = $newModel->price - $this->price;

        if ($diff > 0 && $player->money < $diff) {
            return false;
        }

        $player->decrement('money', $diff);

        $updates = ['lvl' => 1];

        foreach ($cloneFields as $field) {
            $updates[$field] = $newModel->$field;
        }

        if (in_array('max_armor', $cloneFields)) {
            $updates['armor'] = $newModel->max_armor;
        }
        if (in_array('max_fuel', $cloneFields)) {
            $updates['fuel'] = $newModel->max_fuel;
        }

        $this->update($updates);

        return true;
    }

    public function isMaxed(int $maxLvl = 10): bool
    {
        return $this->lvl >= $maxLvl;
    }
}

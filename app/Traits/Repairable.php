<?php

namespace App\Traits;

trait Repairable
{
    public function repairWith(callable $playerResolver, string $currentField, string $maxField, int $costPerUnit = 2): bool
    {
        $player = $playerResolver($this);
        $current = $this->$currentField;
        $max = $this->$maxField;

        if ($current >= $max) {
            return false; // already fully repaired
        }

        $missing = $max - $current;
        $repairCost = $missing * $costPerUnit;

        if ($player->money < $repairCost) {
            return false;
        }

        $player->decrement('money', $repairCost);
        $this->update([$currentField => $max]);

        return true;
    }
}


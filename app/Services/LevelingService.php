<?php

namespace App\Services;

use App\Models\Player;

class LevelingService
{
    public const SCOUT_EXP_REWARD = 3;
    public const BASE_MAX_EXP = 100;
    public const EXP_CURVE_EXPONENT = 1.3;
    public const STAMINA_GAIN_PER_LEVEL = 5;
    public const MONEY_REWARD_BASE = 100;
    public const MONEY_REWARD_PER_LEVEL = 50;

    /**
     * Add experience to the player and process any resulting level-ups.
     *
     * @param Player $player
     * @param int $amount
     * @param string $source
     * @return int Number of levels gained (0 if none).
     */
    public function addExperience(Player $player, int $amount, string $source): int
    {
        if ($amount <= 0) {
            return 0;
        }

        $player->exp += $amount;

        $levelsGained = 0;

        while ($player->exp >= $player->max_exp) {
            $this->applyLevelUp($player);
            $levelsGained++;
        }

        $player->save();

        if ($levelsGained > 0) {
            session()->flash('levelup_message', __('player.level_up_message', ['level' => $player->lvl]));
        }

        return $levelsGained;
    }

    /**
     * Calculate the max_exp threshold for the given level.
     */
    protected function calculateMaxExp(int $level): int
    {
        return (int) round(self::BASE_MAX_EXP * ($level ** self::EXP_CURVE_EXPONENT));
    }

    /**
     * Apply a single level-up: increase lvl, carry over leftover exp,
     * recalculate max_exp and grant rewards.
     */
    protected function applyLevelUp(Player $player): void
    {
        $player->exp -= $player->max_exp;
        $player->lvl += 1;
        $player->max_exp = $this->calculateMaxExp($player->lvl);
        $player->max_stamina += self::STAMINA_GAIN_PER_LEVEL;
        $player->money += self::MONEY_REWARD_BASE + $player->lvl * self::MONEY_REWARD_PER_LEVEL;
    }
}

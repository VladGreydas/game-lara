<?php

namespace App\View\Components;

use App\Models\Player;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;

class PlayerInfo extends Component
{
    public Player $player;
    public int $progress;

    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        // Отримуємо поточного гравця, якщо користувач автентифікований
        $user = Auth::user();
        if ($user && $user->player) {
            $this->player = $user->player;
            // Обчислюємо прогрес для візуалізації рівня
            $this->progress = $this->calculateProgress();
        } else {
            // Можна створити "пустий" об'єкт для неавтентифікованих користувачів
            // або просто не рендерити компонент, якщо гравець не існує
            $this->player = new Player();
            $this->progress = 0;
        }
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.player-info');
    }

    /**
     * Calculate the player's level progress percentage.
     */
    private function calculateProgress(): int
    {
        if ($this->player->max_exp > 0) {
            return floor(($this->player->exp / $this->player->max_exp) * 100);
        }
        return 0;
    }
}

<?php

namespace App\View\Components;

use App\Models\Weapon;
use Closure;
use Illuminate\View\Component;
use Illuminate\Contracts\View\View;

class WeaponList extends Component
{
    public $weapons;

    public function __construct($weapons)
    {
        $this->weapons = $weapons;
    }

    public function render(): View|Closure|string
    {
        return view('components.weapon-list');
    }
}

<?php

namespace App\View\Components;

use App\Models\Wagon;
use Closure;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\Component;
use Illuminate\Contracts\View\View;

class WagonList extends Component
{
    public Collection $wagons;

    public function __construct(Collection $wagons)
    {
        $this->wagons = $wagons;
    }

    public function render(): View|Closure|string
    {
        return view('components.wagon-list');
    }
}

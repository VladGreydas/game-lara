<?php

namespace App\View\Components;

use App\Models\Wagon;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class WagonCard extends Component
{
    public bool $rename;
    public bool $upgrade;
    /**
     * Create a new component instance.
     */
    public function __construct(public Wagon $wagon, $rename = true, $upgrade = false)
    {
        $this->rename = $rename;
        $this->upgrade = $upgrade;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.wagon-card');
    }
}

<?php

namespace App\View\Components;

use App\Models\Locomotive;
use Closure;
use Illuminate\View\Component;
use Illuminate\Contracts\View\View;

class LocomotiveCard extends Component
{
    public Locomotive $locomotive;
    public bool $rename;
    public bool $upgrade;

    public function __construct(Locomotive $locomotive, $rename = true, $upgrade = false)
    {
        $this->locomotive = $locomotive;
        $this->rename = $rename;
        $this->upgrade = $upgrade;
    }

    public function render(): View|Closure|string
    {
        return view('components.locomotive-card');
    }
}

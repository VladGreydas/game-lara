<?php

namespace App\View\Components;

use App\Models\Locomotive;
use Closure;
use Illuminate\View\Component;
use Illuminate\Contracts\View\View;

class LocomotiveCard extends Component
{
    public Locomotive $locomotive;

    public function __construct(Locomotive $locomotive)
    {
        $this->locomotive = $locomotive;
    }

    public function render(): View|Closure|string
    {
        return view('components.locomotive-card');
    }
}

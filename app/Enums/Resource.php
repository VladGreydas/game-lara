<?php

namespace App\Enums;

enum Resource: string
{
    case IRON_ORE = 'iron_ore';
    case COPPER_ORE = 'copper_ore';
    case STONE = 'stone';
    case COAL = 'coal';
    case OIL = 'oil';
    case URANIUM_ORE = 'uranium_ore';
}

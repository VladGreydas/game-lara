<?php

return [

    // ─── Combat Events ──────────────────────────────────────────────────────────

    'bandit_attack' => [
        'title' => 'Bandit Attack',
        'description' => 'A group of bandits is attacking your train! You must act quickly.',
        'fight' => 'Fight back',
        'pay' => 'Pay ransom (100 gold)',
        'flee' => 'Flee (delay 30 min)',
    ],

    'robber_ambush' => [
        'title' => 'Robber Ambush',
        'description' => 'Robbers have set an ambush on the tracks ahead!',
        'fight' => 'Fight back',
        'pay' => 'Pay ransom (150 gold)',
        'ignore' => 'Ignore (lose 20% of cargo)',
    ],

    'rival_train' => [
        'title' => 'Rival Train',
        'description' => 'A rival train blocks your path. They demand you yield the tracks.',
        'fight' => 'Fight them',
        'avoid' => 'Detour (delay 1 hour)',
        'ignore' => 'Ignore (lose 15% of cargo)',
    ],

    // ─── Adventure Events ───────────────────────────────────────────────────────

    'broken_bridge' => [
        'title' => 'Broken Bridge',
        'description' => 'The bridge ahead is destroyed. You need to find a way across.',
        'repair' => 'Repair with Planks (5 Planks)',
        'repair_wood' => 'Repair with Wood (10 Wood)',
        'detour' => 'Detour (delay 2 hours)',
        'wait' => 'Wait for help (delay 6 hours)',
    ],

    'hidden_treasure' => [
        'title' => 'Hidden Treasure',
        'description' => 'You spot something glinting near the tracks. It might be treasure!',
        'explore' => 'Explore (takes time)',
        'skip' => 'Skip',
    ],

    'trading_post' => [
        'title' => 'Trading Post',
        'description' => 'A small trading post offers goods at favorable prices.',
        'buy' => 'Buy resources',
        'sell' => 'Sell resources',
        'skip' => 'Skip',
    ],

    // ─── Common ─────────────────────────────────────────────────────────────────

    'battle_placeholder' => '⚔️ Battle system is coming soon! For now, you win by default.',
    'treasure_found' => 'You found :amount :resource and :money gold!',
    'treasure_not_found' => 'Nothing was found. The search took some time.',
    'trade_buy' => 'Bought :amount :resource for :price gold.',
    'trade_sell' => 'Sold :amount :resource for :price gold.',

    // ─── UI ─────────────────────────────────────────────────────────────────────

    'type_combat' => 'Combat',
    'type_adventure' => 'Adventure',
    'travel_paused' => 'Your train is paused until you resolve this event.',
    'event_resolved' => 'Event resolved. Travel continues.',
    'invalid_choice' => 'Invalid choice. Please try again.',
    'events_remaining' => ':count events remaining',

];

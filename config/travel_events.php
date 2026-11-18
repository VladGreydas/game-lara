<?php

return [

    // ─── Combat Events ──────────────────────────────────────────────────────────

    'bandit_attack' => [
        'type' => 'combat',
        'title_key' => 'travel_events.bandit_attack.title',
        'description_key' => 'travel_events.bandit_attack.description',
        'choices' => [
            [
                'label_key' => 'travel_events.bandit_attack.fight',
                'condition' => 'has_weapon',
                'effect' => ['type' => 'battle_placeholder'],
            ],
            [
                'label_key' => 'travel_events.bandit_attack.pay',
                'condition' => 'has_money:100',
                'effect' => ['money' => -100],
            ],
            [
                'label_key' => 'travel_events.bandit_attack.flee',
                'condition' => 'none',
                'effect' => ['time' => '+0.5h'],
            ],
        ],
    ],

    'robber_ambush' => [
        'type' => 'combat',
        'title_key' => 'travel_events.robber_ambush.title',
        'description_key' => 'travel_events.robber_ambush.description',
        'choices' => [
            [
                'label_key' => 'travel_events.robber_ambush.fight',
                'condition' => 'has_weapon',
                'effect' => ['type' => 'battle_placeholder'],
            ],
            [
                'label_key' => 'travel_events.robber_ambush.pay',
                'condition' => 'has_money:150',
                'effect' => ['money' => -150],
            ],
            [
                'label_key' => 'travel_events.robber_ambush.ignore',
                'condition' => 'none',
                'effect' => ['resources_percent' => -20],
            ],
        ],
    ],

    'rival_train' => [
        'type' => 'combat',
        'title_key' => 'travel_events.rival_train.title',
        'description_key' => 'travel_events.rival_train.description',
        'choices' => [
            [
                'label_key' => 'travel_events.rival_train.fight',
                'condition' => 'has_weapon',
                'effect' => ['type' => 'battle_placeholder'],
            ],
            [
                'label_key' => 'travel_events.rival_train.avoid',
                'condition' => 'none',
                'effect' => ['time' => '+1h'],
            ],
            [
                'label_key' => 'travel_events.rival_train.ignore',
                'condition' => 'none',
                'effect' => ['resources_percent' => -15],
            ],
        ],
    ],

    // ─── Adventure Events ───────────────────────────────────────────────────────

    'broken_bridge' => [
        'type' => 'adventure',
        'title_key' => 'travel_events.broken_bridge.title',
        'description_key' => 'travel_events.broken_bridge.description',
        'choices' => [
            [
                'label_key' => 'travel_events.broken_bridge.repair',
                'condition' => 'has_resource:Planks:5',
                'effect' => ['resource' => 'Planks', 'resource_amount' => -5, 'xp' => 10],
            ],
            [
                'label_key' => 'travel_events.broken_bridge.repair_wood',
                'condition' => 'has_resource:Wood:10',
                'effect' => ['resource' => 'Wood', 'resource_amount' => -10, 'xp' => 10],
            ],
            [
                'label_key' => 'travel_events.broken_bridge.detour',
                'condition' => 'none',
                'effect' => ['time' => '+2h'],
            ],
            [
                'label_key' => 'travel_events.broken_bridge.wait',
                'condition' => 'none',
                'effect' => ['time' => '+6h'],
            ],
        ],
    ],

    'hidden_treasure' => [
        'type' => 'adventure',
        'title_key' => 'travel_events.hidden_treasure.title',
        'description_key' => 'travel_events.hidden_treasure.description',
        'choices' => [
            [
                'label_key' => 'travel_events.hidden_treasure.explore',
                'condition' => 'none',
                'effect' => ['type' => 'treasure_hunt'],
            ],
            [
                'label_key' => 'travel_events.hidden_treasure.skip',
                'condition' => 'none',
                'effect' => [],
            ],
        ],
    ],

    'trading_post' => [
        'type' => 'adventure',
        'title_key' => 'travel_events.trading_post.title',
        'description_key' => 'travel_events.trading_post.description',
        'choices' => [
            [
                'label_key' => 'travel_events.trading_post.buy',
                'condition' => 'has_money:50',
                'effect' => ['type' => 'trade_buy'],
            ],
            [
                'label_key' => 'travel_events.trading_post.sell',
                'condition' => 'has_any_resource:5',
                'effect' => ['type' => 'trade_sell'],
            ],
            [
                'label_key' => 'travel_events.trading_post.skip',
                'condition' => 'none',
                'effect' => [],
            ],
        ],
    ],

];

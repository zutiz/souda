<?php

declare(strict_types=1);

return [
    'default_costing_method' => env('INVENTORY_DEFAULT_COSTING', 'weighted_average'),

    'reservation_ttl_minutes' => env('INVENTORY_RESERVATION_TTL', 30),

    'low_stock_threshold_default' => env('INVENTORY_LOW_STOCK_DEFAULT', 10),

    'dead_stock_days' => env('INVENTORY_DEAD_STOCK_DAYS', 90),

    'expiry_alert_days' => env('INVENTORY_EXPIRY_ALERT_DAYS', 30),

    'default_picking_method' => env('INVENTORY_DEFAULT_PICKING', 'fefo'),

    'batch_tracking_enabled' => env('INVENTORY_BATCH_TRACKING', false),

    'serial_tracking_enabled' => env('INVENTORY_SERIAL_TRACKING', false),

    'warranty_period_days' => env('INVENTORY_WARRANTY_PERIOD_DAYS', 365),

    'default_lead_time_days' => env('INVENTORY_LEAD_TIME_DAYS', 7),

    'default_safety_stock' => env('INVENTORY_SAFETY_STOCK', 0),

    'sales_velocity_days' => env('INVENTORY_SALES_VELOCITY_DAYS', 30),

    'reorder_max_quantity' => env('INVENTORY_REORDER_MAX', 10000),

    'velocity_slow_threshold' => env('INVENTORY_VELOCITY_SLOW', 1.0),
    'velocity_fast_threshold' => env('INVENTORY_VELOCITY_FAST', 10.0),

    'rule_evaluation_limit' => env('INVENTORY_RULE_LIMIT', 50),

    'forecast_default_horizon_days' => env('INVENTORY_FORECAST_HORIZON', 30),
    'forecast_seasonal_period_months' => env('INVENTORY_FORECAST_SEASONAL_MONTHS', 12),
    'forecast_trend_lookback_days' => env('INVENTORY_FORECAST_TREND_LOOKBACK', 90),

    'health_score_low_stock_weight' => env('INVENTORY_HEALTH_LOW_STOCK_WEIGHT', 0.4),
    'health_score_dead_stock_weight' => env('INVENTORY_HEALTH_DEAD_STOCK_WEIGHT', 0.35),
    'health_score_velocity_weight' => env('INVENTORY_HEALTH_VELOCITY_WEIGHT', 0.25),
    'health_score_velocity_target' => env('INVENTORY_HEALTH_VELOCITY_TARGET', 10.0),

    'dashboard_default_days' => env('INVENTORY_DASHBOARD_DAYS', 30),

    'count_reference_prefix' => env('INVENTORY_COUNT_PREFIX', 'CNT'),

    'schedules' => [
        'reconcile' => env('INVENTORY_SCHEDULE_RECONCILE', '01:00'),
        'dead_stock' => env('INVENTORY_SCHEDULE_DEAD_STOCK', '23:00'),
    ],

    'reference_prefixes' => [
        'purchase_receipt' => 'PUR',
        'sale_deduction' => 'SAL',
        'return_restock' => 'RET',
        'transfer_out' => 'TRF',
        'transfer_in' => 'TRF',
        'adjustment_increase' => 'ADJ',
        'adjustment_decrease' => 'ADJ',
        'production_output' => 'PROD',
        'recipe_consumption' => 'REC',
        'reservation_deduction' => 'RSV',
        'reservation_release' => 'RSV',
        'reversal' => 'REV',
        'initial_stock' => 'INI',
    ],
];

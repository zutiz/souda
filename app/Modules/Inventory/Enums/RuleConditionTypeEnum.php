<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Enums;

enum RuleConditionTypeEnum: string
{
    case LowStock = 'low_stock';
    case DeadStock = 'dead_stock';
    case Overstock = 'overstock';
    case ExpiringBatch = 'expiring_batch';
    case SlowMoving = 'slow_moving';
    case FastMoving = 'fast_moving';

    public function label(): string
    {
        return match ($this) {
            self::LowStock => 'Low Stock',
            self::DeadStock => 'Dead Stock',
            self::Overstock => 'Overstock',
            self::ExpiringBatch => 'Expiring Batch',
            self::SlowMoving => 'Slow Moving',
            self::FastMoving => 'Fast Moving',
        };
    }

    public function defaultConfig(): array
    {
        return match ($this) {
            self::LowStock => ['threshold' => 10],
            self::DeadStock => ['days' => 90],
            self::Overstock => ['threshold' => 1000],
            self::ExpiringBatch => ['days' => 30],
            self::SlowMoving => ['velocity_threshold' => 1.0, 'days' => 90],
            self::FastMoving => ['velocity_threshold' => 10.0, 'days' => 30],
        };
    }
}

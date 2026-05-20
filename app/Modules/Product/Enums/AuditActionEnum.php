<?php

declare(strict_types=1);

namespace App\Modules\Product\Enums;

enum AuditActionEnum: string
{
    case StockReceived = 'stock_received';
    case StockDeducted = 'stock_deducted';
    case StockAdjusted = 'stock_adjusted';
    case StockTransferred = 'stock_transferred';
    case StockDamaged = 'stock_damaged';
    case StockExpired = 'stock_expired';
    case StockReserved = 'stock_reserved';
    case StockReleased = 'stock_released';

    public function label(): string
    {
        return match ($this) {
            self::StockReceived => 'Stock Received',
            self::StockDeducted => 'Stock Deducted',
            self::StockAdjusted => 'Stock Adjusted',
            self::StockTransferred => 'Stock Transferred',
            self::StockDamaged => 'Stock Damaged',
            self::StockExpired => 'Stock Expired',
            self::StockReserved => 'Stock Reserved',
            self::StockReleased => 'Stock Released',
        };
    }
}

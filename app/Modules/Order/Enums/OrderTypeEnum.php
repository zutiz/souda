<?php

declare(strict_types=1);

namespace App\Modules\Order\Enums;

enum OrderTypeEnum: string
{
    case DineIn = 'dine_in';
    case Takeaway = 'takeaway';
    case Delivery = 'delivery';
    case InStore = 'in_store';
    case Online = 'online';
    case Wholesale = 'wholesale';
    case PreOrder = 'pre_order';

    public function label(): string
    {
        return match ($this) {
            self::DineIn => 'Dine In',
            self::Takeaway => 'Takeaway',
            self::Delivery => 'Delivery',
            self::InStore => 'In Store',
            self::Online => 'Online',
            self::Wholesale => 'Wholesale',
            self::PreOrder => 'Pre-Order',
        };
    }

    public function requiresShipping(): bool
    {
        return match ($this) {
            self::Delivery, self::Online => true,
            default => false,
        };
    }
}

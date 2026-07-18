<?php

declare(strict_types=1);

namespace App\Modules\Order\Enums;

enum OrderStatusEnum: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Processing = 'processing';
    case ReadyForPickup = 'ready_for_pickup';
    case OutForDelivery = 'out_for_delivery';
    case PartiallyShipped = 'partially_shipped';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';
    case PartiallyRefunded = 'partially_refunded';
    case OnHold = 'on_hold';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Confirmed => 'Confirmed',
            self::Processing => 'Processing',
            self::ReadyForPickup => 'Ready for Pickup',
            self::OutForDelivery => 'Out for Delivery',
            self::PartiallyShipped => 'Partially Shipped',
            self::Shipped => 'Shipped',
            self::Delivered => 'Delivered',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
            self::Refunded => 'Refunded',
            self::PartiallyRefunded => 'Partially Refunded',
            self::OnHold => 'On Hold',
            self::Failed => 'Failed',
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, self::allowedTransitions()[$this->value] ?? [], true);
    }

    public static function allowedTransitions(): array
    {
        return [
            'pending' => ['confirmed', 'cancelled', 'on_hold', 'failed'],
            'confirmed' => ['processing', 'cancelled', 'on_hold'],
            'processing' => ['ready_for_pickup', 'out_for_delivery', 'shipped', 'partially_shipped', 'cancelled', 'on_hold'],
            'ready_for_pickup' => ['completed', 'cancelled'],
            'out_for_delivery' => ['delivered', 'failed', 'cancelled'],
            'partially_shipped' => ['shipped', 'out_for_delivery', 'cancelled'],
            'shipped' => ['delivered', 'partially_delivered', 'cancelled'],
            'delivered' => ['completed', 'refunded', 'partially_refunded'],
            'completed' => ['refunded', 'partially_refunded'],
            'cancelled' => [],
            'refunded' => [],
            'partially_refunded' => ['refunded'],
            'on_hold' => ['pending', 'confirmed', 'cancelled'],
            'failed' => ['pending', 'cancelled'],
        ];
    }

    public function isActive(): bool
    {
        return ! in_array($this, [self::Completed, self::Cancelled, self::Refunded, self::Failed], true);
    }

    public function isPaid(): bool
    {
        return in_array($this, [
            self::Processing, self::ReadyForPickup, self::OutForDelivery,
            self::PartiallyShipped, self::Shipped, self::Delivered,
            self::Completed,
        ], true);
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Completed, self::Cancelled, self::Refunded, self::Failed], true);
    }
}

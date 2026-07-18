<?php

declare(strict_types=1);

namespace App\Modules\Order\Enums;

enum ShipmentStatusEnum: string
{
    case Pending = 'pending';
    case LabelCreated = 'label_created';
    case PickedUp = 'picked_up';
    case InTransit = 'in_transit';
    case OutForDelivery = 'out_for_delivery';
    case Delivered = 'delivered';
    case DeliveryFailed = 'delivery_failed';
    case ReturnedToSender = 'returned_to_sender';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::LabelCreated => 'Label Created',
            self::PickedUp => 'Picked Up',
            self::InTransit => 'In Transit',
            self::OutForDelivery => 'Out for Delivery',
            self::Delivered => 'Delivered',
            self::DeliveryFailed => 'Delivery Failed',
            self::ReturnedToSender => 'Returned to Sender',
            self::Cancelled => 'Cancelled',
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, self::allowedTransitions()[$this->value] ?? [], true);
    }

    public static function allowedTransitions(): array
    {
        return [
            'pending' => ['label_created', 'picked_up', 'cancelled'],
            'label_created' => ['picked_up', 'cancelled'],
            'picked_up' => ['in_transit', 'delivered', 'delivery_failed', 'returned_to_sender'],
            'in_transit' => ['out_for_delivery', 'delivered', 'delivery_failed', 'returned_to_sender'],
            'out_for_delivery' => ['delivered', 'delivery_failed', 'returned_to_sender'],
            'delivered' => [],
            'delivery_failed' => ['out_for_delivery', 'returned_to_sender', 'cancelled'],
            'returned_to_sender' => [],
            'cancelled' => [],
        ];
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Delivered, self::ReturnedToSender, self::Cancelled], true);
    }
}

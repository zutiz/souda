<?php

declare(strict_types=1);

namespace App\Modules\Order\Services;

use App\Modules\Order\DTOs\OrderDTO;
use App\Modules\Order\Enums\OrderStatusEnum;
use App\Modules\Order\Events\OrderRefunded;
use App\Modules\Order\Exceptions\OrderNotFoundException;
use App\Modules\Order\Models\Order;
use App\Modules\Order\Models\OrderItem;
use Illuminate\Support\Facades\DB;

class RefundService
{
    public function __construct(
        protected OrderService $orderService,
    ) {}

    public function refundOrder(string $orderId, int $amount, string $reason, ?string $refundedBy = null): OrderDTO
    {
        $order = Order::with('items')->find($orderId);

        if (! $order) {
            throw new OrderNotFoundException($orderId);
        }

        return DB::transaction(function () use ($order, $amount, $reason, $refundedBy) {
            $currentRefundTotal = $order->refund_total;
            $newRefundTotal = $currentRefundTotal + $amount;

            if ($newRefundTotal > $order->grand_total) {
                throw new \InvalidArgumentException(
                    "Refund amount {$amount} exceeds remaining balance of ".($order->grand_total - $currentRefundTotal)
                );
            }

            $newDue = max(0, $order->grand_total - $order->paid_total + $newRefundTotal);

            $order->update([
                'refund_total' => $newRefundTotal,
                'due_total' => $newDue,
                'payment_status' => $newRefundTotal >= $order->grand_total ? 'refunded' : 'partially_refunded',
                'status' => $newRefundTotal >= $order->grand_total
                    ? OrderStatusEnum::Refunded->value
                    : OrderStatusEnum::PartiallyRefunded->value,
            ]);

            $order->statusHistories()->create([
                'from_status' => $order->status,
                'to_status' => $order->fresh()->status,
                'changed_by' => $refundedBy,
                'reason' => "Refund of {$amount}: {$reason}",
            ]);

            $result = $this->orderService->getOrder($orderId);

            (new OrderRefunded(
                order: $result,
                refundAmount: $amount,
                reason: $reason,
            ))->dispatch();

            return $result;
        });
    }

    public function refundItem(string $orderId, string $orderItemId, int $amount, string $reason, ?string $refundedBy = null): OrderDTO
    {
        $item = OrderItem::where('order_id', $orderId)->find($orderItemId);

        if (! $item) {
            throw new \InvalidArgumentException("Order item {$orderItemId} not found in order {$orderId}.");
        }

        return $this->refundOrder($orderId, $amount, "Item {$item->name}: {$reason}", $refundedBy);
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\Order\Services;

use App\Modules\Order\Contracts\ReceiptRenderer;
use App\Modules\Order\DTOs\OrderDTO;
use Illuminate\Support\Str;

class ThermalReceiptRenderer implements ReceiptRenderer
{
    private int $lineWidth = 32;

    public function render(OrderDTO $order, array $config = []): string
    {
        $lines = [];

        $lines[] = str_repeat('=', $this->lineWidth);
        $lines[] = str_pad($config['store_name'] ?? 'STORE', $this->lineWidth, ' ', STR_PAD_BOTH);
        $lines[] = str_repeat('=', $this->lineWidth);
        $lines[] = '';

        $lines[] = sprintf('Order: %s', $order->orderNumber);
        $lines[] = sprintf('Date:  %s', $order->placedAt->format('d/m/Y H:i'));
        $lines[] = sprintf('Type:  %s', str_replace('_', ' ', $order->orderType));
        if ($order->customerName) {
            $lines[] = sprintf('Customer: %s', $order->customerName);
        }
        $lines[] = '';

        $lines[] = str_repeat('-', $this->lineWidth);
        $lines[] = sprintf('%-2s %-18s %8s', 'Qty', 'Item', 'Total');
        $lines[] = str_repeat('-', $this->lineWidth);

        foreach ($order->lineItems as $item) {
            $lines[] = sprintf(
                '%-2dx %-18s %8s',
                $item->quantity,
                Str::limit($item->name, 18),
                number_format($item->totalPrice / 100, 2),
            );
        }

        $lines[] = str_repeat('-', $this->lineWidth);

        $lines[] = sprintf('%-24s %8s', 'Subtotal', number_format($order->subtotal / 100, 2));

        if ($order->shippingTotal > 0) {
            $lines[] = sprintf('%-24s %8s', 'Shipping', number_format($order->shippingTotal / 100, 2));
        }

        if ($order->taxTotal > 0) {
            $lines[] = sprintf('%-24s %8s', 'Tax', number_format($order->taxTotal / 100, 2));
        }

        if ($order->discountTotal > 0) {
            $lines[] = sprintf('%-24s %8s', 'Discount', '-'.number_format($order->discountTotal / 100, 2));
        }

        $lines[] = str_repeat('=', $this->lineWidth);
        $lines[] = sprintf('%-24s %8s', 'TOTAL', number_format($order->grandTotal / 100, 2));
        $lines[] = str_repeat('=', $this->lineWidth);
        $lines[] = '';

        if ($order->paidTotal > 0) {
            $lines[] = sprintf('Paid: %s', number_format($order->paidTotal / 100, 2));
            if ($order->dueTotal > 0) {
                $lines[] = sprintf('Due:  %s', number_format($order->dueTotal / 100, 2));
            }
        }

        $lines[] = $order->paymentMethod ? sprintf('Payment: %s', str_replace('_', ' ', $order->paymentMethod)) : '';
        $lines[] = sprintf('Status: %s', str_replace('_', ' ', $order->status));
        $lines[] = '';
        $lines[] = str_pad('Thank you!', $this->lineWidth, ' ', STR_PAD_BOTH);
        $lines[] = str_repeat('=', $this->lineWidth);

        return implode("\n", $lines);
    }

    public function contentType(): string
    {
        return 'text/plain';
    }
}

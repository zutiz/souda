<?php

declare(strict_types=1);

namespace App\Modules\Order\Services;

use App\Modules\Order\Contracts\ReceiptRenderer;
use App\Modules\Order\DTOs\OrderDTO;

class A4InvoiceRenderer implements ReceiptRenderer
{
    public function render(OrderDTO $order, array $config = []): string
    {
        $storeName = $config['store_name'] ?? 'Store';
        $storeAddress = $config['store_address'] ?? '';
        $storePhone = $config['store_phone'] ?? '';
        $storeEmail = $config['store_email'] ?? '';

        $itemsHtml = '';
        foreach ($order->lineItems as $item) {
            $itemsHtml .= <<<HTML
            <tr>
                <td style="padding: 8px; border-bottom: 1px solid #ddd;">{$item->name}</td>
                <td style="padding: 8px; border-bottom: 1px solid #ddd; text-align: center;">{$item->quantity}</td>
                <td style="padding: 8px; border-bottom: 1px solid #ddd; text-align: right;">{$this->format($item->unitPrice)}</td>
                <td style="padding: 8px; border-bottom: 1px solid #ddd; text-align: right;">{$this->format($item->totalPrice)}</td>
            </tr>
            HTML;
        }

        $shipAddr = $order->shippingAddress;
        $shipHtml = $shipAddr->name ? <<<HTML
        <h3 style="color: #333; margin-top: 20px;">Shipping Address</h3>
        <p>{$shipAddr->name}<br>{$shipAddr->phone}<br>{$shipAddr->addressLine1}<br>
        {$shipAddr->city}, {$shipAddr->state} {$shipAddr->postalCode}</p>
        HTML : '';

        return <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>Invoice {$order->orderNumber}</title>
            <style>
                body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 12px; color: #333; margin: 0; padding: 20px; }
                table { width: 100%; border-collapse: collapse; }
                th { background: #f5f5f5; padding: 8px; text-align: left; border-bottom: 2px solid #333; }
                .total-row td { padding: 8px; font-weight: bold; border-top: 2px solid #333; }
                .text-right { text-align: right; }
            </style>
        </head>
        <body>
            <div style="max-width: 800px; margin: 0 auto;">
                <div style="text-align: center; margin-bottom: 30px;">
                    <h1 style="margin: 0; font-size: 24px;">{$storeName}</h1>
                    <p style="margin: 4px 0;">{$storeAddress}</p>
                    <p style="margin: 4px 0;">{$storePhone} | {$storeEmail}</p>
                </div>

                <hr style="border: 1px solid #333;">

                <div style="display: flex; justify-content: space-between; margin: 20px 0;">
                    <div>
                        <strong>Invoice:</strong> {$order->orderNumber}<br>
                        <strong>Date:</strong> {$order->placedAt->format('d/m/Y H:i')}<br>
                        <strong>Type:</strong> {$order->orderType}
                    </div>
                    <div style="text-align: right;">
                        <strong>Status:</strong> {$order->status}<br>
                        <strong>Payment:</strong> {$order->paymentStatus}
                    </div>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th style="text-align: center;">Qty</th>
                            <th style="text-align: right;">Price</th>
                            <th style="text-align: right;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        {$itemsHtml}
                    </tbody>
                    <tfoot>
                        <tr class="total-row">
                            <td colspan="3" style="text-align: right;">Subtotal</td>
                            <td style="text-align: right;">{$this->format($order->subtotal)}</td>
                        </tr>
                        {$this->totalRow('Shipping', $order->shippingTotal)}
                        {$this->totalRow('Tax', $order->taxTotal)}
                        {$this->totalRow('Discount', -$order->discountTotal)}
                        <tr>
                            <td colspan="3" style="padding: 8px; text-align: right; font-size: 16px; font-weight: bold; border-top: 2px solid #000;">TOTAL</td>
                            <td style="padding: 8px; text-align: right; font-size: 16px; font-weight: bold; border-top: 2px solid #000;">{$this->format($order->grandTotal)}</td>
                        </tr>
                    </tfoot>
                </table>

                {$shipHtml}

                <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; text-align: center; color: #888; font-size: 11px;">
                    <p>Thank you for your business!</p>
                </div>
            </div>
        </body>
        </html>
        HTML;
    }

    public function contentType(): string
    {
        return 'text/html';
    }

    private function format(int $cents): string
    {
        return number_format($cents / 100, 2);
    }

    private function totalRow(string $label, int $value): string
    {
        if ($value === 0) {
            return '';
        }

        $prefix = $value < 0 ? '-' : '';
        $display = $prefix ? abs($value) : $value;

        return <<<HTML
        <tr>
            <td colspan="3" style="padding: 8px; text-align: right;">{$label}</td>
            <td style="padding: 8px; text-align: right;">{$prefix}{$this->format($display)}</td>
        </tr>
        HTML;
    }
}

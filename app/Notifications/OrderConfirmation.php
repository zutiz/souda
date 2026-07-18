<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Modules\Order\DTOs\OrderDTO;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderConfirmation extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public OrderDTO $order,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Order Confirmed — #'.$this->order->orderNumber)
            ->markdown('emails.order-confirmation', [
                'order' => $this->order,
                'lineItems' => $this->order->lineItems,
                'customerName' => $this->order->customerName,
                'customerEmail' => $this->order->customerEmail,
                'customerPhone' => $this->order->customerPhone,
                'orderNumber' => $this->order->orderNumber,
                'placedAt' => $this->order->placedAt->format('d M Y, h:i A'),
                'subtotal' => $this->formatCents($this->order->subtotal),
                'discountTotal' => $this->formatCents($this->order->discountTotal),
                'taxTotal' => $this->formatCents($this->order->taxTotal),
                'shippingTotal' => $this->formatCents($this->order->shippingTotal),
                'grandTotal' => $this->formatCents($this->order->grandTotal),
                'paymentMethod' => $this->order->paymentMethod,
                'paymentStatus' => $this->order->paymentStatus,
                'currency' => $this->order->currency,
                'address' => $this->order->shippingAddress,
            ]);

        return $message;
    }

    private function formatCents(int $cents): string
    {
        return number_format($cents / 100, 2);
    }
}

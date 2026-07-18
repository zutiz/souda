<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Modules\Order\DTOs\ShipmentDTO;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ShipmentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public ShipmentDTO $shipment,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Shipment Update — '.$this->shipment->shipmentNumber)
            ->markdown('emails.shipment-notification', [
                'shipment' => $this->shipment,
                'shipmentNumber' => $this->shipment->shipmentNumber,
                'carrier' => $this->shipment->courier,
                'trackingNumber' => $this->shipment->trackingNumber,
                'trackingUrl' => $this->shipment->trackingUrl,
                'status' => $this->shipment->status,
                'recipientName' => $this->shipment->recipientName,
                'recipientCity' => $this->shipment->recipientCity,
                'estimatedDelivery' => $this->shipment->estimatedDelivery?->format('d M Y'),
                'totalItems' => $this->shipment->totalItems,
            ]);

        return $message;
    }
}

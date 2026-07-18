@component('mail::message')
# Shipment Update

Your order is on the move!

**Shipment #{{ $shipmentNumber }}**  
**Carrier:** {{ ucfirst($carrier ?? 'N/A') }}  
**Status:** {{ ucfirst($status) }}

@if($trackingNumber)
**Tracking Number:** {{ $trackingNumber }}

@if($trackingUrl)
@component('mail::button', ['url' => $trackingUrl, 'color' => 'primary'])
Track Your Shipment
@endcomponent
@endif
@endif

@if($recipientName)
**Recipient:** {{ $recipientName }} — {{ $recipientCity ?? 'N/A' }}
@endif

**Items in this shipment:** {{ $totalItems }}

@if($estimatedDelivery)
**Estimated Delivery:** {{ $estimatedDelivery }}
@endif

@component('mail::panel')
Your shipment is {{ $status === 'delivered' ? 'on its way to you' : 'being processed' }}. You will receive another update when the status changes.
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent

@component('mail::message')
# Order Confirmed

Thank you for your order!

**Order #{{ $orderNumber }}**  
Placed on: {{ $placedAt }}

---

## Items

@component('mail::table')
| Item | Qty | Price |
|:----|:---:|:-----:|
@foreach($lineItems as $item)
| {{ $item->name }} | {{ $item->quantity }} | {{ $currency }} {{ number_format($item->totalPrice / 100, 2) }} |
@endforeach
@endcomponent

---

## Summary

| | |
|---|---|
| **Subtotal** | {{ $currency }} {{ $subtotal }} |
@if((int) $discountTotal > 0)
| **Discount** | -{{ $currency }} {{ $discountTotal }} |
@endif
| **Tax** | {{ $currency }} {{ $taxTotal }} |
| **Shipping** | {{ $currency }} {{ $shippingTotal }} |
| **Grand Total** | **{{ $currency }} {{ $grandTotal }}** |

---

@if($customerName)
**Customer:** {{ $customerName }}  
@endif
@if($customerEmail)
**Email:** {{ $customerEmail }}  
@endif
@if($customerPhone)
**Phone:** {{ $customerPhone }}  
@endif

**Payment:** {{ ucfirst($paymentMethod ?? 'N/A') }} — {{ ucfirst($paymentStatus) }}

@if($address)
@component('mail::panel')
**Shipping Address**  
{{ $address->name }}  
{{ $address->addressLine1 }}  
{{ $address->city }}, {{ $address->postalCode }}  
{{ $address->country }}
@endcomponent
@endif

Thanks,<br>
{{ config('app.name') }}
@endcomponent

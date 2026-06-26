<?php

namespace App\Modules\Billing\Events;

use App\Modules\Billing\Models\Payment;
use App\Modules\Billing\Models\Subscription;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentFailed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Payment $payment,
        public Subscription $subscription,
        public ?string $errorMessage = null,
    ) {}
}

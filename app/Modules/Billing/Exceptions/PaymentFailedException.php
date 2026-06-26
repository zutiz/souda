<?php

namespace App\Modules\Billing\Exceptions;

use Exception;

class PaymentFailedException extends Exception
{
    public function __construct(
        string $message = 'Payment processing failed.',
        public readonly ?string $gateway = null,
        public readonly ?string $transactionId = null,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }
}

<?php

namespace App\Modules\Billing\Exceptions;

use Exception;

class SubscriptionException extends Exception
{
    public function __construct(
        string $message = 'Subscription operation failed.',
        public readonly ?string $tenantId = null,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }
}

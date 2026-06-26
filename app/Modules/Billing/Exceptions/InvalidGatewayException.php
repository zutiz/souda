<?php

namespace App\Modules\Billing\Exceptions;

use Exception;

class InvalidGatewayException extends Exception
{
    public function __construct(
        string $gateway = '',
        string $message = '',
        ?\Throwable $previous = null,
    ) {
        $message = $message ?: "Payment gateway '{$gateway}' is not supported or not configured.";
        parent::__construct($message, 0, $previous);
    }
}

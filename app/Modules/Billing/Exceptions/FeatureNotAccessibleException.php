<?php

namespace App\Modules\Billing\Exceptions;

use Exception;

class FeatureNotAccessibleException extends Exception
{
    public function __construct(
        string $feature = '',
        string $message = '',
        ?\Throwable $previous = null,
    ) {
        $message = $message ?: "Feature '{$feature}' is not accessible with the current subscription.";
        parent::__construct($message, 0, $previous);
    }
}

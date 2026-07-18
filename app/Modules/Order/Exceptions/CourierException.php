<?php

declare(strict_types=1);

namespace App\Modules\Order\Exceptions;

class CourierException extends \RuntimeException
{
    public function __construct(string $message = 'Courier operation failed.', ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}

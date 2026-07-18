<?php

declare(strict_types=1);

namespace App\Modules\Order\Exceptions;

class CourierNotAvailableException extends \RuntimeException
{
    public function __construct(string $courier)
    {
        parent::__construct("Courier '{$courier}' is not available or not configured.");
    }
}

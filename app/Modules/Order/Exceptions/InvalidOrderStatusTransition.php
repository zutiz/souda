<?php

declare(strict_types=1);

namespace App\Modules\Order\Exceptions;

class InvalidOrderStatusTransition extends \RuntimeException
{
    public function __construct(string $from, string $to)
    {
        parent::__construct("Cannot transition order status from '{$from}' to '{$to}'.");
    }
}

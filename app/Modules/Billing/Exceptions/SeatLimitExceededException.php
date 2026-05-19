<?php

namespace App\Modules\Billing\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class SeatLimitExceededException extends HttpException
{
    public function __construct(
        string $message = 'Seat limit exceeded for the current plan.',
        public readonly ?int $maxSeats = null,
        public readonly ?int $currentSeats = null,
        ?\Throwable $previous = null,
    ) {
        parent::__construct(422, $message, $previous);
    }
}

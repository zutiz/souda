<?php

declare(strict_types=1);

namespace App\Modules\Product\Exceptions;

use DomainException;

class StockReservationExpiredException extends DomainException
{
    public function __construct(int $reservationId)
    {
        parent::__construct(
            message: "Stock reservation has expired: {$reservationId}",
            code: 410,
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Exceptions;

use DomainException;

class ReservationNotFoundException extends DomainException
{
    public function __construct(int $reservationId)
    {
        parent::__construct(
            message: "Stock reservation not found: {$reservationId}",
            code: 404,
        );
    }
}

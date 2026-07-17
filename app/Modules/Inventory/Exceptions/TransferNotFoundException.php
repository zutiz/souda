<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Exceptions;

use DomainException;

class TransferNotFoundException extends DomainException
{
    public function __construct(int $transferId)
    {
        parent::__construct(
            message: "Inventory transfer not found: {$transferId}",
            code: 404,
        );
    }
}

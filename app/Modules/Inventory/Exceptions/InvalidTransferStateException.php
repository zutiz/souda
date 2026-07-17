<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Exceptions;

use DomainException;

class InvalidTransferStateException extends DomainException
{
    public function __construct(
        string $message = 'Invalid transfer state for this operation',
        int $code = 422,
    ) {
        parent::__construct(message: $message, code: $code);
    }
}

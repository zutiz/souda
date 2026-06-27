<?php

declare(strict_types=1);

namespace App\Modules\Store\Exceptions;

use DomainException;

class StoreLimitExceededException extends DomainException
{
    public function __construct()
    {
        parent::__construct(
            message: 'Store limit exceeded for your current plan.',
            code: 403,
        );
    }
}

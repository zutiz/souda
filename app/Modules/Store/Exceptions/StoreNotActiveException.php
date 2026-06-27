<?php

declare(strict_types=1);

namespace App\Modules\Store\Exceptions;

use DomainException;

class StoreNotActiveException extends DomainException
{
    public function __construct(string $storeId)
    {
        parent::__construct(
            message: "Store '{$storeId}' is not active.",
            code: 403,
        );
    }
}

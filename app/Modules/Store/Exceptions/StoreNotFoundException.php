<?php

declare(strict_types=1);

namespace App\Modules\Store\Exceptions;

use DomainException;

class StoreNotFoundException extends DomainException
{
    public function __construct(string $storeId)
    {
        parent::__construct(
            message: "Store not found: {$storeId}",
            code: 404,
        );
    }
}

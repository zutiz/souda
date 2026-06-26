<?php

declare(strict_types=1);

namespace App\Modules\Product\Exceptions;

use DomainException;

class InvalidProductTypeException extends DomainException
{
    public function __construct(string $type)
    {
        parent::__construct(
            message: "Invalid product type: {$type}",
            code: 422,
        );
    }
}

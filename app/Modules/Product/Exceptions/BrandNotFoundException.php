<?php

declare(strict_types=1);

namespace App\Modules\Product\Exceptions;

use DomainException;

class BrandNotFoundException extends DomainException
{
    public function __construct(int $brandId)
    {
        parent::__construct(
            message: "Brand not found: {$brandId}",
            code: 404,
        );
    }
}

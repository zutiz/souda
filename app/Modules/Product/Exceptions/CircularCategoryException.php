<?php

declare(strict_types=1);

namespace App\Modules\Product\Exceptions;

use DomainException;

class CircularCategoryException extends DomainException
{
    public function __construct()
    {
        parent::__construct(
            message: 'Circular category reference detected. A category cannot be its own parent.',
            code: 422,
        );
    }
}

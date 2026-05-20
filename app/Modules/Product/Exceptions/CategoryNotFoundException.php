<?php

declare(strict_types=1);

namespace App\Modules\Product\Exceptions;

use DomainException;

class CategoryNotFoundException extends DomainException
{
    public function __construct(int $categoryId)
    {
        parent::__construct(
            message: "Category not found: {$categoryId}",
            code: 404,
        );
    }
}

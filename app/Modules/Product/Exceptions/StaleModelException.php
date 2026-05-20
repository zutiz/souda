<?php

declare(strict_types=1);

namespace App\Modules\Product\Exceptions;

use DomainException;

class StaleModelException extends DomainException
{
    public function __construct(string $modelClass, mixed $modelId)
    {
        parent::__construct(
            message: "Model has been modified by another process: {$modelClass}#{$modelId}",
            code: 409,
        );
    }
}

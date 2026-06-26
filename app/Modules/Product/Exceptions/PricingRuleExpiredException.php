<?php

declare(strict_types=1);

namespace App\Modules\Product\Exceptions;

use DomainException;

class PricingRuleExpiredException extends DomainException
{
    public function __construct(int $ruleId)
    {
        parent::__construct(
            message: "Pricing rule has expired: {$ruleId}",
            code: 410,
        );
    }
}

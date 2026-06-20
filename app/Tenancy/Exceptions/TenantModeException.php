<?php

namespace App\Tenancy\Exceptions;

class TenantModeException extends \RuntimeException
{
    public static function unsupportedOperation(string $message): self
    {
        return new self($message);
    }
}

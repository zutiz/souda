<?php

declare(strict_types=1);

namespace App\Modules\Onboarding\Services;

use App\Modules\Onboarding\Contracts\TenantTemplate;
use RuntimeException;

class TenantTemplateRegistry
{
    private array $templates = [];

    public function register(TenantTemplate $template): void
    {
        $this->templates[$template->businessType()] = $template;
    }

    public function get(string $businessType): ?TenantTemplate
    {
        return $this->templates[$businessType] ?? null;
    }

    public function getOrFail(string $businessType): TenantTemplate
    {
        $template = $this->get($businessType);

        if ($template === null) {
            throw new RuntimeException("Template for [{$businessType}] not found.");
        }

        return $template;
    }

    public function all(): array
    {
        return $this->templates;
    }

    public function has(string $businessType): bool
    {
        return isset($this->templates[$businessType]);
    }
}

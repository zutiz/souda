<?php

declare(strict_types=1);

namespace App\Modules\BusinessType\ValueObjects;

readonly class ModuleDefinition
{
    public function __construct(
        public string $slug,
        public string $name,
        public string $description = '',
        public string $version = '1.0.0',
        public ?string $providerClass = null,
        public array $dependencies = [],
        public array $requiredFeatures = [],
        public bool $isCore = false,
    ) {}

    public function toArray(): array
    {
        return [
            'slug' => $this->slug,
            'name' => $this->name,
            'description' => $this->description,
            'version' => $this->version,
            'provider_class' => $this->providerClass,
            'dependencies' => $this->dependencies,
            'required_features' => $this->requiredFeatures,
            'is_core' => $this->isCore,
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\BusinessType\Services;

use App\Modules\BusinessType\Contracts\IndustryPack;
use RuntimeException;

class IndustryPackRegistry
{
    private array $packs = [];

    public function register(IndustryPack $pack): void
    {
        $this->packs[$pack->slug()] = $pack;
    }

    public function get(string $slug): ?IndustryPack
    {
        return $this->packs[$slug] ?? null;
    }

    public function getOrFail(string $slug): IndustryPack
    {
        $pack = $this->get($slug);

        if ($pack === null) {
            throw new RuntimeException("Industry pack [{$slug}] is not registered.");
        }

        return $pack;
    }

    public function all(): array
    {
        return $this->packs;
    }

    public function has(string $slug): bool
    {
        return isset($this->packs[$slug]);
    }
}

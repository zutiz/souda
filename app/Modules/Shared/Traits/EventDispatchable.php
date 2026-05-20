<?php

declare(strict_types=1);

namespace App\Modules\Shared\Traits;

use Illuminate\Support\Facades\Event;

trait EventDispatchable
{
    public function dispatch(): void
    {
        Event::dispatch($this);
    }

    public function dispatchIf(bool $condition): void
    {
        if ($condition) {
            $this->dispatch();
        }
    }

    public function dispatchUnless(bool $condition): void
    {
        if (! $condition) {
            $this->dispatch();
        }
    }
}

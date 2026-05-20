<?php

declare(strict_types=1);

namespace App\Providers;

use App\Modules\Order\Events\OrderCancelled;
use App\Modules\Order\Events\OrderCreated;
use App\Modules\Product\Listeners\DeductProductStock;
use App\Modules\Product\Listeners\RestoreProductStock;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class OrderServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Event::listen(OrderCreated::class, DeductProductStock::class);
        Event::listen(OrderCancelled::class, RestoreProductStock::class);
    }
}

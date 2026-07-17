<?php

declare(strict_types=1);

namespace App\Providers;

use App\Modules\Inventory\Listeners\DeductInventoryStock;
use App\Modules\Inventory\Listeners\RestoreInventoryOnRefund;
use App\Modules\Inventory\Listeners\RestoreInventoryStock;
use App\Modules\Order\Events\OrderCancelled;
use App\Modules\Order\Events\OrderCreated;
use App\Modules\Order\Events\OrderRefunded;
use App\Modules\Product\Listeners\DeductProductStock;
use App\Modules\Product\Listeners\RestoreProductStock;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class OrderServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Product module (legacy — will be deprecated in favor of Inventory)
        Event::listen(OrderCreated::class, DeductProductStock::class);
        Event::listen(OrderCancelled::class, RestoreProductStock::class);

        // Inventory module (authoritative stock system via InventoryEngine)
        Event::listen(OrderCreated::class, DeductInventoryStock::class);
        Event::listen(OrderCancelled::class, RestoreInventoryStock::class);
        Event::listen(OrderRefunded::class, RestoreInventoryOnRefund::class);
    }
}

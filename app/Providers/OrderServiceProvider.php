<?php

declare(strict_types=1);

namespace App\Providers;

use App\Modules\Inventory\Listeners\DeductInventoryStock;
use App\Modules\Inventory\Listeners\RestoreInventoryOnRefund;
use App\Modules\Inventory\Listeners\RestoreInventoryStock;
use App\Modules\Order\Events\OrderCancelled;
use App\Modules\Order\Events\OrderCreated;
use App\Modules\Order\Events\OrderRefunded;
use App\Modules\Order\Events\ShipmentCreated;
use App\Modules\Order\Events\ShipmentDelivered;
use App\Modules\Order\Listeners\AutoCompleteOrder;
use App\Modules\Order\Listeners\NotifyCustomerOnShipment;
use App\Modules\Order\Listeners\SendOrderConfirmation;
use App\Modules\Order\Models\Order;
use App\Modules\Order\Models\Shipment;
use App\Modules\Order\Observers\OrderObserver;
use App\Modules\Order\Observers\ShipmentObserver;
use App\Modules\Order\Policies\OrderPolicy;
use App\Modules\Order\Policies\ShipmentPolicy;
use App\Modules\Order\Services\CourierManager;
use App\Modules\Order\Services\FulfillmentService;
use App\Modules\Order\Services\OrderIndustryIntegrator;
use App\Modules\Order\Services\OrderNumberGenerator;
use App\Modules\Order\Services\OrderService;
use App\Modules\Order\Services\OrderTimelineService;
use App\Modules\Order\Services\RefundService;
use App\Modules\Order\Services\ShipmentService;
use App\Modules\Order\Services\ShippingRateCalculator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class OrderServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(OrderNumberGenerator::class);
        $this->app->singleton(OrderService::class);
        $this->app->singleton(CourierManager::class);
        $this->app->singleton(ShipmentService::class);
        $this->app->singleton(FulfillmentService::class);
        $this->app->singleton(ShippingRateCalculator::class);
        $this->app->singleton(RefundService::class);
        $this->app->singleton(OrderTimelineService::class);
        $this->app->singleton(OrderIndustryIntegrator::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Modules/Order/Database/Migrations/Tenant');

        Gate::policy(Order::class, OrderPolicy::class);
        Gate::policy(Shipment::class, ShipmentPolicy::class);

        Order::observe(OrderObserver::class);
        Shipment::observe(ShipmentObserver::class);

        $this->registerCourierDrivers();

        // Order notifications
        Event::listen(OrderCreated::class, SendOrderConfirmation::class);
        Event::listen(ShipmentCreated::class, NotifyCustomerOnShipment::class);

        // Order auto-completion
        Event::listen(ShipmentDelivered::class, AutoCompleteOrder::class);

        // Inventory module (authoritative stock system via InventoryEngine)
        Event::listen(OrderCreated::class, DeductInventoryStock::class);
        Event::listen(OrderCancelled::class, RestoreInventoryStock::class);
        Event::listen(OrderRefunded::class, RestoreInventoryOnRefund::class);
    }

    private function registerCourierDrivers(): void
    {
        $manager = $this->app->make(CourierManager::class);
        $manager->registerDefaults();
    }
}

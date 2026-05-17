<?php

namespace App\Providers;

use App\Http\Middleware\EnsureTenantHasFeature;
use App\Http\Middleware\EnsureTenantHasSubscription;
use App\Modules\Billing\Events\PaymentFailed;
use App\Modules\Billing\Events\PaymentReceived;
use App\Modules\Billing\Events\SubscriptionActivated;
use App\Modules\Billing\Events\SubscriptionCancelled;
use App\Modules\Billing\Events\SubscriptionExpired;
use App\Modules\Billing\Listeners\SendSubscriptionNotification;
use App\Modules\Billing\Services\BillingManager;
use App\Modules\Billing\Services\InvoiceService;
use App\Modules\Billing\Services\PaymentService;
use App\Modules\Billing\Services\PlanFeatureService;
use App\Modules\Billing\Services\PlanService;
use App\Modules\Billing\Services\SubscriptionService;
use Illuminate\Support\ServiceProvider;

class BillingServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(config_path('billing.php'), 'billing');

        $this->app->singleton(BillingManager::class, function ($app) {
            return new BillingManager;
        });

        $this->app->singleton(SubscriptionService::class, function ($app) {
            return new SubscriptionService(
                $app->make(BillingManager::class),
                $app->make(PaymentService::class),
                $app->make(PlanService::class),
            );
        });

        $this->app->singleton(PlanService::class);
        $this->app->singleton(PaymentService::class);
        $this->app->singleton(InvoiceService::class);
        $this->app->singleton(PlanFeatureService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(database_path('migrations'));

        $this->app->make('router')
            ->aliasMiddleware('subscription', EnsureTenantHasSubscription::class);

        $this->app->make('router')
            ->aliasMiddleware('feature', EnsureTenantHasFeature::class);

        $this->registerEventListeners();
    }

    /**
     * Register billing event listeners.
     */
    private function registerEventListeners(): void
    {
        $events = $this->app->make('events');

        $listener = $this->app->make(SendSubscriptionNotification::class);

        $events->listen(
            SubscriptionActivated::class,
            [$listener, 'handleSubscriptionActivated']
        );

        $events->listen(
            SubscriptionExpired::class,
            [$listener, 'handleSubscriptionExpired']
        );

        $events->listen(
            PaymentReceived::class,
            [$listener, 'handlePaymentReceived']
        );

        $events->listen(
            PaymentFailed::class,
            [$listener, 'handlePaymentFailed']
        );

        $events->listen(
            SubscriptionCancelled::class,
            [$listener, 'handleSubscriptionCancelled']
        );
    }
}

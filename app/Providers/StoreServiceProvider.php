<?php

declare(strict_types=1);

namespace App\Providers;

use App\Modules\Store\Models\Store;
use App\Modules\Store\Policies\StorePolicy;
use App\Modules\Store\Services\StoreContextManager;
use App\Modules\Store\Services\StoreService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class StoreServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(config_path('store.php'), 'store');

        $this->app->singleton(StoreContextManager::class);
        $this->app->singleton(StoreService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(app_path('Modules/Store/Database/Migrations/Tenant'));

        Gate::policy(Store::class, StorePolicy::class);
    }
}

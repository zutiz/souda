<?php

declare(strict_types=1);

namespace App\Providers;

use App\Modules\BusinessType\Packs\AgroShopPack;
use App\Modules\BusinessType\Packs\BakeryPack;
use App\Modules\BusinessType\Packs\BookstorePack;
use App\Modules\BusinessType\Packs\CafePack;
use App\Modules\BusinessType\Packs\CosmeticsPack;
use App\Modules\BusinessType\Packs\DistributionPack;
use App\Modules\BusinessType\Packs\ElectronicsPack;
use App\Modules\BusinessType\Packs\FashionPack;
use App\Modules\BusinessType\Packs\GroceryPack;
use App\Modules\BusinessType\Packs\HardwarePack;
use App\Modules\BusinessType\Packs\PharmacyPack;
use App\Modules\BusinessType\Packs\RestaurantPack;
use App\Modules\BusinessType\Packs\SalonPack;
use App\Modules\BusinessType\Packs\SpaPack;
use App\Modules\BusinessType\Packs\WholesalePack;
use App\Modules\BusinessType\Services\BusinessTypeConfigBuilder;
use App\Modules\BusinessType\Services\BusinessTypeEngine;
use App\Modules\BusinessType\Services\IndustryPackRegistry;
use App\Modules\BusinessType\ValueObjects\TenantConfig;
use Illuminate\Support\ServiceProvider;

class IndustryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(IndustryPackRegistry::class);
        $this->app->singleton(BusinessTypeEngine::class);
        $this->app->singleton(BusinessTypeConfigBuilder::class);

        $this->app->bind(TenantConfig::class, function ($app) {
            $user = auth()->user();

            if ($user === null || $user->tenant === null) {
                return $app->make(BusinessTypeConfigBuilder::class)->buildDefaultConfig();
            }

            return $app->make(BusinessTypeEngine::class)->getEffectiveConfig($user->tenant);
        });
    }

    public function boot(): void
    {
        $registry = $this->app->make(IndustryPackRegistry::class);

        $registry->register(new AgroShopPack);
        $registry->register(new BakeryPack);
        $registry->register(new BookstorePack);
        $registry->register(new CafePack);
        $registry->register(new CosmeticsPack);
        $registry->register(new DistributionPack);
        $registry->register(new ElectronicsPack);
        $registry->register(new FashionPack);
        $registry->register(new GroceryPack);
        $registry->register(new HardwarePack);
        $registry->register(new PharmacyPack);
        $registry->register(new RestaurantPack);
        $registry->register(new SalonPack);
        $registry->register(new SpaPack);
        $registry->register(new WholesalePack);
    }
}

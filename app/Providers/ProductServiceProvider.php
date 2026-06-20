<?php

declare(strict_types=1);

namespace App\Providers;

use App\Modules\Product\Contracts\PricingCalculator;
use App\Modules\Product\Contracts\ProductCatalogService;
use App\Modules\Product\Contracts\ProductResolver;
use App\Modules\Product\Contracts\SKUGenerator;
use App\Modules\Product\Contracts\StockAllocator;
use App\Modules\Product\Contracts\StockChecker;
use App\Modules\Product\Events\LowStockAlert;
use App\Modules\Product\Events\ProductCreated;
use App\Modules\Product\Events\ProductDeleted;
use App\Modules\Product\Events\ProductUpdated;
use App\Modules\Product\Events\StockDepleted;
use App\Modules\Product\Events\StockReservationCreated;
use App\Modules\Product\Events\StockReservationExpired;
use App\Modules\Product\Events\StockUpdated;
use App\Modules\Product\Listeners\GenerateProductSKU;
use App\Modules\Product\Listeners\IndexProductForSearch;
use App\Modules\Product\Listeners\MarkProductUnavailable;
use App\Modules\Product\Listeners\ReleaseExpiredStock;
use App\Modules\Product\Listeners\RemoveProductFromSearchIndex;
use App\Modules\Product\Listeners\SendLowStockNotification;
use App\Modules\Product\Listeners\SendStockDepletedNotification;
use App\Modules\Product\Listeners\TrackStockReservation;
use App\Modules\Product\Listeners\UpdateProductSearchIndex;
use App\Modules\Product\Listeners\UpdateProductStockCache;
use App\Modules\Product\Models\Brand;
use App\Modules\Product\Models\Category;
use App\Modules\Product\Models\Product;
use App\Modules\Product\Models\StockReservation;
use App\Modules\Product\Models\Variant;
use App\Modules\Product\Models\Warehouse;
use App\Modules\Product\Models\WarehouseStock;
use App\Modules\Product\Observers\ProductObserver;
use App\Modules\Product\Observers\StockReservationObserver;
use App\Modules\Product\Observers\VariantObserver;
use App\Modules\Product\Observers\WarehouseStockObserver;
use App\Modules\Product\Policies\BrandPolicy;
use App\Modules\Product\Policies\CategoryPolicy;
use App\Modules\Product\Policies\ProductPolicy;
use App\Modules\Product\Policies\WarehousePolicy;
use App\Modules\Product\Services\AttributeService;
use App\Modules\Product\Services\BrandService;
use App\Modules\Product\Services\CategoryService;
use App\Modules\Product\Services\DefaultSKUGenerator;
use App\Modules\Product\Services\DefaultStockAllocator;
use App\Modules\Product\Services\EloquentPricingCalculator;
use App\Modules\Product\Services\EloquentProductCatalogService;
use App\Modules\Product\Services\EloquentProductResolver;
use App\Modules\Product\Services\EloquentStockChecker;
use App\Modules\Product\Services\MediaService;
use App\Modules\Product\Services\PricingRuleService;
use App\Modules\Product\Services\ProductImportService;
use App\Modules\Product\Services\ProductService;
use App\Modules\Product\Services\StockAuditService;
use App\Modules\Product\Services\StockLockService;
use App\Modules\Product\Services\StockReservationService;
use App\Modules\Product\Services\StockService;
use App\Modules\Product\Services\TaxService;
use App\Modules\Product\Services\VariantService;
use App\Modules\Product\Services\WarehouseService;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class ProductServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ProductService::class);
        $this->app->singleton(CategoryService::class);
        $this->app->singleton(BrandService::class);
        $this->app->singleton(VariantService::class);
        $this->app->singleton(AttributeService::class);
        $this->app->singleton(MediaService::class);
        $this->app->singleton(WarehouseService::class);
        $this->app->singleton(StockLockService::class);
        $this->app->singleton(StockAuditService::class);
        $this->app->singleton(StockService::class);
        $this->app->singleton(StockReservationService::class);
        $this->app->singleton(TaxService::class);
        $this->app->singleton(PricingRuleService::class);
        $this->app->singleton(ProductImportService::class);

        $this->app->bind(ProductResolver::class, EloquentProductResolver::class);
        $this->app->bind(StockChecker::class, EloquentStockChecker::class);
        $this->app->bind(PricingCalculator::class, EloquentPricingCalculator::class);
        $this->app->bind(SKUGenerator::class, DefaultSKUGenerator::class);
        $this->app->bind(StockAllocator::class, DefaultStockAllocator::class);
        $this->app->bind(ProductCatalogService::class, EloquentProductCatalogService::class);
    }

    public function boot(): void
    {
        Gate::policy(Product::class, ProductPolicy::class);
        Gate::policy(Category::class, CategoryPolicy::class);
        Gate::policy(Brand::class, BrandPolicy::class);
        Gate::policy(Warehouse::class, WarehousePolicy::class);

        Product::observe(ProductObserver::class);
        Variant::observe(VariantObserver::class);
        WarehouseStock::observe(WarehouseStockObserver::class);
        StockReservation::observe(StockReservationObserver::class);

        Event::listen(ProductCreated::class, IndexProductForSearch::class);
        Event::listen(ProductCreated::class, GenerateProductSKU::class);
        Event::listen(ProductUpdated::class, UpdateProductSearchIndex::class);
        Event::listen(ProductDeleted::class, RemoveProductFromSearchIndex::class);
        Event::listen(StockUpdated::class, UpdateProductStockCache::class);
        Event::listen(StockDepleted::class, MarkProductUnavailable::class);
        Event::listen(StockDepleted::class, SendStockDepletedNotification::class);
        Event::listen(LowStockAlert::class, SendLowStockNotification::class);
        Event::listen(StockReservationCreated::class, TrackStockReservation::class);
        Event::listen(StockReservationExpired::class, ReleaseExpiredStock::class);
    }
}

<?php

use App\Modules\Inventory\Providers\InventoryServiceProvider;
use App\Providers\AppServiceProvider;
use App\Providers\BillingServiceProvider;
use App\Providers\FortifyServiceProvider;
use App\Providers\IndustryServiceProvider;
use App\Providers\OnboardingServiceProvider;
use App\Providers\OrderServiceProvider;
use App\Providers\ProductServiceProvider;
use App\Providers\StoreServiceProvider;
use App\Providers\TenancyServiceProvider;

return [
    AppServiceProvider::class,
    FortifyServiceProvider::class,
    StoreServiceProvider::class,
    ProductServiceProvider::class,
    InventoryServiceProvider::class,
    TenancyServiceProvider::class,
    BillingServiceProvider::class,
    IndustryServiceProvider::class,
    OnboardingServiceProvider::class,
    OrderServiceProvider::class,
];

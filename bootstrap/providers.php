<?php

use App\Providers\AppServiceProvider;
use App\Providers\BillingServiceProvider;
use App\Providers\FortifyServiceProvider;
use App\Providers\ProductServiceProvider;
use App\Providers\TenancyServiceProvider;

return [
    AppServiceProvider::class,
    FortifyServiceProvider::class,
    ProductServiceProvider::class,
    TenancyServiceProvider::class,
    BillingServiceProvider::class,
];

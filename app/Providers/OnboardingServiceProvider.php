<?php

declare(strict_types=1);

namespace App\Providers;

use App\Modules\Onboarding\Services\ProvisioningPipeline;
use App\Modules\Onboarding\Services\TenantTemplateRegistry;
use App\Modules\Onboarding\Templates\AgroShopTemplate;
use App\Modules\Onboarding\Templates\BakeryTemplate;
use App\Modules\Onboarding\Templates\BookstoreTemplate;
use App\Modules\Onboarding\Templates\CafeTemplate;
use App\Modules\Onboarding\Templates\CosmeticsTemplate;
use App\Modules\Onboarding\Templates\DefaultTemplate;
use App\Modules\Onboarding\Templates\DistributionTemplate;
use App\Modules\Onboarding\Templates\ElectronicsTemplate;
use App\Modules\Onboarding\Templates\FashionTemplate;
use App\Modules\Onboarding\Templates\GroceryTemplate;
use App\Modules\Onboarding\Templates\HardwareTemplate;
use App\Modules\Onboarding\Templates\PharmacyTemplate;
use App\Modules\Onboarding\Templates\RestaurantTemplate;
use App\Modules\Onboarding\Templates\SalonTemplate;
use App\Modules\Onboarding\Templates\SpaTemplate;
use App\Modules\Onboarding\Templates\WholesaleTemplate;
use Illuminate\Support\ServiceProvider;

class OnboardingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TenantTemplateRegistry::class);
        $this->app->singleton(ProvisioningPipeline::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(base_path('routes/onboarding.php'));

        $registry = $this->app->make(TenantTemplateRegistry::class);

        $registry->register(new PharmacyTemplate);
        $registry->register(new RestaurantTemplate);
        $registry->register(new GroceryTemplate);
        $registry->register(new SalonTemplate);
        $registry->register(new BakeryTemplate);
        $registry->register(new CafeTemplate);
        $registry->register(new ElectronicsTemplate);
        $registry->register(new FashionTemplate);
        $registry->register(new CosmeticsTemplate);
        $registry->register(new HardwareTemplate);
        $registry->register(new WholesaleTemplate);
        $registry->register(new DistributionTemplate);
        $registry->register(new AgroShopTemplate);
        $registry->register(new BookstoreTemplate);
        $registry->register(new SpaTemplate);
        $registry->register(new DefaultTemplate);
    }
}

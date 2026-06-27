<?php

declare(strict_types=1);

namespace App\Modules\Onboarding\Services;

use App\Modules\Onboarding\Contracts\ProvisioningStep;
use App\Modules\Onboarding\Data\ProvisioningContext;
use App\Modules\Store\DTOs\StoreDTO;
use App\Modules\Store\Models\Store;
use App\Modules\Store\Services\StoreService;
use App\Tenancy\TenantManager;
use Illuminate\Support\Facades\DB;

class CreateDefaultStoreStep implements ProvisioningStep
{
    public function handle(ProvisioningContext $context): void
    {
        $templateRegistry = app(TenantTemplateRegistry::class);
        $template = $templateRegistry->get($context->businessTypeSlug);

        app(TenantManager::class)->initialize($context->tenant);

        DB::transaction(function () use ($template) {
            $storeService = app(StoreService::class);

            foreach ($template->defaultStores() as $storeData) {
                $storeService->createStore(StoreDTO::fromRequest($storeData));
            }
        });

        app(TenantManager::class)->end();
    }

    public function rollback(ProvisioningContext $context): void
    {
        app(TenantManager::class)->initialize($context->tenant);

        Store::query()->forceDelete();

        app(TenantManager::class)->end();
    }

    public function label(): string
    {
        return 'Creating default store';
    }
}

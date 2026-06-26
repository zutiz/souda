<?php

declare(strict_types=1);

namespace App\Modules\Onboarding\Services;

use App\Models\TenantSetting;
use App\Modules\BusinessType\Services\IndustryPackRegistry;
use App\Modules\Onboarding\Contracts\ProvisioningStep;
use App\Modules\Onboarding\Data\ProvisioningContext;
use App\Modules\Product\Models\Category;
use Illuminate\Support\Facades\DB;

class SeedDefaultDataStep implements ProvisioningStep
{
    public function handle(ProvisioningContext $context): void
    {
        $packRegistry = app(IndustryPackRegistry::class);
        $pack = $packRegistry->get($context->businessTypeSlug);

        $templateRegistry = app(TenantTemplateRegistry::class);
        $template = $templateRegistry->get($context->businessTypeSlug);

        tenancy()->initialize($context->tenant);

        DB::transaction(function () use ($pack, $template) {
            foreach ($template?->defaultCategories() ?? [] as $cat) {
                $this->createCategory($cat, null);
            }

            $defaults = TenantSetting::getDefaults();

            if ($pack !== null) {
                foreach ($pack->defaultSettings() as $key => $value) {
                    $defaults[$key] = $value;
                }
            }

            TenantSetting::query()->create($defaults);
        });

        tenancy()->end();
    }

    private function createCategory(array $data, ?int $parentId): void
    {
        $children = $data['children'] ?? [];
        unset($data['children']);

        $category = Category::query()->create([
            'name' => $data['name'],
            'parent_id' => $parentId,
            'is_active' => true,
        ]);

        foreach ($children as $child) {
            $this->createCategory($child, $category->id);
        }
    }

    public function rollback(ProvisioningContext $context): void
    {
        tenancy()->initialize($context->tenant);

        Category::query()->whereNull('parent_id')->delete();
        TenantSetting::query()->truncate();

        tenancy()->end();
    }

    public function label(): string
    {
        return 'Seeding default data';
    }
}

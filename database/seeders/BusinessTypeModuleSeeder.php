<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\BusinessType\Models\BusinessType;
use App\Modules\BusinessType\Models\Module;
use Illuminate\Database\Seeder;

class BusinessTypeModuleSeeder extends Seeder
{
    public function run(): void
    {
        $mappings = [
            'grocery' => [
                'product' => true,
                'inventory' => true,
                'order' => true,
                'pos' => true,
                'crm' => true,
                'billing' => true,
                'team' => true,
                'supplier' => true,
                'reporting' => true,
            ],
            'pharmacy' => [
                'product' => true,
                'inventory' => true,
                'order' => true,
                'pos' => true,
                'crm' => true,
                'billing' => true,
                'team' => true,
                'supplier' => true,
                'reporting' => true,
            ],
            'restaurant' => [
                'product' => true,
                'inventory' => true,
                'order' => true,
                'pos' => true,
                'crm' => true,
                'billing' => true,
                'team' => true,
                'kitchen' => true,
                'reporting' => true,
            ],
            'cafe' => [
                'product' => true,
                'inventory' => true,
                'order' => true,
                'pos' => true,
                'crm' => true,
                'billing' => true,
                'team' => true,
                'reporting' => true,
            ],
            'bakery' => [
                'product' => true,
                'inventory' => true,
                'order' => true,
                'pos' => true,
                'crm' => true,
                'billing' => true,
                'team' => true,
                'reporting' => true,
            ],
            'salon' => [
                'product' => true,
                'inventory' => true,
                'order' => true,
                'pos' => true,
                'crm' => true,
                'billing' => true,
                'team' => true,
                'appointment' => true,
                'reporting' => true,
            ],
            'spa' => [
                'product' => true,
                'inventory' => true,
                'order' => true,
                'pos' => true,
                'crm' => true,
                'billing' => true,
                'team' => true,
                'appointment' => true,
                'reporting' => true,
            ],
            'electronics' => [
                'product' => true,
                'inventory' => true,
                'order' => true,
                'pos' => true,
                'crm' => true,
                'billing' => true,
                'team' => true,
                'supplier' => true,
                'reporting' => true,
            ],
            'fashion' => [
                'product' => true,
                'inventory' => true,
                'order' => true,
                'pos' => true,
                'crm' => true,
                'billing' => true,
                'team' => true,
                'reporting' => true,
            ],
            'cosmetics' => [
                'product' => true,
                'inventory' => true,
                'order' => true,
                'pos' => true,
                'crm' => true,
                'billing' => true,
                'team' => true,
                'reporting' => true,
            ],
            'hardware' => [
                'product' => true,
                'inventory' => true,
                'order' => true,
                'pos' => true,
                'crm' => true,
                'billing' => true,
                'team' => true,
                'supplier' => true,
                'reporting' => true,
            ],
            'wholesale' => [
                'product' => true,
                'inventory' => true,
                'order' => true,
                'pos' => true,
                'crm' => true,
                'billing' => true,
                'team' => true,
                'supplier' => true,
                'reporting' => true,
            ],
            'distribution' => [
                'product' => true,
                'inventory' => true,
                'order' => true,
                'crm' => true,
                'billing' => true,
                'team' => true,
                'supplier' => true,
                'reporting' => true,
            ],
            'agro_shop' => [
                'product' => true,
                'inventory' => true,
                'order' => true,
                'pos' => true,
                'crm' => true,
                'billing' => true,
                'team' => true,
                'supplier' => true,
                'reporting' => true,
            ],
            'bookstore' => [
                'product' => true,
                'inventory' => true,
                'order' => true,
                'pos' => true,
                'crm' => true,
                'billing' => true,
                'team' => true,
                'reporting' => true,
            ],
        ];

        foreach ($mappings as $businessTypeSlug => $moduleMappings) {
            $businessType = BusinessType::query()->where('slug', $businessTypeSlug)->first();

            if ($businessType === null) {
                continue;
            }

            foreach ($moduleMappings as $moduleSlug => $isRequired) {
                $module = Module::query()->where('slug', $moduleSlug)->first();

                if ($module === null) {
                    continue;
                }

                $businessType->modules()->syncWithoutDetaching([
                    $module->id => ['is_required' => $isRequired],
                ]);
            }
        }
    }
}

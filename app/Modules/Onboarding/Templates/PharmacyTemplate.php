<?php

declare(strict_types=1);

namespace App\Modules\Onboarding\Templates;

use App\Modules\Onboarding\Contracts\TenantTemplate;

class PharmacyTemplate implements TenantTemplate
{
    public function businessType(): string
    {
        return 'pharmacy';
    }

    public function defaultCategories(): array
    {
        return [
            ['name' => 'Medicines', 'children' => [
                ['name' => 'Antibiotics'],
                ['name' => 'Pain Relief'],
                ['name' => 'Cardiovascular'],
                ['name' => 'Diabetes'],
                ['name' => 'Respiratory'],
                ['name' => 'Gastrointestinal'],
            ]],
            ['name' => 'Wellness', 'children' => [
                ['name' => 'Vitamins & Supplements'],
                ['name' => 'First Aid'],
                ['name' => 'Personal Care'],
            ]],
            ['name' => 'Medical Devices'],
        ];
    }

    public function productSchema(): array
    {
        return [
            'fields' => [
                [
                    'slug' => 'generic_name',
                    'label' => 'Generic Name',
                    'type' => 'string',
                    'required' => true,
                    'section' => 'details',
                    'order' => 1,
                ],
                [
                    'slug' => 'strength',
                    'label' => 'Strength',
                    'type' => 'string',
                    'required' => false,
                    'section' => 'details',
                    'order' => 2,
                    'placeholder' => 'e.g., 500mg',
                ],
                [
                    'slug' => 'drug_schedule',
                    'label' => 'Drug Schedule',
                    'type' => 'select',
                    'required' => true,
                    'section' => 'classification',
                    'order' => 3,
                    'options' => ['N/A', 'Schedule H', 'Schedule G', 'Schedule X', 'Schedule H1', 'OTC'],
                ],
                [
                    'slug' => 'requires_prescription',
                    'label' => 'Requires Prescription',
                    'type' => 'boolean',
                    'required' => true,
                    'section' => 'classification',
                    'order' => 4,
                ],
                [
                    'slug' => 'manufacturer',
                    'label' => 'Manufacturer',
                    'type' => 'string',
                    'required' => false,
                    'section' => 'supply',
                    'order' => 5,
                ],
                [
                    'slug' => 'batch_number',
                    'label' => 'Batch Number',
                    'type' => 'string',
                    'required' => false,
                    'section' => 'inventory',
                    'order' => 6,
                ],
                [
                    'slug' => 'expiry_date',
                    'label' => 'Expiry Date',
                    'type' => 'date',
                    'required' => true,
                    'section' => 'inventory',
                    'order' => 7,
                ],
            ],
            'sections' => [
                'details' => ['title' => 'Drug Details', 'order' => 1],
                'classification' => ['title' => 'Classification', 'order' => 2],
                'supply' => ['title' => 'Supply Chain', 'order' => 3],
                'inventory' => ['title' => 'Inventory Control', 'order' => 4],
            ],
            'search_columns' => ['name', 'sku', 'barcode', 'generic_name', 'strength'],
            'list_columns' => ['name', 'generic_name', 'strength', 'stock', 'expiry_date'],
        ];
    }

    public function dashboardLayout(): array
    {
        return [
            ['widget' => 'today_sales_summary', 'width' => 'half', 'order' => 1],
            ['widget' => 'expiring_products', 'width' => 'half', 'order' => 2],
            ['widget' => 'low_stock_medicines', 'width' => 'half', 'order' => 3],
            ['widget' => 'prescription_vs_otc', 'width' => 'half', 'order' => 4],
            ['widget' => 'top_medicines', 'width' => 'full', 'order' => 5],
        ];
    }

    public function posDefaults(): array
    {
        return [
            'search_columns' => ['name', 'sku', 'barcode', 'generic_name', 'strength'],
            'checkout_fields' => [
                ['slug' => 'prescription_number', 'label' => 'Prescription #', 'required' => false],
                ['slug' => 'doctor_name', 'label' => 'Doctor Name', 'required' => false],
            ],
            'batch_picking' => true,
            'show_expiry_warning' => true,
        ];
    }

    public function defaultTeam(): array
    {
        return [
            'roles' => ['admin', 'manager', 'pharmacist', 'cashier'],
        ];
    }

    public function notificationDefaults(): array
    {
        return [
            'email_notifications' => true,
            'order_confirmation' => true,
            'low_stock_alerts' => true,
            'expiry_alerts' => true,
        ];
    }

    public function initialData(): array
    {
        return [];
    }

    public function defaultStores(): array
    {
        return [
            [
                'name' => 'Main Store',
                'slug' => 'main',
                'code' => 'STORE-001',
                'currency' => 'XOF',
                'timezone' => 'Africa/Porto-Novo',
                'is_default' => true,
                'status' => 'active',
            ],
        ];
    }
}

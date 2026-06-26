<?php

declare(strict_types=1);

namespace App\Modules\Onboarding\Templates;

use App\Modules\Onboarding\Contracts\TenantTemplate;

class SalonTemplate implements TenantTemplate
{
    public function businessType(): string
    {
        return 'salon';
    }

    public function defaultCategories(): array
    {
        return [
            ['name' => 'Hair Services', 'children' => [
                ['name' => 'Cuts & Styling'],
                ['name' => 'Coloring'],
                ['name' => 'Treatments'],
            ]],
            ['name' => 'Nail Services', 'children' => [
                ['name' => 'Manicure'],
                ['name' => 'Pedicure'],
                ['name' => 'Gel & Acrylic'],
            ]],
            ['name' => 'Skincare', 'children' => [
                ['name' => 'Facials'],
                ['name' => 'Waxing'],
            ]],
            ['name' => 'Products', 'children' => [
                ['name' => 'Hair Products'],
                ['name' => 'Skincare Products'],
            ]],
        ];
    }

    public function productSchema(): array
    {
        return [
            'fields' => [
                [
                    'slug' => 'duration_minutes',
                    'label' => 'Duration (min)',
                    'type' => 'number',
                    'required' => true,
                    'section' => 'scheduling',
                    'order' => 1,
                ],
                [
                    'slug' => 'requires_booking',
                    'label' => 'Requires Booking',
                    'type' => 'boolean',
                    'required' => true,
                    'section' => 'scheduling',
                    'order' => 2,
                ],
                [
                    'slug' => 'gender',
                    'label' => 'Gender',
                    'type' => 'select',
                    'required' => false,
                    'section' => 'details',
                    'order' => 3,
                    'options' => ['All', 'Male', 'Female', 'Kids'],
                ],
                [
                    'slug' => 'product_brand',
                    'label' => 'Brand Used',
                    'type' => 'string',
                    'required' => false,
                    'section' => 'supply',
                    'order' => 4,
                ],
            ],
            'sections' => [
                'scheduling' => ['title' => 'Scheduling', 'order' => 1],
                'details' => ['title' => 'Service Details', 'order' => 2],
                'supply' => ['title' => 'Product Supply', 'order' => 3],
            ],
            'search_columns' => ['name', 'sku', 'category'],
            'list_columns' => ['name', 'category', 'price', 'duration_minutes'],
        ];
    }

    public function dashboardLayout(): array
    {
        return [
            ['widget' => 'todays_appointments', 'width' => 'half', 'order' => 1],
            ['widget' => 'daily_revenue', 'width' => 'half', 'order' => 2],
            ['widget' => 'popular_services', 'width' => 'full', 'order' => 3],
        ];
    }

    public function posDefaults(): array
    {
        return [
            'layout' => 'grid',
            'has_appointment_integration' => true,
            'tender_types' => ['cash', 'card', 'mobile_banking'],
        ];
    }

    public function defaultTeam(): array
    {
        return [
            'roles' => ['admin', 'manager', 'stylist', 'receptionist'],
        ];
    }

    public function notificationDefaults(): array
    {
        return [
            'email_notifications' => true,
            'appointment_reminders' => true,
            'new_booking_alerts' => true,
        ];
    }

    public function initialData(): array
    {
        return [];
    }
}

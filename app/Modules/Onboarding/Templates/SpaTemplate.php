<?php

declare(strict_types=1);

namespace App\Modules\Onboarding\Templates;

use App\Modules\Onboarding\Contracts\TenantTemplate;

class SpaTemplate implements TenantTemplate
{
    public function businessType(): string
    {
        return 'spa';
    }

    public function defaultCategories(): array
    {
        return [
            ['name' => 'Massage', 'children' => [
                ['name' => 'Swedish Massage'],
                ['name' => 'Deep Tissue'],
                ['name' => 'Hot Stone'],
                ['name' => 'Aromatherapy'],
            ]],
            ['name' => 'Body Treatments', 'children' => [
                ['name' => 'Body Scrubs'],
                ['name' => 'Body Wraps'],
                ['name' => 'Hydrotherapy'],
            ]],
            ['name' => 'Facials', 'children' => [
                ['name' => 'Classic Facial'],
                ['name' => 'Anti-Aging'],
                ['name' => 'Deep Cleansing'],
            ]],
            ['name' => 'Packages', 'children' => [
                ['name' => 'Couples Packages'],
                ['name' => 'Day Spa Packages'],
                ['name' => 'Wellness Programs'],
            ]],
            ['name' => 'Retail Products', 'children' => [
                ['name' => 'Skincare Products'],
                ['name' => 'Essential Oils'],
                ['name' => 'Spa Accessories'],
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
                    'options' => ['All', 'Male', 'Female'],
                ],
                [
                    'slug' => 'room_type',
                    'label' => 'Room Type',
                    'type' => 'select',
                    'required' => false,
                    'section' => 'facilities',
                    'order' => 4,
                    'options' => ['Private Room', 'Couples Room', 'Open Area', 'VIP Suite'],
                ],
                [
                    'slug' => 'product_brand',
                    'label' => 'Product Brand',
                    'type' => 'string',
                    'required' => false,
                    'section' => 'supply',
                    'order' => 5,
                ],
                [
                    'slug' => 'prep_time_minutes',
                    'label' => 'Prep Time (min)',
                    'type' => 'number',
                    'required' => false,
                    'section' => 'scheduling',
                    'order' => 6,
                ],
            ],
            'sections' => [
                'scheduling' => ['title' => 'Scheduling', 'order' => 1],
                'details' => ['title' => 'Service Details', 'order' => 2],
                'facilities' => ['title' => 'Facilities', 'order' => 3],
                'supply' => ['title' => 'Product Supply', 'order' => 4],
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
            ['widget' => 'popular_services', 'width' => 'half', 'order' => 3],
            ['widget' => 'upcoming_bookings', 'width' => 'full', 'order' => 4],
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
            'roles' => ['admin', 'manager', 'therapist', 'receptionist'],
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

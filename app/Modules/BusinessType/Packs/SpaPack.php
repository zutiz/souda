<?php

declare(strict_types=1);

namespace App\Modules\BusinessType\Packs;

use App\Models\Tenant;
use App\Modules\BusinessType\Contracts\IndustryPack;

class SpaPack implements IndustryPack
{
    public function slug(): string
    {
        return 'spa';
    }

    public function name(): string
    {
        return 'Spa';
    }

    public function description(): string
    {
        return 'Wellness spa with package management, appointment booking, therapist scheduling, and membership plans';
    }

    public function modules(): array
    {
        return [
            'product' => ['required' => true],
            'inventory' => ['required' => true],
            'order' => ['required' => true],
            'pos' => ['required' => true],
            'crm' => ['required' => true],
            'billing' => ['required' => true],
            'team' => ['required' => true],
            'appointment' => ['required' => true],
            'reporting' => ['required' => true],
        ];
    }

    public function menus(): array
    {
        return [
            'main' => [
                ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'LayoutDashboard'],
                ['label' => 'Services', 'route' => 'products.index', 'icon' => 'Sparkles', 'children' => [
                    ['label' => 'All Services', 'route' => 'products.index'],
                    ['label' => 'Packages', 'route' => 'spa.packages'],
                    ['label' => 'Memberships', 'route' => 'spa.memberships'],
                ]],
                ['label' => 'Appointments', 'route' => 'appointments.index', 'icon' => 'Calendar', 'children' => [
                    ['label' => 'Calendar', 'route' => 'appointments.index'],
                    ['label' => 'New Booking', 'route' => 'appointments.create'],
                ]],
                ['label' => 'Therapists', 'route' => 'staff.index', 'icon' => 'Users'],
                ['label' => 'POS', 'route' => 'pos.index', 'icon' => 'ShoppingCart'],
                ['label' => 'Customers', 'route' => 'customers.index', 'icon' => 'UserCircle'],
                ['label' => 'Reports', 'route' => 'reports.index', 'icon' => 'BarChart3', 'children' => [
                    ['label' => 'Revenue Report', 'route' => 'reports.revenue'],
                    ['label' => 'Therapist Performance', 'route' => 'reports.therapist-performance'],
                    ['label' => 'Membership Report', 'route' => 'reports.membership'],
                ]],
                ['label' => 'Settings', 'route' => 'settings.index', 'icon' => 'Settings'],
            ],
        ];
    }

    public function permissions(): array
    {
        return [
            'admin' => ['products.*', 'inventory.*', 'pos.*', 'orders.*', 'customers.*', 'appointments.*', 'staff.*', 'reports.*', 'settings.*', 'spa.*'],
            'manager' => ['products.*', 'inventory.view', 'pos.*', 'orders.*', 'customers.*', 'appointments.*', 'staff.view', 'reports.view'],
            'therapist' => ['appointments.view', 'appointments.update', 'pos.create', 'customers.view'],
        ];
    }

    public function posConfig(): array
    {
        return [
            'layout' => 'grid',
            'product_search_columns' => ['name', 'category'],
            'quick_actions' => [
                ['label' => 'Walk-in', 'action' => 'walk_in'],
                ['label' => 'Membership', 'action' => 'apply_membership'],
                ['label' => 'Package Deal', 'action' => 'add_package'],
            ],
            'checkout_fields' => [
                ['slug' => 'therapist_id', 'label' => 'Therapist', 'required' => true],
                ['slug' => 'room_number', 'label' => 'Room #', 'required' => false],
                ['slug' => 'membership_id', 'label' => 'Membership', 'required' => false],
            ],
            'has_memberships' => true,
            'has_packages' => true,
            'receipt_fields' => ['services', 'therapist', 'duration', 'subtotal', 'total'],
            'tender_types' => ['cash', 'card', 'mobile_banking', 'membership'],
        ];
    }

    public function dashboardWidgets(): array
    {
        return [
            'today_appointments' => ['component' => 'TodayAppointments', 'title' => 'Today\'s Bookings', 'width' => 'half', 'permission' => 'appointments.view'],
            'revenue_summary' => ['component' => 'RevenueSummary', 'title' => 'Revenue Today', 'width' => 'half', 'permission' => 'reports.view'],
            'membership_stats' => ['component' => 'MembershipStats', 'title' => 'Active Memberships', 'width' => 'half', 'permission' => 'spa.memberships.view'],
        ];
    }

    public function reportDefinitions(): array
    {
        return [
            'revenue' => ['name' => 'Revenue Report', 'description' => 'Revenue by service and therapist', 'permission' => 'reports.view', 'filters' => ['date_range', 'therapist'], 'export_formats' => ['pdf', 'csv', 'xlsx']],
            'therapist-performance' => ['name' => 'Therapist Performance', 'description' => 'Bookings and revenue per therapist', 'permission' => 'reports.view', 'filters' => ['date_range', 'therapist'], 'export_formats' => ['pdf', 'csv', 'xlsx']],
            'membership' => ['name' => 'Membership Report', 'description' => 'Membership signups, renewals, and churn', 'permission' => 'reports.view', 'filters' => ['date_range'], 'export_formats' => ['pdf', 'csv', 'xlsx']],
        ];
    }

    public function defaultSettings(): array
    {
        return ['currency' => 'BDT', 'default_session_duration_minutes' => 60, 'membership_grace_days' => 7];
    }

    public function featureFlags(): array
    {
        return [
            'appointment_booking',
            'membership_plans',
            'package_deals',
            'therapist_commission',
            'online_booking',
            'batch_tracking',
            'low_stock_alerts',
            'stock_transfers',
            'cycle_counting',
            'dead_stock_detection',
        ];
    }

    public function branding(): array
    {
        return [
            'primary' => 'oklch(0.60 0.12 200)',     // Calming teal
            'primary_foreground' => 'oklch(0.985 0 0)',
            'accent' => 'oklch(0.94 0.05 200)',      // Light teal
            'accent_foreground' => 'oklch(0.205 0 0)',
            'sidebar' => 'oklch(0.96 0.03 200)',
            'sidebar_foreground' => 'oklch(0.205 0 0)',
            'sidebar_accent' => 'oklch(0.92 0.04 200)',
            'radius' => '1rem',                      // Soft, calming
        ];
    }

    public function onTenantAssigned(Tenant $tenant): void {}

    public function onTenantRemoved(Tenant $tenant): void {}
}

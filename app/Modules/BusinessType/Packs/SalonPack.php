<?php

declare(strict_types=1);

namespace App\Modules\BusinessType\Packs;

use App\Models\Tenant;
use App\Modules\BusinessType\Contracts\IndustryPack;

class SalonPack implements IndustryPack
{
    public function slug(): string
    {
        return 'salon';
    }

    public function name(): string
    {
        return 'Salon';
    }

    public function description(): string
    {
        return 'Hair and beauty salon with service management, appointment booking, and commission tracking';
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
                ['label' => 'Services', 'route' => 'products.index', 'icon' => 'Scissors', 'children' => [
                    ['label' => 'All Services', 'route' => 'products.index'],
                    ['label' => 'Service Categories', 'route' => 'categories.index'],
                    ['label' => 'Service Packages', 'route' => 'products.packages'],
                ]],
                ['label' => 'Appointments', 'route' => 'appointments.index', 'icon' => 'Calendar', 'children' => [
                    ['label' => 'Calendar', 'route' => 'appointments.index'],
                    ['label' => 'New Booking', 'route' => 'appointments.create'],
                    ['label' => 'Waitlist', 'route' => 'appointments.waitlist'],
                ]],
                ['label' => 'Staff', 'route' => 'staff.index', 'icon' => 'Users', 'children' => [
                    ['label' => 'Staff List', 'route' => 'staff.index'],
                    ['label' => 'Schedule', 'route' => 'staff.schedule'],
                    ['label' => 'Commission', 'route' => 'staff.commission'],
                ]],
                ['label' => 'POS', 'route' => 'pos.index', 'icon' => 'ShoppingCart'],
                ['label' => 'Customers', 'route' => 'customers.index', 'icon' => 'UserCircle'],
                ['label' => 'Inventory', 'route' => 'inventory.index', 'icon' => 'Package', 'children' => [
                    ['label' => 'Products', 'route' => 'inventory.index'],
                    ['label' => 'Stock Movements', 'route' => 'stock.movements'],
                ]],
                ['label' => 'Reports', 'route' => 'reports.index', 'icon' => 'BarChart3', 'children' => [
                    ['label' => 'Sales Report', 'route' => 'reports.sales'],
                    ['label' => 'Staff Commission', 'route' => 'reports.staff-commission'],
                    ['label' => 'Service Popularity', 'route' => 'reports.service-popularity'],
                    ['label' => 'Customer Retention', 'route' => 'reports.customer-retention'],
                ]],
                ['label' => 'Settings', 'route' => 'settings.index', 'icon' => 'Settings'],
            ],
        ];
    }

    public function permissions(): array
    {
        return [
            'admin' => [
                'products.*',
                'inventory.*',
                'pos.*',
                'orders.*',
                'customers.*',
                'appointments.*',
                'staff.*',
                'reports.*',
                'settings.*',
                'commission.*',
            ],
            'manager' => [
                'products.*',
                'inventory.view',
                'pos.*',
                'orders.*',
                'customers.*',
                'appointments.*',
                'staff.view',
                'reports.view',
                'commission.view',
            ],
            'stylist' => [
                'appointments.view',
                'appointments.update',
                'pos.create',
                'orders.create',
                'customers.create',
                'customers.view',
                'products.view',
            ],
            'receptionist' => [
                'appointments.*',
                'customers.*',
                'pos.create',
                'orders.create',
            ],
        ];
    }

    public function posConfig(): array
    {
        return [
            'layout' => 'grid',
            'product_search_columns' => ['name', 'category', 'staff_name'],
            'quick_actions' => [
                ['label' => 'Walk-in', 'action' => 'walk_in'],
                ['label' => 'Add Staff', 'action' => 'assign_staff'],
                ['label' => 'Add Package', 'action' => 'add_package'],
            ],
            'checkout_fields' => [
                ['slug' => 'staff_id', 'label' => 'Staff Member', 'required' => true],
                ['slug' => 'service_duration', 'label' => 'Duration (mins)', 'required' => false, 'type' => 'number'],
                ['slug' => 'customer_notes', 'label' => 'Notes', 'required' => false],
            ],
            'has_staff_assignment' => true,
            'has_service_duration' => true,
            'has_packages' => true,
            'receipt_fields' => ['services', 'staff', 'duration', 'subtotal', 'commission', 'total'],
            'tender_types' => ['cash', 'card', 'mobile_banking', 'membership'],
        ];
    }

    public function dashboardWidgets(): array
    {
        return [
            'today_appointments' => [
                'component' => 'TodayAppointments',
                'title' => 'Today\'s Appointments',
                'width' => 'half',
                'permission' => 'appointments.view',
            ],
            'daily_revenue' => [
                'component' => 'DailyRevenueSummary',
                'title' => 'Daily Revenue',
                'width' => 'half',
                'permission' => 'reports.view',
            ],
            'top_staff' => [
                'component' => 'TopStaffTable',
                'title' => 'Top Performing Staff',
                'width' => 'half',
                'permission' => 'reports.view',
            ],
            'upcoming_bookings' => [
                'component' => 'UpcomingBookings',
                'title' => 'Upcoming Bookings',
                'width' => 'half',
                'permission' => 'appointments.view',
            ],
        ];
    }

    public function reportDefinitions(): array
    {
        return [
            'sales' => [
                'name' => 'Sales Report',
                'description' => 'Revenue breakdown by service, staff, and time period',
                'permission' => 'reports.view',
                'filters' => ['date_range', 'staff', 'service_category'],
                'export_formats' => ['pdf', 'csv', 'xlsx'],
            ],
            'staff-commission' => [
                'name' => 'Staff Commission Report',
                'description' => 'Commission earned by each staff member',
                'permission' => 'reports.view',
                'filters' => ['date_range', 'staff'],
                'export_formats' => ['pdf', 'csv', 'xlsx'],
            ],
            'service-popularity' => [
                'name' => 'Service Popularity',
                'description' => 'Most booked services and revenue generated',
                'permission' => 'reports.view',
                'filters' => ['date_range', 'category'],
                'export_formats' => ['pdf', 'csv', 'xlsx'],
            ],
            'customer-retention' => [
                'name' => 'Customer Retention',
                'description' => 'Returning vs new customer analysis',
                'permission' => 'reports.view',
                'filters' => ['date_range'],
                'export_formats' => ['pdf', 'csv', 'xlsx'],
            ],
        ];
    }

    public function defaultSettings(): array
    {
        return [
            'currency' => 'BDT',
            'default_commission_percentage' => 40,
            'appointment_reminder_hours' => 24,
            'allow_walkins' => true,
            'default_appointment_duration_minutes' => 60,
            'cancellation_policy_hours' => 4,
        ];
    }

    public function featureFlags(): array
    {
        return [
            'appointment_booking',
            'staff_commission',
            'service_packages',
            'customer_loyalty',
            'online_booking',
            'waitlist_management',
        ];
    }

    public function onTenantAssigned(Tenant $tenant): void {}

    public function onTenantRemoved(Tenant $tenant): void {}
}

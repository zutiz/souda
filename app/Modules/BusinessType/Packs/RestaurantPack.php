<?php

declare(strict_types=1);

namespace App\Modules\BusinessType\Packs;

use App\Models\Tenant;
use App\Modules\BusinessType\Contracts\IndustryPack;

class RestaurantPack implements IndustryPack
{
    public function slug(): string
    {
        return 'restaurant';
    }

    public function name(): string
    {
        return 'Restaurant';
    }

    public function description(): string
    {
        return 'Full-service restaurant with menu management, kitchen display, and table service';
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
            'kitchen' => ['required' => true],
            'reporting' => ['required' => true],
        ];
    }

    public function menus(): array
    {
        return [
            'main' => [
                ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'LayoutDashboard'],
                ['label' => 'Menu', 'route' => 'products.index', 'icon' => 'UtensilsCrossed', 'children' => [
                    ['label' => 'Menu Items', 'route' => 'products.index'],
                    ['label' => 'Categories', 'route' => 'categories.index'],
                    ['label' => 'Recipes', 'route' => 'kitchen.recipes'],
                    ['label' => 'Modifiers', 'route' => 'products.modifiers'],
                ]],
                ['label' => 'Kitchen', 'route' => 'kitchen.index', 'icon' => 'ChefHat', 'children' => [
                    ['label' => 'Order Queue', 'route' => 'kitchen.index'],
                    ['label' => 'Prep Stations', 'route' => 'kitchen.prep-stations'],
                    ['label' => 'Recipe Management', 'route' => 'kitchen.recipes'],
                ]],
                ['label' => 'Orders', 'route' => 'orders.index', 'icon' => 'ClipboardList', 'children' => [
                    ['label' => 'Active Orders', 'route' => 'orders.index'],
                    ['label' => 'Dine-in', 'route' => 'orders.dine-in'],
                    ['label' => 'Takeaway', 'route' => 'orders.takeaway'],
                    ['label' => 'Delivery', 'route' => 'orders.delivery'],
                    ['label' => 'Reservations', 'route' => 'orders.reservations'],
                ]],
                ['label' => 'POS', 'route' => 'pos.index', 'icon' => 'ShoppingCart'],
                ['label' => 'Inventory', 'route' => 'inventory.index', 'icon' => 'Package', 'children' => [
                    ['label' => 'Stock', 'route' => 'inventory.index'],
                    ['label' => 'Stock Movements', 'route' => 'stock.movements'],
                    ['label' => 'Low Stock', 'route' => 'inventory.low-stock'],
                ]],
                ['label' => 'Customers', 'route' => 'customers.index', 'icon' => 'Users'],
                ['label' => 'Reports', 'route' => 'reports.index', 'icon' => 'BarChart3', 'children' => [
                    ['label' => 'Sales Report', 'route' => 'reports.sales'],
                    ['label' => 'Food Cost', 'route' => 'reports.food-cost'],
                    ['label' => 'Kitchen Performance', 'route' => 'reports.kitchen-performance'],
                    ['label' => 'Popular Items', 'route' => 'reports.popular-items'],
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
                'kitchen.*',
                'reports.*',
                'settings.*',
            ],
            'manager' => [
                'products.*',
                'inventory.view',
                'inventory.stock.adjust',
                'pos.*',
                'orders.*',
                'customers.view',
                'customers.create',
                'kitchen.view',
                'reports.view',
                'menu.pricing.manage',
            ],
            'chef' => [
                'kitchen.*',
                'products.view',
                'inventory.view',
            ],
            'server' => [
                'pos.create',
                'orders.create',
                'orders.view',
                'customers.create',
            ],
            'cashier' => [
                'pos.*',
                'orders.create',
                'orders.view',
                'orders.close',
            ],
        ];
    }

    public function posConfig(): array
    {
        return [
            'layout' => 'grid',
            'product_search_columns' => ['name', 'sku', 'category'],
            'quick_actions' => [
                ['label' => 'Dine-in', 'action' => 'set_dine_in'],
                ['label' => 'Takeaway', 'action' => 'set_takeaway'],
                ['label' => 'Delivery', 'action' => 'set_delivery'],
            ],
            'checkout_fields' => [
                ['slug' => 'table_number', 'label' => 'Table #', 'required' => false],
                ['slug' => 'guest_count', 'label' => 'Guests', 'required' => false, 'type' => 'number'],
                ['slug' => 'server_name', 'label' => 'Server', 'required' => true],
                ['slug' => 'order_note', 'label' => 'Special Instructions', 'required' => false],
            ],
            'has_tables' => true,
            'has_takeaway' => true,
            'has_delivery' => true,
            'has_kitchen_display' => true,
            'receipt_fields' => ['items', 'table', 'server', 'subtotal', 'tax', 'total'],
            'tender_types' => ['cash', 'card', 'mobile_banking'],
        ];
    }

    public function dashboardWidgets(): array
    {
        return [
            'todays_revenue' => [
                'component' => 'RevenueSummary',
                'title' => 'Today\'s Revenue',
                'width' => 'half',
                'permission' => 'reports.view',
            ],
            'active_orders' => [
                'component' => 'ActiveOrdersList',
                'title' => 'Active Orders',
                'width' => 'half',
                'permission' => 'orders.view',
            ],
            'popular_items' => [
                'component' => 'PopularItemsTable',
                'title' => 'Popular Menu Items',
                'width' => 'full',
                'permission' => 'reports.view',
            ],
            'food_cost_percentage' => [
                'component' => 'FoodCostGauge',
                'title' => 'Food Cost %',
                'width' => 'half',
                'permission' => 'reports.view',
            ],
            'kitchen_queue' => [
                'component' => 'KitchenQueueStatus',
                'title' => 'Kitchen Queue',
                'width' => 'half',
                'permission' => 'kitchen.view',
            ],
        ];
    }

    public function reportDefinitions(): array
    {
        return [
            'sales' => [
                'name' => 'Sales Report',
                'description' => 'Daily, weekly, and monthly sales breakdown',
                'permission' => 'reports.view',
                'filters' => ['date_range', 'order_type', 'payment_method'],
                'export_formats' => ['pdf', 'csv', 'xlsx'],
            ],
            'food-cost' => [
                'name' => 'Food Cost Report',
                'description' => 'Cost analysis by menu item and category',
                'permission' => 'reports.view',
                'filters' => ['date_range', 'category'],
                'export_formats' => ['pdf', 'csv', 'xlsx'],
            ],
            'kitchen-performance' => [
                'name' => 'Kitchen Performance',
                'description' => 'Order preparation times and kitchen efficiency',
                'permission' => 'reports.view',
                'filters' => ['date_range', 'prep_station'],
                'export_formats' => ['pdf', 'csv'],
            ],
            'popular-items' => [
                'name' => 'Popular Menu Items',
                'description' => 'Top selling items by volume and revenue',
                'permission' => 'reports.view',
                'filters' => ['date_range', 'category'],
                'export_formats' => ['pdf', 'csv', 'xlsx'],
            ],
        ];
    }

    public function defaultSettings(): array
    {
        return [
            'currency' => 'BDT',
            'default_tip_percentage' => 5,
            'service_charge_percentage' => 10,
            'vat_percentage' => 5,
            'auto_send_to_kitchen' => true,
            'default_prep_time_minutes' => 15,
        ];
    }

    public function featureFlags(): array
    {
        return [
            'table_management',
            'kitchen_display',
            'recipe_management',
            'online_ordering',
            'delivery_tracking',
            'reservation_management',
        ];
    }

    public function onTenantAssigned(Tenant $tenant): void {}

    public function onTenantRemoved(Tenant $tenant): void {}
}

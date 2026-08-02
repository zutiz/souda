<?php

declare(strict_types=1);

namespace App\Modules\BusinessType\Packs;

use App\Models\Tenant;
use App\Modules\BusinessType\Contracts\IndustryPack;

class ElectronicsPack implements IndustryPack
{
    public function slug(): string
    {
        return 'electronics';
    }

    public function name(): string
    {
        return 'Electronics';
    }

    public function description(): string
    {
        return 'Electronics and gadget store with IMEI tracking, warranty management, and serial number tracking';
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
            'supplier' => ['required' => true],
            'reporting' => ['required' => true],
        ];
    }

    public function menus(): array
    {
        return [
            'main' => [
                ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'LayoutDashboard'],
                ['label' => 'Products', 'route' => 'products.index', 'icon' => 'Monitor', 'children' => [
                    ['label' => 'All Products', 'route' => 'products.index'],
                    ['label' => 'Categories', 'route' => 'categories.index'],
                    ['label' => 'Brands', 'route' => 'brands.index'],
                    ['label' => 'IMEI Tracking', 'route' => 'electronics.imei'],
                    ['label' => 'Warranty Management', 'route' => 'electronics.warranty'],
                ]],
                ['label' => 'Inventory', 'route' => 'inventory.index', 'icon' => 'Package', 'children' => [
                    ['label' => 'Stock', 'route' => 'inventory.index'],
                    ['label' => 'Serial Numbers', 'route' => 'electronics.serials'],
                    ['label' => 'Stock Movements', 'route' => 'stock.movements'],
                    ['label' => 'Stock Transfers', 'route' => 'stock.transfers'],
                ]],
                ['label' => 'Sales', 'route' => 'pos.index', 'icon' => 'ShoppingCart', 'children' => [
                    ['label' => 'POS', 'route' => 'pos.index'],
                    ['label' => 'Orders', 'route' => 'orders.index'],
                    ['label' => 'Warranty Claims', 'route' => 'electronics.warranty-claims'],
                ]],
                ['label' => 'Customers', 'route' => 'customers.index', 'icon' => 'Users'],
                ['label' => 'Suppliers', 'route' => 'suppliers.index', 'icon' => 'Truck'],
                ['label' => 'Reports', 'route' => 'reports.index', 'icon' => 'BarChart3', 'children' => [
                    ['label' => 'Sales Report', 'route' => 'reports.sales'],
                    ['label' => 'Warranty Report', 'route' => 'reports.warranty'],
                    ['label' => 'IMEI Report', 'route' => 'reports.imei'],
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
                'suppliers.*',
                'reports.*',
                'settings.*',
                'electronics.*',
            ],
            'manager' => [
                'products.*',
                'inventory.view',
                'inventory.stock.adjust',
                'pos.*',
                'orders.*',
                'customers.*',
                'reports.view',
                'electronics.imei.view',
                'electronics.warranty.*',
            ],
            'salesperson' => [
                'pos.*',
                'orders.create',
                'orders.view',
                'customers.create',
                'customers.view',
                'products.view',
                'electronics.imei.register',
                'electronics.warranty.register',
            ],
            'technician' => [
                'electronics.warranty-claims.*',
                'products.view',
                'inventory.view',
                'electronics.serials.view',
            ],
        ];
    }

    public function posConfig(): array
    {
        return [
            'layout' => 'grid',
            'product_search_columns' => ['name', 'sku', 'barcode', 'model', 'brand'],
            'quick_actions' => [
                ['label' => 'Scan IMEI', 'action' => 'scan_imei'],
                ['label' => 'Register Warranty', 'action' => 'register_warranty'],
            ],
            'checkout_fields' => [
                ['slug' => 'imei_number', 'label' => 'IMEI', 'required' => false],
                ['slug' => 'serial_number', 'label' => 'Serial #', 'required' => false],
                ['slug' => 'warranty_period', 'label' => 'Warranty (months)', 'required' => false, 'type' => 'number'],
                ['slug' => 'customer_phone', 'label' => 'Customer Phone', 'required' => true],
            ],
            'imei_scanning' => true,
            'serial_tracking' => true,
            'warranty_registration' => true,
            'receipt_fields' => ['items', 'serial', 'imei', 'warranty', 'total'],
            'tender_types' => ['cash', 'card', 'mobile_banking', 'installment'],
        ];
    }

    public function dashboardWidgets(): array
    {
        return [
            'today_sales' => [
                'component' => 'TodaySalesSummary',
                'title' => 'Today\'s Sales',
                'width' => 'half',
                'permission' => 'reports.view',
            ],
            'active_warranties' => [
                'component' => 'ActiveWarrantiesCount',
                'title' => 'Active Warranties',
                'width' => 'half',
                'permission' => 'electronics.warranty.view',
            ],
            'top_brands' => [
                'component' => 'TopBrandsTable',
                'title' => 'Top Selling Brands',
                'width' => 'full',
                'permission' => 'reports.view',
            ],
        ];
    }

    public function reportDefinitions(): array
    {
        return [
            'sales' => [
                'name' => 'Sales Report',
                'description' => 'Sales by product category, brand, and time period',
                'permission' => 'reports.view',
                'filters' => ['date_range', 'brand', 'category'],
                'export_formats' => ['pdf', 'csv', 'xlsx'],
            ],
            'warranty' => [
                'name' => 'Warranty Report',
                'description' => 'Active and expired warranties',
                'permission' => 'reports.view',
                'filters' => ['date_range', 'status'],
                'export_formats' => ['pdf', 'csv', 'xlsx'],
            ],
            'imei' => [
                'name' => 'IMEI/Serial Tracking',
                'description' => 'Track IMEI and serial numbers through lifecycle',
                'permission' => 'reports.view',
                'filters' => ['status', 'product'],
                'export_formats' => ['pdf', 'csv'],
            ],
        ];
    }

    public function defaultSettings(): array
    {
        return [
            'currency' => 'BDT',
            'default_warranty_months' => 12,
            'require_imei_for_mobiles' => true,
            'auto_generate_serials' => false,
        ];
    }

    public function featureFlags(): array
    {
        return [
            'imei_tracking',
            'serial_number_tracking',
            'warranty_management',
            'installment_sales',
            'low_stock_alerts',
            'stock_transfers',
            'cycle_counting',
            'dead_stock_detection',
        ];
    }

    public function branding(): array
    {
        return [
            'primary' => 'oklch(0.55 0.18 250)',     // Tech blue
            'primary_foreground' => 'oklch(0.985 0 0)',
            'accent' => 'oklch(0.20 0.08 250)',      // Dark accent
            'accent_foreground' => 'oklch(0.985 0 0)',
            'sidebar' => 'oklch(0.18 0 0)',
            'sidebar_foreground' => 'oklch(0.985 0 0)',
            'sidebar_accent' => 'oklch(0.30 0.08 250)',
            'radius' => '0.375rem',                  // Sharp, tech
        ];
    }

    public function onTenantAssigned(Tenant $tenant): void {}

    public function onTenantRemoved(Tenant $tenant): void {}
}

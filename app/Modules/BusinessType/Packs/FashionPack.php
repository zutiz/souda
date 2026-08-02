<?php

declare(strict_types=1);

namespace App\Modules\BusinessType\Packs;

use App\Models\Tenant;
use App\Modules\BusinessType\Contracts\IndustryPack;

class FashionPack implements IndustryPack
{
    public function slug(): string
    {
        return 'fashion';
    }

    public function name(): string
    {
        return 'Fashion';
    }

    public function description(): string
    {
        return 'Fashion retail with size/color matrix inventory, seasonal collections, and multi-variant management';
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
            'reporting' => ['required' => true],
        ];
    }

    public function menus(): array
    {
        return [
            'main' => [
                ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'LayoutDashboard'],
                ['label' => 'Products', 'route' => 'products.index', 'icon' => 'Shirt', 'children' => [
                    ['label' => 'All Products', 'route' => 'products.index'],
                    ['label' => 'Categories', 'route' => 'categories.index'],
                    ['label' => 'Brands', 'route' => 'brands.index'],
                    ['label' => 'Collections', 'route' => 'products.collections'],
                    ['label' => 'Seasons', 'route' => 'products.seasons'],
                ]],
                ['label' => 'Inventory', 'route' => 'inventory.index', 'icon' => 'Package', 'children' => [
                    ['label' => 'Stock by Size/Color', 'route' => 'inventory.size-color'],
                    ['label' => 'Stock Movements', 'route' => 'stock.movements'],
                    ['label' => 'Low Stock', 'route' => 'inventory.low-stock'],
                ]],
                ['label' => 'Sales', 'route' => 'pos.index', 'icon' => 'ShoppingCart', 'children' => [
                    ['label' => 'POS', 'route' => 'pos.index'],
                    ['label' => 'Orders', 'route' => 'orders.index'],
                ]],
                ['label' => 'Customers', 'route' => 'customers.index', 'icon' => 'Users'],
                ['label' => 'Reports', 'route' => 'reports.index', 'icon' => 'BarChart3', 'children' => [
                    ['label' => 'Sales Report', 'route' => 'reports.sales'],
                    ['label' => 'Size Distribution', 'route' => 'reports.size-distribution'],
                    ['label' => 'Color Popularity', 'route' => 'reports.color-popularity'],
                    ['label' => 'Seasonal Analysis', 'route' => 'reports.seasonal-analysis'],
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
                'reports.*',
                'settings.*',
            ],
            'manager' => [
                'products.*',
                'inventory.view',
                'inventory.stock.adjust',
                'pos.*',
                'orders.*',
                'customers.*',
                'reports.view',
            ],
            'salesperson' => [
                'pos.*',
                'orders.create',
                'orders.view',
                'customers.create',
                'products.view',
            ],
        ];
    }

    public function posConfig(): array
    {
        return [
            'layout' => 'grid',
            'product_search_columns' => ['name', 'sku', 'barcode', 'brand'],
            'quick_actions' => [
                ['label' => 'Select Size', 'action' => 'select_size'],
                ['label' => 'Select Color', 'action' => 'select_color'],
            ],
            'checkout_fields' => [
                ['slug' => 'size', 'label' => 'Size', 'required' => true],
                ['slug' => 'color', 'label' => 'Color', 'required' => true],
                ['slug' => 'quantity', 'label' => 'Qty', 'required' => true, 'type' => 'number'],
            ],
            'has_variant_selection' => true,
            'has_size_chart' => true,
            'has_color_swatches' => true,
            'receipt_fields' => ['items', 'size', 'color', 'subtotal', 'total'],
            'tender_types' => ['cash', 'card', 'mobile_banking'],
        ];
    }

    public function dashboardWidgets(): array
    {
        return [
            'today_revenue' => [
                'component' => 'RevenueSummary',
                'title' => 'Today\'s Revenue',
                'width' => 'half',
                'permission' => 'reports.view',
            ],
            'popular_sizes' => [
                'component' => 'PopularSizesChart',
                'title' => 'Popular Sizes',
                'width' => 'half',
                'permission' => 'reports.view',
            ],
            'color_trends' => [
                'component' => 'ColorTrendsChart',
                'title' => 'Color Trends',
                'width' => 'half',
                'permission' => 'reports.view',
            ],
            'top_products' => [
                'component' => 'TopSellingTable',
                'title' => 'Top Selling Products',
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
                'description' => 'Sales by product, category, and time period',
                'permission' => 'reports.view',
                'filters' => ['date_range', 'category', 'brand'],
                'export_formats' => ['pdf', 'csv', 'xlsx'],
            ],
            'size-distribution' => [
                'name' => 'Size Distribution',
                'description' => 'Sales distribution across sizes',
                'permission' => 'reports.view',
                'filters' => ['date_range', 'category'],
                'export_formats' => ['pdf', 'csv', 'xlsx'],
            ],
            'color-popularity' => [
                'name' => 'Color Popularity',
                'description' => 'Sales by color across products',
                'permission' => 'reports.view',
                'filters' => ['date_range', 'category'],
                'export_formats' => ['pdf', 'csv', 'xlsx'],
            ],
            'seasonal-analysis' => [
                'name' => 'Seasonal Analysis',
                'description' => 'Seasonal sales trends and forecasting',
                'permission' => 'reports.view',
                'filters' => ['season', 'year'],
                'export_formats' => ['pdf', 'csv', 'xlsx'],
            ],
        ];
    }

    public function defaultSettings(): array
    {
        return [
            'currency' => 'BDT',
            'default_size_system' => 'standard', // standard, US, UK, EU
            'track_by_size_color' => true,
            'enable_seasonal_collections' => true,
        ];
    }

    public function featureFlags(): array
    {
        return [
            'size_color_matrix',
            'seasonal_collections',
            'multi_variant_inventory',
            'size_chart_management',
            'low_stock_alerts',
            'stock_transfers',
            'cycle_counting',
            'dead_stock_detection',
            'overstock_detection',
        ];
    }

    public function branding(): array
    {
        return [
            'primary' => 'oklch(0.15 0 0)',          // Black elegant
            'primary_foreground' => 'oklch(0.985 0 0)',
            'accent' => 'oklch(0.60 0.02 0)',        // Warm gray
            'accent_foreground' => 'oklch(0.985 0 0)',
            'sidebar' => 'oklch(0.22 0 0)',
            'sidebar_foreground' => 'oklch(0.985 0 0)',
            'sidebar_accent' => 'oklch(0.35 0 0)',
            'radius' => '0.375rem',                  // Sharp, minimal
        ];
    }

    public function onTenantAssigned(Tenant $tenant): void {}

    public function onTenantRemoved(Tenant $tenant): void {}
}

<?php

declare(strict_types=1);

namespace App\Modules\BusinessType\Packs;

use App\Models\Tenant;
use App\Modules\BusinessType\Contracts\IndustryPack;

class DistributionPack implements IndustryPack
{
    public function slug(): string
    {
        return 'distribution';
    }

    public function name(): string
    {
        return 'Distribution';
    }

    public function description(): string
    {
        return 'Product distribution and logistics with fleet management, route planning, and delivery tracking';
    }

    public function modules(): array
    {
        return [
            'product' => ['required' => true],
            'inventory' => ['required' => true],
            'order' => ['required' => true],
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
                ['label' => 'Products', 'route' => 'products.index', 'icon' => 'Package', 'children' => [
                    ['label' => 'All Products', 'route' => 'products.index'],
                    ['label' => 'Categories', 'route' => 'categories.index'],
                ]],
                ['label' => 'Inventory', 'route' => 'inventory.index', 'icon' => 'Warehouse', 'children' => [
                    ['label' => 'Stock', 'route' => 'inventory.index'],
                    ['label' => 'Warehouses', 'route' => 'warehouses.index'],
                    ['label' => 'Stock Transfers', 'route' => 'stock.transfers'],
                ]],
                ['label' => 'Orders', 'route' => 'orders.index', 'icon' => 'ClipboardList', 'children' => [
                    ['label' => 'All Orders', 'route' => 'orders.index'],
                    ['label' => 'Delivery Schedule', 'route' => 'distribution.deliveries'],
                ]],
                ['label' => 'Customers', 'route' => 'customers.index', 'icon' => 'Building2'],
                ['label' => 'Suppliers', 'route' => 'suppliers.index', 'icon' => 'Truck'],
                ['label' => 'Reports', 'route' => 'reports.index', 'icon' => 'BarChart3'],
                ['label' => 'Settings', 'route' => 'settings.index', 'icon' => 'Settings'],
            ],
        ];
    }

    public function permissions(): array
    {
        return [
            'admin' => ['products.*', 'inventory.*', 'orders.*', 'customers.*', 'suppliers.*', 'reports.*', 'settings.*', 'distribution.*'],
            'manager' => ['products.*', 'inventory.view', 'orders.*', 'customers.*', 'reports.view'],
            'logistics' => ['orders.view', 'distribution.deliveries.*', 'inventory.view'],
        ];
    }

    public function posConfig(): array
    {
        return [];
    }

    public function dashboardWidgets(): array
    {
        return [
            'pending_deliveries' => ['component' => 'PendingDeliveriesWidget', 'title' => 'Pending Deliveries', 'width' => 'half', 'permission' => 'orders.view'],
            'inventory_summary' => ['component' => 'InventorySummaryWidget', 'title' => 'Inventory Summary', 'width' => 'half', 'permission' => 'inventory.view'],
        ];
    }

    public function reportDefinitions(): array
    {
        return [
            'sales' => ['name' => 'Sales Report', 'description' => 'Distribution sales by region', 'permission' => 'reports.view', 'filters' => ['date_range'], 'export_formats' => ['pdf', 'csv', 'xlsx']],
            'delivery-performance' => ['name' => 'Delivery Performance', 'description' => 'On-time delivery metrics', 'permission' => 'reports.view', 'filters' => ['date_range'], 'export_formats' => ['pdf', 'csv']],
        ];
    }

    public function defaultSettings(): array
    {
        return ['currency' => 'BDT', 'enable_delivery_tracking' => true];
    }

    public function featureFlags(): array
    {
        return ['delivery_tracking', 'warehouse_transfers', 'route_planning'];
    }

    public function onTenantAssigned(Tenant $tenant): void {}

    public function onTenantRemoved(Tenant $tenant): void {}
}

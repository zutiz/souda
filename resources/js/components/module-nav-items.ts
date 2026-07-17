import {
    BarChart3,
    Calendar,
    CreditCard,
    Package,
    ShoppingCart,
    Truck,
    type LucideIcon,
    Users,
    UtensilsCrossed,
    Warehouse,
} from 'lucide-react';
import type { NavItem, NavSubItem } from '@/types';

export type ModuleNavDef = {
    label: string;
    icon: LucideIcon;
    items: NavSubItem[];
};

export const moduleNavMap: Record<string, ModuleNavDef> = {
    product: {
        label: 'Products',
        icon: Package,
        items: [
            { title: 'All Products', href: '/products' },
            { title: 'Categories', href: '/products/categories' },
            { title: 'Brands', href: '/products/brands' },
            { title: 'Attributes', href: '/products/attributes' },
        ],
    },
    inventory: {
        label: 'Inventory',
        icon: Warehouse,
        items: [
            { title: 'Dashboard', href: '/inventory' },
            { title: 'Balances', href: '/inventory/balances' },
            { title: 'Movements', href: '/inventory/movements' },
            { title: 'Batches', href: '/inventory/batches' },
            { title: 'Serial Numbers', href: '/inventory/serials' },
            { title: 'Reservations', href: '/inventory/reservations' },
            { title: 'Transfers', href: '/inventory/transfers' },
            { title: 'Alerts', href: '/inventory/alerts' },
            { title: 'Classification', href: '/inventory/classification' },
            { title: 'Forecasts', href: '/inventory/forecasts' },
            { title: 'Counts', href: '/inventory/counts' },
            { title: 'Suggestions', href: '/inventory/suggestions' },
            { title: 'Operations', href: '/inventory/operations' },
        ],
    },
    order: {
        label: 'Orders',
        icon: ShoppingCart,
        items: [
            { title: 'All Orders', href: '/orders' },
        ],
    },
    pos: {
        label: 'POS',
        icon: CreditCard,
        items: [
            { title: 'Register', href: '/pos' },
        ],
    },
    crm: {
        label: 'CRM',
        icon: Users,
        items: [
            { title: 'Customers', href: '/crm/customers' },
            { title: 'Segments', href: '/crm/segments' },
        ],
    },
    billing: {
        label: 'Billing',
        icon: CreditCard,
        items: [
            { title: 'Subscriptions', href: '/billing' },
            { title: 'Invoices', href: '/billing/invoices' },
        ],
    },
    team: {
        label: 'Team',
        icon: Users,
        items: [
            { title: 'Members', href: '/team' },
        ],
    },
    supplier: {
        label: 'Suppliers',
        icon: Truck,
        items: [
            { title: 'Vendors', href: '/suppliers' },
            { title: 'Purchase Orders', href: '/suppliers/purchase-orders' },
        ],
    },
    kitchen: {
        label: 'Kitchen',
        icon: UtensilsCrossed,
        items: [
            { title: 'Display', href: '/kitchen' },
        ],
    },
    appointment: {
        label: 'Appointments',
        icon: Calendar,
        items: [
            { title: 'Calendar', href: '/appointments' },
            { title: 'Services', href: '/appointments/services' },
        ],
    },
    reporting: {
        label: 'Reports',
        icon: BarChart3,
        items: [
            { title: 'Dashboard', href: '/reports' },
        ],
    },
};

export function buildModuleNavItems(enabledModules: string[]): NavItem[] {
    return enabledModules
        .map((slug) => {
            const def = moduleNavMap[slug];
            if (!def) return null;

            return {
                title: def.label,
                href: def.items[0]?.href ?? '#',
                icon: def.icon,
                items: def.items,
            } satisfies NavItem;
        })
        .filter((item): item is NavItem => item !== null);
}

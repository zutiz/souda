import { Link, usePage } from '@inertiajs/react';
import {
    CreditCard,
    LayoutGrid,
    ListTodo,
    Package,
    PanelLeft,
    Settings,
    Users,
} from 'lucide-react';
import { edit as adminSettingsGeneral } from '@/actions/App/Http/Controllers/Admin/AppSettingsController';
import AdminDashboard from '@/actions/App/Http/Controllers/Admin/DashboardController';
import { index as pricingIndex } from '@/actions/App/Http/Controllers/Admin/PlanController';
import { index as usersIndex } from '@/actions/App/Http/Controllers/Admin/UserController';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import type { NavItem } from '@/types';
import AppLogo from './app-logo';

const adminNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: AdminDashboard.url(),
        icon: PanelLeft,
    },
    {
        title: 'Users',
        href: usersIndex(),
        icon: Users,
    },
    {
        title: 'Pricing',
        href: pricingIndex(),
        icon: CreditCard,
    },
    {
        title: 'Settings',
        href: adminSettingsGeneral(),
        icon: Settings,
    },
];

export function AppSidebar() {
    const { auth } = usePage<{
        auth: { is_admin: boolean };
    }>().props;
    const dashboardHref = '/dashboard';

    const mainNavItems: NavItem[] = [
        {
            title: 'Dashboard',
            href: dashboardHref,
            icon: LayoutGrid,
        },
        {
            title: 'Tasks',
            href: '/tasks',
            icon: ListTodo,
        },
    ];

    const productNavItems: NavItem[] = [
        {
            title: 'Products',
            href: '/products',
            icon: Package,
            items: [
                { title: 'All Products', href: '/products' },
                { title: 'Categories', href: '/products/categories' },
                { title: 'Brands', href: '/products/brands' },
                { title: 'Attributes', href: '/products/attributes' },
                { title: 'Inventory', href: '/products/inventory' },
                { title: 'Stock Transfers', href: '/products/stock-transfers' },
            ],
        },
    ];

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboardHref} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={mainNavItems} />
                <NavMain items={productNavItems} label="Products" />
            </SidebarContent>

            <SidebarFooter>
                {auth.is_admin && (
                    <NavMain items={adminNavItems} label="Admin" />
                )}
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}

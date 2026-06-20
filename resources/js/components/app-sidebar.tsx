import { Link, usePage } from '@inertiajs/react';
import {
    CreditCard,
    LayoutGrid,
    ListTodo,
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
import { useEnabledModules } from '@/hooks/use-tenant-config';
import AppLogo from './app-logo';
import { buildModuleNavItems } from './module-nav-items';

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
    const enabledModules = useEnabledModules();
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

    const moduleNavItems = buildModuleNavItems(enabledModules);

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
                {moduleNavItems.length > 0 && (
                    <NavMain items={moduleNavItems} label="Modules" />
                )}
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

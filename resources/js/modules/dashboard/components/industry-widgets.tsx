import {
    ShoppingCart,
    Package,
    Calendar,
    CreditCard,
    Truck,
    UtensilsCrossed,
    Scissors,
    Sparkles,
    Monitor,
    Shirt,
    Palette,
    Hammer,
    Warehouse,
    Sprout,
    BookOpen,
    Coffee,
    Cake,
    Pill,
    ChevronRight,
    Users,
    BarChart3,
    type LucideIcon,
} from 'lucide-react';
import { Link } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { cn } from '@/lib/utils';
import { useEnabledModules, useTenantConfig } from '@/hooks/use-tenant-config';

type IndustryInfo = {
    name: string;
    icon: LucideIcon;
    greeting: string;
    description: string;
};

type ModuleAction = {
    title: string;
    description: string;
    href: string;
    icon: LucideIcon;
};

const industryMap: Record<string, IndustryInfo> = {
    grocery: {
        name: 'Grocery',
        icon: ShoppingCart,
        greeting: 'Welcome to your Grocery Store',
        description: 'Manage inventory, process sales, and track suppliers.',
    },
    pharmacy: {
        name: 'Pharmacy',
        icon: Pill,
        greeting: 'Welcome to your Pharmacy',
        description: 'Manage prescriptions, stock, and customer records.',
    },
    restaurant: {
        name: 'Restaurant',
        icon: UtensilsCrossed,
        greeting: 'Welcome to your Restaurant',
        description: 'Manage your menu, orders, kitchen, and dining experience.',
    },
    cafe: {
        name: 'Cafe',
        icon: Coffee,
        greeting: 'Welcome to your Cafe',
        description: 'Manage your menu, orders, and customer favorites.',
    },
    bakery: {
        name: 'Bakery',
        icon: Cake,
        greeting: 'Welcome to your Bakery',
        description: 'Track recipes, inventory, and daily specials.',
    },
    salon: {
        name: 'Salon',
        icon: Scissors,
        greeting: 'Welcome to your Salon',
        description: 'Manage appointments, services, and client profiles.',
    },
    spa: {
        name: 'Spa',
        icon: Sparkles,
        greeting: 'Welcome to your Spa',
        description: 'Manage treatments, bookings, and wellness packages.',
    },
    electronics: {
        name: 'Electronics',
        icon: Monitor,
        greeting: 'Welcome to your Electronics Store',
        description: 'Track products, warranties, and supplier orders.',
    },
    fashion: {
        name: 'Fashion',
        icon: Shirt,
        greeting: 'Welcome to your Fashion Store',
        description: 'Manage collections, sizes, and seasonal inventory.',
    },
    cosmetics: {
        name: 'Cosmetics',
        icon: Palette,
        greeting: 'Welcome to your Cosmetics Store',
        description: 'Track beauty products, brands, and customer preferences.',
    },
    hardware: {
        name: 'Hardware',
        icon: Hammer,
        greeting: 'Welcome to your Hardware Store',
        description: 'Manage tools, materials, and contractor accounts.',
    },
    wholesale: {
        name: 'Wholesale',
        icon: Warehouse,
        greeting: 'Welcome to your Wholesale Business',
        description: 'Manage bulk pricing, distributors, and large orders.',
    },
    distribution: {
        name: 'Distribution',
        icon: Truck,
        greeting: 'Welcome to your Distribution Center',
        description: 'Track logistics, fleet, and supply chain operations.',
    },
    agro_shop: {
        name: 'Agro Shop',
        icon: Sprout,
        greeting: 'Welcome to your Agro Shop',
        description: 'Manage agricultural supplies, seeds, and equipment.',
    },
    bookstore: {
        name: 'Bookstore',
        icon: BookOpen,
        greeting: 'Welcome to your Bookstore',
        description: 'Catalog books, track sales, and manage author events.',
    },
};

function getIndustryInfo(businessType: string | null): IndustryInfo {
    if (businessType && industryMap[businessType]) {
        return industryMap[businessType];
    }

    return {
        name: 'Business',
        icon: Package,
        greeting: 'Welcome to your Dashboard',
        description: 'Manage your business operations efficiently.',
    };
}

const moduleActionMap: Record<string, ModuleAction> = {
    product: {
        title: 'Products',
        description: 'Manage your product catalog',
        href: '/products',
        icon: Package,
    },
    inventory: {
        title: 'Inventory',
        description: 'Track stock levels and movements',
        href: '/inventory',
        icon: Warehouse,
    },
    order: {
        title: 'Orders',
        description: 'View and fulfill orders',
        href: '/orders',
        icon: ShoppingCart,
    },
    pos: {
        title: 'Point of Sale',
        description: 'Process sales transactions',
        href: '/pos',
        icon: CreditCard,
    },
    crm: {
        title: 'Customers',
        description: 'Manage customer relationships',
        href: '/crm/customers',
        icon: Users,
    },
    billing: {
        title: 'Billing',
        description: 'View subscriptions and invoices',
        href: '/billing',
        icon: CreditCard,
    },
    team: {
        title: 'Team',
        description: 'Manage your team members',
        href: '/team',
        icon: Users,
    },
    supplier: {
        title: 'Suppliers',
        description: 'Manage vendors and purchases',
        href: '/suppliers',
        icon: Truck,
    },
    kitchen: {
        title: 'Kitchen',
        description: 'Track order preparation',
        href: '/kitchen',
        icon: UtensilsCrossed,
    },
    appointment: {
        title: 'Appointments',
        description: 'Manage bookings and schedule',
        href: '/appointments',
        icon: Calendar,
    },
    reporting: {
        title: 'Reports',
        description: 'View business analytics',
        href: '/reports',
        icon: BarChart3,
    },
};

export function IndustryGreeting() {
    const config = useTenantConfig();
    const info = getIndustryInfo(config?.business_type ?? null);

    return (
        <Card className="overflow-hidden">
            <div className="bg-gradient-to-r from-primary/10 via-primary/5 to-transparent p-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div className="flex items-center gap-4">
                        <div className="flex size-14 items-center justify-center rounded-xl bg-primary/15 shadow-sm">
                            <info.icon className="size-7 text-primary" />
                        </div>
                        <div>
                            <CardTitle className="text-xl font-semibold tracking-tight">
                                {info.greeting}
                            </CardTitle>
                            <p className="mt-1 text-sm text-muted-foreground">
                                {info.description}
                            </p>
                        </div>
                    </div>
                    <div className="flex items-center gap-2 text-sm text-muted-foreground">
                        <span>{new Date().toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric' })}</span>
                    </div>
                </div>
            </div>
        </Card>
    );
}

export function ModuleQuickActions() {
    const enabledModules = useEnabledModules();

    const visibleModules = enabledModules
        .filter((slug) => moduleActionMap[slug])
        .slice(0, 6);

    if (visibleModules.length === 0) return null;

    return (
        <div className="space-y-3">
            <div className="flex items-center justify-between">
                <h3 className="text-sm font-medium text-muted-foreground">
                    Quick Actions
                </h3>
                <span className="text-xs text-muted-foreground">
                    {visibleModules.length} modules active
                </span>
            </div>
            <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                {visibleModules.map((slug) => {
                    const action = moduleActionMap[slug];
                    const Icon = action.icon;

                    return (
                        <Link
                            key={slug}
                            href={action.href}
                            className="group block"
                        >
                            <Card className="h-full transition-all duration-200 hover:border-primary/50 hover:shadow-md hover:-translate-y-0.5">
                                <CardContent className="flex items-center gap-4 p-4">
                                    <div className="flex size-11 shrink-0 items-center justify-center rounded-lg bg-primary/10 transition-colors group-hover:bg-primary/15">
                                        <Icon className="size-5 text-primary" />
                                    </div>
                                    <div className="min-w-0 flex-1">
                                        <div className="flex items-center gap-1">
                                            <p className="text-sm font-medium group-hover:text-primary transition-colors">
                                                {action.title}
                                            </p>
                                            <ChevronRight className="size-3 text-muted-foreground opacity-0 transition-opacity group-hover:opacity-100" />
                                        </div>
                                        <p className="text-xs text-muted-foreground truncate">
                                            {action.description}
                                        </p>
                                    </div>
                                </CardContent>
                            </Card>
                        </Link>
                    );
                })}
            </div>
        </div>
    );
}

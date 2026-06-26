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
    type LucideIcon,
} from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { useEnabledModules, useTenantConfig } from '@/hooks/use-tenant-config';

type IndustryInfo = {
    name: string;
    icon: LucideIcon;
    greeting: string;
    description: string;
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

const moduleActionMap: Record<string, { title: string; description: string }> = {
    product: { title: 'Products', description: 'Manage your product catalog' },
    inventory: { title: 'Inventory', description: 'Track stock levels and movements' },
    order: { title: 'Orders', description: 'View and fulfill orders' },
    pos: { title: 'Point of Sale', description: 'Process sales transactions' },
    crm: { title: 'Customers', description: 'Manage customer relationships' },
    billing: { title: 'Billing', description: 'View subscriptions and invoices' },
    team: { title: 'Team', description: 'Manage your team members' },
    supplier: { title: 'Suppliers', description: 'Manage vendors and purchases' },
    kitchen: { title: 'Kitchen', description: 'Track order preparation' },
    appointment: { title: 'Appointments', description: 'Manage bookings and schedule' },
    reporting: { title: 'Reports', description: 'View business analytics' },
};

export function IndustryGreeting() {
    const config = useTenantConfig();
    const info = getIndustryInfo(config?.business_type ?? null);

    return (
        <Card>
            <CardHeader className="flex flex-row items-center gap-4">
                <div className="flex size-12 items-center justify-center rounded-lg bg-primary/10">
                    <info.icon className="size-6 text-primary" />
                </div>
                <div>
                    <CardTitle className="text-xl">{info.greeting}</CardTitle>
                    <p className="mt-1 text-sm text-muted-foreground">
                        {info.description}
                    </p>
                </div>
            </CardHeader>
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
            <h3 className="text-sm font-medium text-muted-foreground">
                Quick Actions
            </h3>
            <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                {visibleModules.map((slug) => {
                    const action = moduleActionMap[slug];

                    return (
                        <Card key={slug} className="transition-colors hover:border-primary/50">
                            <CardContent className="flex items-center gap-3 p-4">
                                <div className="flex size-10 items-center justify-center rounded-md bg-primary/10">
                                    <Package className="size-5 text-primary" />
                                </div>
                                <div>
                                    <p className="text-sm font-medium">
                                        {action.title}
                                    </p>
                                    <p className="text-xs text-muted-foreground">
                                        {action.description}
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                    );
                })}
            </div>
        </div>
    );
}

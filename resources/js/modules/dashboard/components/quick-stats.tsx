import { DollarSign, ShoppingBag, Package, Users } from 'lucide-react';
import { StatCard } from '@/modules/shared/components/stat-card';
import { useTenantConfig } from '@/hooks/use-tenant-config';

type QuickStat = {
    label: string;
    value: string | number;
    trend?: number;
    icon: typeof DollarSign;
    variant?: 'default' | 'positive' | 'warning' | 'danger';
};

interface QuickStatsProps {
    stats?: QuickStat[];
}

// Default stats based on industry
function getDefaultStats(businessType: string | null): QuickStat[] {
    // These can be customized based on industry pack
    const defaults: QuickStat[] = [
        {
            label: "Today's Sales",
            value: '$0.00',
            icon: DollarSign,
        },
        {
            label: "Today's Orders",
            value: '0',
            icon: ShoppingBag,
        },
        {
            label: 'Low Stock Items',
            value: '0',
            icon: Package,
            variant: 'warning' as const,
        },
        {
            label: 'Active Customers',
            value: '0',
            icon: Users,
        },
    ];

    // Industry-specific customization
    switch (businessType) {
        case 'restaurant':
            return [
                { ...defaults[0], label: "Table Revenue" },
                { ...defaults[1], label: 'Orders' },
                { ...defaults[2], label: 'Kitchen Queue' },
                { ...defaults[3], label: 'Active Tables' },
            ];
        case 'pharmacy':
            return [
                { ...defaults[0] },
                { ...defaults[1], label: 'Prescriptions' },
                { label: 'Expiring Soon', value: '0', icon: Package, variant: 'warning' as const },
                { ...defaults[3] },
            ];
        case 'salon':
        case 'spa':
            return [
                { ...defaults[0], label: "Today's Revenue" },
                { label: 'Appointments', value: '0', icon: ShoppingBag },
                { ...defaults[2], label: 'Pending' },
                { label: 'Staff on Duty', value: '0', icon: Users },
            ];
        default:
            return defaults;
    }
}

export function QuickStats({ stats }: QuickStatsProps) {
    const config = useTenantConfig();
    const businessType = config?.business_type ?? null;

    const displayStats = stats ?? getDefaultStats(businessType);

    return (
        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            {displayStats.map((stat, index) => {
                const Icon = stat.icon;
                return (
                    <StatCard
                        key={index}
                        title={stat.label}
                        value={stat.value}
                        icon={Icon}
                        trend={stat.trend}
                        variant={stat.variant}
                    />
                );
            })}
        </div>
    );
}
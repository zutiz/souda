import { cn } from '@/lib/utils';
import { Badge } from '@/components/ui/badge';

// Common status types
export type StatusType =
    | 'active'
    | 'inactive'
    | 'pending'
    | 'processing'
    | 'completed'
    | 'cancelled'
    | 'draft'
    | 'published'
    | 'archived'
    | 'in_stock'
    | 'low_stock'
    | 'out_of_stock'
    | 'paid'
    | 'unpaid'
    | 'partial'
    | 'delivered'
    | 'shipped'
    | 'returned';

// Status badge configurations
const statusConfig: Record<StatusType, { label: string; className: string }> = {
    // General
    active: { label: 'Active', className: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 border-green-200 dark:border-green-800' },
    inactive: { label: 'Inactive', className: 'bg-muted text-muted-foreground border-transparent' },
    pending: { label: 'Pending', className: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400 border-yellow-200 dark:border-yellow-800' },
    processing: { label: 'Processing', className: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 border-blue-200 dark:border-blue-800' },
    completed: { label: 'Completed', className: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 border-green-200 dark:border-green-800' },
    cancelled: { label: 'Cancelled', className: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400 border-red-200 dark:border-red-800' },
    draft: { label: 'Draft', className: 'bg-muted text-muted-foreground border-transparent' },
    published: { label: 'Published', className: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 border-green-200 dark:border-green-800' },
    archived: { label: 'Archived', className: 'bg-gray-100 text-gray-600 dark:bg-gray-900/30 dark:text-gray-400 border-gray-200 dark:border-gray-800' },

    // Inventory
    in_stock: { label: 'In Stock', className: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 border-green-200 dark:border-green-800' },
    low_stock: { label: 'Low Stock', className: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400 border-yellow-200 dark:border-yellow-800' },
    out_of_stock: { label: 'Out of Stock', className: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400 border-red-200 dark:border-red-800' },

    // Payment
    paid: { label: 'Paid', className: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 border-green-200 dark:border-green-800' },
    unpaid: { label: 'Unpaid', className: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400 border-red-200 dark:border-red-800' },
    partial: { label: 'Partial', className: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400 border-yellow-200 dark:border-yellow-800' },

    // Order
    delivered: { label: 'Delivered', className: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 border-green-200 dark:border-green-800' },
    shipped: { label: 'Shipped', className: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 border-blue-200 dark:border-blue-800' },
    returned: { label: 'Returned', className: 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400 border-purple-200 dark:border-purple-800' },
};

// StatusBadge component with predefined statuses
export function StatusBadge({
    status,
    label,
    customClassName,
    size = 'default',
}: {
    status: StatusType;
    label?: string;
    customClassName?: string;
    size?: 'sm' | 'default';
}) {
    const config = statusConfig[status] ?? { label: status, className: '' };

    return (
        <Badge
            className={cn(
                'font-medium border',
                size === 'sm' && 'text-[10px] px-1.5 py-0',
                config.className,
                customClassName
            )}
        >
            <span className={cn(
                'size-1.5 rounded-full mr-1.5',
                status === 'active' || status === 'completed' || status === 'published' || status === 'in_stock' || status === 'paid' || status === 'delivered' ? 'bg-green-600 dark:bg-green-400' :
                status === 'pending' || status === 'processing' || status === 'low_stock' || status === 'partial' || status === 'shipped' ? 'bg-yellow-600 dark:bg-yellow-400' :
                status === 'cancelled' || status === 'unpaid' || status === 'out_of_stock' ? 'bg-red-600 dark:bg-red-400' :
                status === 'returned' ? 'bg-purple-600 dark:bg-purple-400' :
                'bg-current'
            )} />
            {label ?? config.label}
        </Badge>
    );
}

// Custom status badge with your own colors
export function CustomStatusBadge({
    children,
    variant = 'default',
    dotColor,
    className,
}: {
    children: React.ReactNode;
    variant?: 'success' | 'warning' | 'error' | 'info' | 'default' | 'outline';
    dotColor?: string;
    className?: string;
}) {
    const variantClasses = {
        success: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 border-green-200 dark:border-green-800',
        warning: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400 border-yellow-200 dark:border-yellow-800',
        error: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400 border-red-200 dark:border-red-800',
        info: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 border-blue-200 dark:border-blue-800',
        default: 'bg-muted text-muted-foreground border-transparent',
        outline: 'text-foreground border-input',
    };

    return (
        <Badge
            className={cn(
                'font-medium border',
                variantClasses[variant],
                className
            )}
        >
            {dotColor && (
                <span
                    className="size-1.5 rounded-full mr-1.5"
                    style={{ backgroundColor: dotColor }}
                />
            )}
            {children}
        </Badge>
    );
}

// Count badge for notifications
export function CountBadge({
    count,
    max = 99,
    className,
}: {
    count: number;
    max?: number;
    className?: string;
}) {
    if (count <= 0) return null;

    return (
        <span
            className={cn(
                'inline-flex items-center justify-center rounded-full min-w-[18px] h-[18px] px-1 text-[10px] font-bold text-white bg-destructive',
                className
            )}
        >
            {count > max ? `${max}+` : count}
        </span>
    );
}

// Size variants for badges
export const badgeSizes = {
    sm: 'text-[10px] px-1.5 py-0',
    default: 'text-xs px-2 py-0.5',
    lg: 'text-sm px-2.5 py-0.5',
} as const;

// Common status presets for orders
export const orderStatusPresets = {
    pending: { label: 'Pending', variant: 'warning' as const, dot: '#eab308' },
    confirmed: { label: 'Confirmed', variant: 'info' as const, dot: '#3b82f6' },
    preparing: { label: 'Preparing', variant: 'info' as const, dot: '#3b82f6' },
    ready: { label: 'Ready', variant: 'success' as const, dot: '#22c55e' },
    shipped: { label: 'Shipped', variant: 'info' as const, dot: '#3b82f6' },
    delivered: { label: 'Delivered', variant: 'success' as const, dot: '#22c55e' },
    cancelled: { label: 'Cancelled', variant: 'error' as const, dot: '#ef4444' },
    refunded: { label: 'Refunded', variant: 'error' as const, dot: '#ef4444' },
};

// Common status presets for inventory
export const inventoryStatusPresets = {
    in_stock: { label: 'In Stock', variant: 'success' as const, dot: '#22c55e' },
    low_stock: { label: 'Low Stock', variant: 'warning' as const, dot: '#eab308' },
    out_of_stock: { label: 'Out of Stock', variant: 'error' as const, dot: '#ef4444' },
};

// Common status presets for payments
export const paymentStatusPresets = {
    paid: { label: 'Paid', variant: 'success' as const, dot: '#22c55e' },
    partial: { label: 'Partial', variant: 'warning' as const, dot: '#eab308' },
    unpaid: { label: 'Unpaid', variant: 'error' as const, dot: '#ef4444' },
    refunded: { label: 'Refunded', variant: 'error' as const, dot: '#ef4444' },
};
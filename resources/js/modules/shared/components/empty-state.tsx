import { type LucideIcon, PackageOpen, FileText, Search, ShoppingCart, Users, Inbox } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

type EmptyStateProps = {
    icon?: LucideIcon;
    title: string;
    description?: string;
    action?: {
        label: string;
        onClick: () => void;
        icon?: LucideIcon;
    };
    secondaryAction?: {
        label: string;
        onClick: () => void;
    };
    className?: string;
    variant?: 'default' | 'compact' | 'large';
};

// Map common contexts to appropriate icons
const contextIcons: Record<string, LucideIcon> = {
    products: PackageOpen,
    orders: ShoppingCart,
    customers: Users,
    invoices: FileText,
    search: Search,
    default: Inbox,
};

export function EmptyState({
    icon: Icon,
    title,
    description,
    action,
    secondaryAction,
    className,
    variant = 'default',
}: EmptyStateProps) {
    const sizeClasses = {
        compact: 'py-8',
        default: 'py-12',
        large: 'py-16',
    };

    const iconSizeClasses = {
        compact: 'size-12',
        default: 'size-16',
        large: 'size-20',
    };

    const iconInnerSize = {
        compact: 'size-6',
        default: 'size-8',
        large: 'size-10',
    };

    return (
        <div className={cn(
            'flex flex-col items-center gap-4 text-center px-4',
            sizeClasses[variant],
            className
        )}>
            <div className={cn(
                'bg-muted flex items-center justify-center rounded-full',
                iconSizeClasses[variant]
            )}>
                {Icon ? (
                    <Icon className={cn('text-muted-foreground', iconInnerSize[variant])} />
                ) : (
                    <Inbox className={cn('text-muted-foreground', iconInnerSize[variant])} />
                )}
            </div>

            <div className="max-w-sm space-y-2">
                <h3 className="text-lg font-semibold">{title}</h3>
                {description && (
                    <p className="text-muted-foreground text-sm leading-relaxed">
                        {description}
                    </p>
                )}
            </div>

            {(action || secondaryAction) && (
                <div className="flex items-center gap-2 mt-2">
                    {action && (
                        <Button onClick={action.onClick} className="gap-2">
                            {action.icon && <action.icon className="size-4" />}
                            {action.label}
                        </Button>
                    )}
                    {secondaryAction && (
                        <Button variant="outline" onClick={secondaryAction.onClick}>
                            {secondaryAction.label}
                        </Button>
                    )}
                </div>
            )}
        </div>
    );
}

// Pre-configured empty states for common use cases
export const EmptyStates = {
    products: (action?: EmptyStateProps['action']) => (
        <EmptyState
            icon={PackageOpen}
            title="No products yet"
            description="Get started by adding your first product to the catalog."
            action={action}
        />
    ),
    orders: (action?: EmptyStateProps['action']) => (
        <EmptyState
            icon={ShoppingCart}
            title="No orders found"
            description="Orders will appear here once customers start placing them."
            action={action}
        />
    ),
    customers: (action?: EmptyStateProps['action']) => (
        <EmptyState
            icon={Users}
            title="No customers yet"
            description="Add customers to track their orders and preferences."
            action={action}
        />
    ),
    search: (onClear?: () => void) => (
        <EmptyState
            icon={Search}
            title="No results found"
            description="Try adjusting your search or filters to find what you're looking for."
            action={onClear ? { label: 'Clear search', onClick: onClear } : undefined}
        />
    ),
};

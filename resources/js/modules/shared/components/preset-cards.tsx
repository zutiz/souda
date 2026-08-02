import { type ReactNode } from 'react';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import { MoreHorizontal, ExternalLink, Edit2, Trash2 } from 'lucide-react';

// Standard card with actions
interface ActionCardProps {
    title: string;
    description?: string;
    children: ReactNode;
    footer?: ReactNode;
    onEdit?: () => void;
    onDelete?: () => void;
    onView?: () => void;
    className?: string;
}

export function ActionCard({
    title,
    description,
    children,
    footer,
    onEdit,
    onDelete,
    onView,
    className,
}: ActionCardProps) {
    return (
        <Card className={cn('group', className)}>
            <CardHeader className="pb-4">
                <div className="flex items-start justify-between">
                    <div className="space-y-1">
                        <CardTitle className="text-base">{title}</CardTitle>
                        {description && <CardDescription>{description}</CardDescription>}
                    </div>
                    <div className="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                        {onView && (
                            <Button variant="ghost" size="icon" className="size-8" onClick={onView}>
                                <ExternalLink className="size-4" />
                            </Button>
                        )}
                        {onEdit && (
                            <Button variant="ghost" size="icon" className="size-8" onClick={onEdit}>
                                <Edit2 className="size-4" />
                            </Button>
                        )}
                        {onDelete && (
                            <Button variant="ghost" size="icon" className="size-8 text-destructive hover:text-destructive" onClick={onDelete}>
                                <Trash2 className="size-4" />
                            </Button>
                        )}
                    </div>
                </div>
            </CardHeader>
            <CardContent className="pb-4">{children}</CardContent>
            {footer && <CardFooter className="pt-0">{footer}</CardFooter>}
        </Card>
    );
}

// Stats card for dashboard
interface StatCardProps {
    title: string;
    value: string | number;
    description?: string;
    change?: { value: number; label: string };
    icon?: React.ComponentType<{ className?: string }>;
    className?: string;
}

export function StatCard({
    title,
    value,
    description,
    change,
    icon: Icon,
    className,
}: StatCardProps) {
    const isPositive = change && change.value > 0;
    const isNegative = change && change.value < 0;

    return (
        <Card className={cn('relative overflow-hidden', className)}>
            {Icon && (
                <div className="absolute right-4 top-4 opacity-10">
                    <Icon className="size-12" />
                </div>
            )}
            <CardHeader className="pb-2">
                <CardDescription className="text-xs font-medium uppercase tracking-wider">{title}</CardDescription>
            </CardHeader>
            <CardContent>
                <div className="text-3xl font-bold">{value}</div>
                {description && <p className="mt-1 text-sm text-muted-foreground">{description}</p>}
                {change && (
                    <div
                        className={cn(
                            'mt-2 flex items-center gap-1 text-xs font-medium',
                            isPositive && 'text-green-600 dark:text-green-400',
                            isNegative && 'text-red-600 dark:text-red-400',
                            !isPositive && !isNegative && 'text-muted-foreground'
                        )}
                    >
                        {isPositive && (
                            <svg className="size-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 10l7-7m0 0l7 7m-7-7v18" />
                            </svg>
                        )}
                        {isNegative && (
                            <svg className="size-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                            </svg>
                        )}
                        <span>
                            {Math.abs(change.value)}% {change.label}
                        </span>
                    </div>
                )}
            </CardContent>
        </Card>
    );
}

// Compact card for lists
interface CompactCardProps {
    title: string;
    subtitle?: string;
    description?: string;
    badge?: ReactNode;
    actions?: ReactNode;
    className?: string;
}

export function CompactCard({
    title,
    subtitle,
    description,
    badge,
    actions,
    className,
}: CompactCardProps) {
    return (
        <div
            className={cn(
                'flex items-center gap-4 rounded-lg border bg-card p-4 transition-colors hover:bg-muted/50',
                className
            )}
        >
            <div className="flex-1 min-w-0">
                <div className="flex items-center gap-2">
                    <h4 className="font-medium truncate">{title}</h4>
                    {badge}
                </div>
                {subtitle && <p className="text-sm text-muted-foreground truncate">{subtitle}</p>}
                {description && <p className="mt-1 text-xs text-muted-foreground line-clamp-2">{description}</p>}
            </div>
            {actions && <div className="flex items-center gap-1 shrink-0">{actions}</div>}
        </div>
    );
}

// Interactive card with hover effect
interface InteractiveCardProps {
    children: ReactNode;
    onClick?: () => void;
    className?: string;
}

export function InteractiveCard({
    children,
    onClick,
    className,
}: InteractiveCardProps) {
    return (
        <Card
            className={cn(
                'cursor-pointer transition-all hover:shadow-md hover:border-primary/20',
                onClick && className
            )}
            onClick={onClick}
        >
            {children}
        </Card>
    );
}

// Grid of cards
interface CardGridProps {
    children: ReactNode;
    columns?: { sm?: number; md?: number; lg?: number };
    gap?: 2 | 4 | 6 | 8;
    className?: string;
}

export function CardGrid({
    children,
    columns = { sm: 1, md: 2, lg: 3 },
    gap = 4,
    className,
}: CardGridProps) {
    return (
        <div
            className={cn(
                'grid gap-4',
                columns.sm === 1 && 'grid-cols-1',
                columns.sm === 2 && 'grid-cols-1 sm:grid-cols-2',
                columns.md === 2 && 'sm:grid-cols-2',
                columns.md === 3 && 'sm:grid-cols-3',
                columns.lg === 2 && 'lg:grid-cols-2',
                columns.lg === 3 && 'lg:grid-cols-3',
                columns.lg === 4 && 'lg:grid-cols-4',
                `gap-${gap}`,
                className
            )}
        >
            {children}
        </div>
    );
}

// Section card with header
interface SectionCardProps {
    title: string;
    description?: string;
    action?: ReactNode;
    children: ReactNode;
    footer?: ReactNode;
    className?: string;
}

export function SectionCard({
    title,
    description,
    action,
    children,
    footer,
    className,
}: SectionCardProps) {
    return (
        <Card className={className}>
            <CardHeader className="pb-4">
                <div className="flex items-center justify-between">
                    <div>
                        <CardTitle className="text-lg">{title}</CardTitle>
                        {description && <CardDescription className="mt-1">{description}</CardDescription>}
                    </div>
                    {action}
                </div>
            </CardHeader>
            <CardContent>{children}</CardContent>
            {footer && <CardFooter className="pt-0 border-t">{footer}</CardFooter>}
        </Card>
    );
}

// Loading skeleton card
interface SkeletonCardProps {
    className?: string;
}

export function SkeletonCard({ className }: SkeletonCardProps) {
    return (
        <Card className={cn('animate-pulse', className)}>
            <CardHeader>
                <div className="h-4 w-24 rounded bg-muted" />
            </CardHeader>
            <CardContent>
                <div className="h-8 w-16 rounded bg-muted mb-2" />
                <div className="h-3 w-32 rounded bg-muted" />
            </CardContent>
        </Card>
    );
}

// Empty state card
interface EmptyCardProps {
    icon?: React.ComponentType<{ className?: string }>;
    title: string;
    description?: string;
    action?: { label: string; onClick: () => void };
    className?: string;
}

export function EmptyCard({
    icon: Icon,
    title,
    description,
    action,
    className,
}: EmptyCardProps) {
    return (
        <Card className={cn('flex flex-col items-center justify-center py-12 px-6', className)}>
            {Icon && (
                <div className="mb-4 flex size-12 items-center justify-center rounded-full bg-muted">
                    <Icon className="size-6 text-muted-foreground" />
                </div>
            )}
            <h3 className="mb-1 text-base font-medium">{title}</h3>
            {description && <p className="mb-4 text-sm text-muted-foreground text-center">{description}</p>}
            {action && (
                <Button onClick={action.onClick}>
                    {action.label}
                </Button>
            )}
        </Card>
    );
}
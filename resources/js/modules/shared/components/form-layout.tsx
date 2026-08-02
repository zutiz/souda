import { type ReactNode } from 'react';
import { ArrowLeft } from 'lucide-react';
import { Link } from '@inertiajs/react';
import { cn } from '@/lib/utils';
import { Button } from '@/components/ui/button';

type FormLayoutProps = {
    title: string;
    description?: string;
    backHref?: string;
    backLabel?: string;
    children: ReactNode;
    actions?: ReactNode;
    className?: string;
};

export function FormLayout({
    title,
    description,
    backHref,
    backLabel = 'Back',
    children,
    actions,
    className,
}: FormLayoutProps) {
    return (
        <div className={cn('max-w-2xl mx-auto', className)}>
            {/* Header */}
            <div className="mb-8">
                {backHref && (
                    <Link
                        href={backHref}
                        className="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground mb-4"
                    >
                        <ArrowLeft className="size-4" />
                        {backLabel}
                    </Link>
                )}
                <div className="flex items-start justify-between gap-4">
                    <div>
                        <h1 className="text-2xl font-semibold tracking-tight">{title}</h1>
                        {description && (
                            <p className="mt-1 text-muted-foreground">{description}</p>
                        )}
                    </div>
                    {actions && <div className="shrink-0">{actions}</div>}
                </div>
            </div>

            {/* Form Content */}
            <div className="space-y-6">{children}</div>
        </div>
    );
}

// Section wrapper with visual hierarchy
export function FormSectionGroup({
    children,
    className,
}: {
    children: ReactNode;
    className?: string;
}) {
    return (
        <div className={cn('space-y-8 rounded-lg border bg-card p-6', className)}>
            {children}
        </div>
    );
}

// Section with header
export function FormSectionHeader({
    title,
    description,
    action,
    className,
}: {
    title: string;
    description?: string;
    action?: ReactNode;
    className?: string;
}) {
    return (
        <div className={cn('flex items-start justify-between gap-4 mb-6', className)}>
            <div>
                <h2 className="text-lg font-semibold">{title}</h2>
                {description && (
                    <p className="mt-1 text-sm text-muted-foreground">{description}</p>
                )}
            </div>
            {action && <div className="shrink-0">{action}</div>}
        </div>
    );
}

// Inline field group (for 2-column layouts)
export function FormFieldGrid({
    children,
    columns = 2,
    className,
}: {
    children: ReactNode;
    columns?: 1 | 2 | 3;
    className?: string;
}) {
    const gridCols = {
        1: 'grid-cols-1',
        2: 'grid-cols-1 sm:grid-cols-2',
        3: 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3',
    };

    return (
        <div className={cn(`grid ${gridCols[columns]} gap-4`, className)}>{children}</div>
    );
}

// Required field indicator
export function RequiredBadge() {
    return <span className="ml-1 text-destructive">*</span>;
}

// Helper text component
export function FormHint({
    children,
    className,
}: {
    children: ReactNode;
    className?: string;
}) {
    return (
        <p className={cn('text-xs text-muted-foreground', className)}>{children}</p>
    );
}

// Divider with optional label
export function FormDivider({
    label,
    className,
}: {
    label?: string;
    className?: string;
}) {
    return (
        <div className={cn('flex items-center gap-4 my-6', className)}>
            <div className="h-px flex-1 bg-border" />
            {label && (
                <span className="text-xs font-medium text-muted-foreground uppercase tracking-wider">
                    {label}
                </span>
            )}
            {label && <div className="h-px flex-1 bg-border" />}
        </div>
    );
}
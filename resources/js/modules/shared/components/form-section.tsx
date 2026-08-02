import { ChevronDownIcon, ChevronRightIcon } from 'lucide-react';
import type { ReactNode } from 'react';
import { cn } from '@/lib/utils';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';

type FormSectionProps = {
    title: string;
    description?: string;
    children: ReactNode;
    defaultOpen?: boolean;
    className?: string;
};

export function FormSection({
    title,
    description,
    children,
    defaultOpen = true,
    className,
}: FormSectionProps) {
    return (
        <div className={cn('space-y-5', className)}>
            <div className="space-y-1.5">
                <h2 className="text-base font-semibold text-foreground">{title}</h2>
                {description && (
                    <p className="text-sm text-muted-foreground">{description}</p>
                )}
            </div>
            <div className="space-y-4">{children}</div>
        </div>
    );
}

type FormSectionCollapsibleProps = FormSectionProps & {
    open?: boolean;
    onToggle?: () => void;
};

export function FormSectionCollapsible({
    title,
    description,
    children,
    open = true,
    onToggle,
    className,
}: FormSectionCollapsibleProps) {
    return (
        <Collapsible open={open} onOpenChange={(isOpen) => !isOpen && onToggle?.()}>
            <CollapsibleTrigger asChild>
                <button
                    type="button"
                    className="flex w-full items-center justify-between text-left group"
                >
                    <div className="space-y-1">
                        <h2 className="text-base font-semibold text-foreground group-hover:text-primary transition-colors">
                            {title}
                        </h2>
                        {description && (
                            <p className="text-sm text-muted-foreground">{description}</p>
                        )}
                    </div>
                    <ChevronRightIcon
                        className={cn(
                            'text-muted-foreground size-5 transition-transform duration-200',
                            open ? 'rotate-90' : '',
                            'group-hover:text-primary'
                        )}
                    />
                </button>
            </CollapsibleTrigger>
            <CollapsibleContent className="pt-4">
                <div className="space-y-4">{children}</div>
            </CollapsibleContent>
        </Collapsible>
    );
}

// Grouped sections with visual divider
export function FormSectionGroup({
    children,
    className,
}: {
    children: ReactNode;
    className?: string;
}) {
    return (
        <div className={cn('rounded-lg border bg-card p-6', className)}>
            {children}
        </div>
    );
}

// Section header with action button
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
        <div className={cn('flex items-start justify-between gap-4 mb-4', className)}>
            <div>
                <h3 className="text-base font-semibold">{title}</h3>
                {description && (
                    <p className="mt-0.5 text-sm text-muted-foreground">{description}</p>
                )}
            </div>
            {action}
        </div>
    );
}

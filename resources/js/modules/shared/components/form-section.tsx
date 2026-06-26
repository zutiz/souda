import { ChevronDownIcon } from 'lucide-react';
import type { ReactNode } from 'react';
import { cn } from '@/lib/utils';
import { Button } from '@/components/ui/button';

type FormSectionProps = {
    title: string;
    description?: string;
    children: ReactNode;
    defaultOpen?: boolean;
    className?: string;
};

export function FormSection({ title, description, children, defaultOpen = true, className }: FormSectionProps) {
    return (
        <div className={cn('space-y-6', className)}>
            <div className="space-y-1">
                <h2 className="text-lg font-semibold">{title}</h2>
                {description && <p className="text-muted-foreground text-sm">{description}</p>}
            </div>
            <div className="space-y-5">{children}</div>
        </div>
    );
}

type FormSectionCollapsibleProps = FormSectionProps & {
    open?: boolean;
    onToggle?: () => void;
};

export function FormSectionCollapsible({
    title, description, children, open = true, onToggle, className,
}: FormSectionCollapsibleProps) {
    return (
        <div className={cn('space-y-4', className)}>
            <button
                type="button"
                onClick={onToggle}
                className="flex w-full items-center justify-between text-left"
            >
                <div className="space-y-1">
                    <h2 className="text-lg font-semibold">{title}</h2>
                    {description && <p className="text-muted-foreground text-sm">{description}</p>}
                </div>
                <ChevronDownIcon
                    className={cn(
                        'text-muted-foreground size-5 transition-transform',
                        open ? '' : '-rotate-90',
                    )}
                />
            </button>
            {open && <div className="space-y-5">{children}</div>}
        </div>
    );
}

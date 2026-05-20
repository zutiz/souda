import { type LucideIcon, PackageOpen } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

type EmptyStateProps = {
    icon?: LucideIcon;
    title: string;
    description?: string;
    action?: {
        label: string;
        onClick: () => void;
    };
    className?: string;
};

export function EmptyState({ icon: Icon = PackageOpen, title, description, action, className }: EmptyStateProps) {
    return (
        <div className={cn('flex flex-col items-center gap-3 py-16 text-center', className)}>
            <div className="bg-muted flex size-16 items-center justify-center rounded-full">
                <Icon className="text-muted-foreground size-8" />
            </div>
            <div className="max-w-sm space-y-1">
                <p className="font-medium">{title}</p>
                {description && <p className="text-muted-foreground text-sm">{description}</p>}
            </div>
            {action && (
                <Button onClick={action.onClick} className="mt-2">
                    {action.label}
                </Button>
            )}
        </div>
    );
}

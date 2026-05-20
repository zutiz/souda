import type { ReactNode } from 'react';
import { cn } from '@/lib/utils';

type DataTableToolbarProps = {
    children?: ReactNode;
    className?: string;
};

export function DataTableToolbar({ children, className }: DataTableToolbarProps) {
    if (!children) return null;

    return (
        <div className={cn('flex flex-wrap items-center gap-2', className)}>
            {children}
        </div>
    );
}

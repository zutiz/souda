import { Trash2, Download, MoreHorizontal, X } from 'lucide-react';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Badge } from '@/components/ui/badge';
import { cn } from '@/lib/utils';

type BulkAction = {
    id: string;
    label: string;
    icon?: React.ComponentType<{ className?: string }>;
    variant?: 'default' | 'destructive';
    onClick: () => void;
};

interface BulkActionsBarProps {
    selectedCount: number;
    actions?: BulkAction[];
    onClear?: () => void;
    onSelectAll?: () => void;
    className?: string;
}

export function BulkActionsBar({
    selectedCount,
    actions = [],
    onClear,
    onSelectAll,
    className,
}: BulkActionsBarProps) {
    if (selectedCount === 0) return null;

    return (
        <div
            className={cn(
                'flex items-center gap-2 rounded-lg border bg-muted/50 p-2',
                className
            )}
        >
            {/* Selection Count */}
            <Badge variant="secondary" className="gap-1 px-2 py-1">
                <span className="font-semibold">{selectedCount}</span>
                <span className="font-normal">selected</span>
            </Badge>

            {/* Default Actions */}
            <div className="flex items-center gap-1">
                {onSelectAll && (
                    <Button
                        variant="ghost"
                        size="sm"
                        onClick={onSelectAll}
                        className="h-8 text-xs"
                    >
                        Select all
                    </Button>
                )}

                {actions.length > 0 && (
                    <DropdownMenu>
                        <DropdownMenuTrigger asChild>
                            <Button variant="ghost" size="sm" className="h-8 gap-1">
                                <MoreHorizontal className="size-4" />
                                Actions
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="start">
                            {actions.map((action, index) => {
                                const Icon = action.icon;
                                return (
                                    <DropdownMenuItem
                                        key={action.id}
                                        onClick={action.onClick}
                                        className={cn(
                                            'gap-2',
                                            action.variant === 'destructive' && 'text-destructive'
                                        )}
                                    >
                                        {Icon && <Icon className="size-4" />}
                                        {action.label}
                                    </DropdownMenuItem>
                                );
                            })}
                        </DropdownMenuContent>
                    </DropdownMenu>
                )}
            </div>

            {/* Divider and Clear */}
            {onClear && (
                <>
                    <div className="h-6 w-px bg-border mx-1" />
                    <Button
                        variant="ghost"
                        size="sm"
                        onClick={onClear}
                        className="h-8 text-muted-foreground hover:text-foreground"
                    >
                        <X className="size-4" />
                        Clear selection
                    </Button>
                </>
            )}
        </div>
    );
}

// Pre-built common bulk actions
export const commonBulkActions = {
    delete: (onDelete: () => void): BulkAction => ({
        id: 'delete',
        label: 'Delete selected',
        icon: Trash2,
        variant: 'destructive' as const,
        onClick: onDelete,
    }),
    export: (onExport: () => void): BulkAction => ({
        id: 'export',
        label: 'Export selected',
        icon: Download,
        onClick: onExport,
    }),
};
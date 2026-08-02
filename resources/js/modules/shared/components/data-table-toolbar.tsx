import { useState, useCallback } from 'react';
import { Search, X } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { cn } from '@/lib/utils';
import type { ReactNode } from 'react';

type DataTableToolbarProps = {
    children?: ReactNode;
    className?: string;
    searchValue?: string;
    onSearchChange?: (value: string) => void;
    searchPlaceholder?: string;
    showSearch?: boolean;
};

export function DataTableToolbar({
    children,
    className,
    searchValue = '',
    onSearchChange,
    searchPlaceholder = 'Search...',
    showSearch = false,
}: DataTableToolbarProps) {
    const [localSearch, setLocalSearch] = useState(searchValue);

    const handleSearch = useCallback((value: string) => {
        setLocalSearch(value);
        onSearchChange?.(value);
    }, [onSearchChange]);

    const handleClear = useCallback(() => {
        setLocalSearch('');
        onSearchChange?.('');
    }, [onSearchChange]);

    if (!children && !showSearch) return null;

    return (
        <div className={cn('flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between', className)}>
            <div className="flex flex-1 flex-wrap items-center gap-2">
                {showSearch && (
                    <div className="relative flex-1 sm:max-w-[250px]">
                        <Search className="absolute left-2.5 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            value={localSearch}
                            onChange={(e) => handleSearch(e.target.value)}
                            placeholder={searchPlaceholder}
                            className="pl-8 pr-8 h-9"
                        />
                        {localSearch && (
                            <button
                                onClick={handleClear}
                                className="absolute right-2 top-1/2 -translate-y-1/2 rounded-sm p-0.5 hover:bg-muted"
                            >
                                <X className="size-3.5 text-muted-foreground" />
                            </button>
                        )}
                    </div>
                )}
                {children}
            </div>
        </div>
    );
}

// Helper components for toolbar sections
export function ToolbarSection({ children, className }: { children: ReactNode; className?: string }) {
    return (
        <div className={cn('flex items-center gap-2', className)}>
            {children}
        </div>
    );
}

export function ToolbarDivider() {
    return <div className="h-6 w-px bg-border hidden sm:block" />;
}

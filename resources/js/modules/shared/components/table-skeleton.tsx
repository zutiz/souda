import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Skeleton } from '@/components/ui/skeleton';
import { cn } from '@/lib/utils';

type TableSkeletonProps = {
    columns: number;
    rows?: number;
    showHeader?: boolean;
    columnWidths?: (string | number)[];
};

export function TableSkeleton({
    columns,
    rows = 8,
    showHeader = true,
    columnWidths,
}: TableSkeletonProps) {
    return (
        <Table>
            {showHeader && (
                <TableHeader>
                    <TableRow className="hover:bg-transparent">
                        {Array.from({ length: columns }).map((_, i) => (
                            <TableHead key={i}>
                                <Skeleton
                                    className="h-4"
                                    style={columnWidths?.[i] ? { width: columnWidths[i] } : undefined}
                                />
                            </TableHead>
                        ))}
                    </TableRow>
                </TableHeader>
            )}
            <TableBody>
                {Array.from({ length: rows }).map((_, r) => (
                    <TableRow key={r}>
                        {Array.from({ length: columns }).map((_, c) => (
                            <TableCell key={c}>
                                <Skeleton
                                    className="h-4 w-full"
                                    style={columnWidths?.[c] ? { width: columnWidths[c] } : undefined}
                                />
                            </TableCell>
                        ))}
                    </TableRow>
                ))}
            </TableBody>
        </Table>
    );
}

// Row-specific skeleton for inline loading
export function TableRowSkeleton({
    columns,
    index = 0,
    columnWidths,
}: {
    columns: number;
    index?: number;
    columnWidths?: (string | number)[];
}) {
    return (
        <TableRow>
            {Array.from({ length: columns }).map((_, c) => (
                <TableCell key={c}>
                    <Skeleton
                        className={cn(
                            'h-4',
                            index % 2 === 0 ? 'w-full' : 'w-3/4'
                        )}
                        style={columnWidths?.[c] ? { width: columnWidths[c] } : undefined}
                    />
                </TableCell>
            ))}
        </TableRow>
    );
}

// Card skeleton
export function CardSkeleton({ count = 3 }: { count?: number }) {
    return (
        <div className="grid gap-4 md:grid-cols-3">
            {Array.from({ length: count }).map((_, i) => (
                <div key={i} className="rounded-xl border p-6">
                    <Skeleton className="mb-2 h-4 w-24" />
                    <Skeleton className="h-8 w-16" />
                </div>
            ))}
        </div>
    );
}

// Stat card skeleton
export function StatCardSkeleton({ className }: { className?: string }) {
    return (
        <div className={cn('rounded-lg border p-4', className)}>
            <Skeleton className="mb-2 h-3 w-20" />
            <Skeleton className="h-8 w-24" />
            <Skeleton className="mt-2 h-3 w-12" />
        </div>
    );
}

// Grid skeleton for dashboard widgets
export function GridSkeleton({ count = 4, columns = 2 }: { count?: number; columns?: number }) {
    return (
        <div className={cn(`grid gap-4 md:grid-cols-${columns}`)}>
            {Array.from({ length: count }).map((_, i) => (
                <CardSkeleton key={i} />
            ))}
        </div>
    );
}

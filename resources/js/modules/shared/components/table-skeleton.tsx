import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Skeleton } from '@/components/ui/skeleton';

type TableSkeletonProps = {
    columns: number;
    rows?: number;
};

export function TableSkeleton({ columns, rows = 8 }: TableSkeletonProps) {
    return (
        <Table>
            <TableHeader>
                <TableRow>
                    {Array.from({ length: columns }).map((_, i) => (
                        <TableHead key={i}>
                            <Skeleton className="h-4 w-24" />
                        </TableHead>
                    ))}
                </TableRow>
            </TableHeader>
            <TableBody>
                {Array.from({ length: rows }).map((_, r) => (
                    <TableRow key={r}>
                        {Array.from({ length: columns }).map((_, c) => (
                            <TableCell key={c}>
                                <Skeleton className="h-4 w-full" />
                            </TableCell>
                        ))}
                    </TableRow>
                ))}
            </TableBody>
        </Table>
    );
}

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

import {
    type ColumnDef,
    type OnChangeFn,
    type Row,
    type SortingState,
    type Table as TanStackTable,
    flexRender,
    getCoreRowModel,
    getSortedRowModel,
    useReactTable,
} from '@tanstack/react-table';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { TableSkeleton } from './table-skeleton';
import { EmptyState } from './empty-state';
import { ErrorState } from './error-state';
import type { ReactNode } from 'react';

type DataTableProps<TData> = {
    columns: ColumnDef<TData, any>[];
    data: TData[];
    loading?: boolean;
    error?: Error | null;
    sorting?: SortingState;
    onSortingChange?: OnChangeFn<SortingState>;
    onRowClick?: (row: TData) => void;
    emptyMessage?: string;
    emptyTitle?: string;
    emptyAction?: { label: string; onClick: () => void };
    enableRowSelection?: boolean;
    rowSelection?: Record<string, boolean>;
    onRowSelectionChange?: OnChangeFn<Record<string, boolean>>;
    getRowId?: (row: TData) => string;
    toolbar?: ReactNode;
    pagination?: ReactNode;
};

export function DataTable<TData>({
    columns,
    data,
    loading = false,
    error,
    sorting,
    onSortingChange,
    onRowClick,
    emptyMessage = 'No results found.',
    emptyTitle,
    emptyAction,
    enableRowSelection = false,
    rowSelection,
    onRowSelectionChange,
    getRowId,
    toolbar,
    pagination,
}: DataTableProps<TData>) {
    const table = useReactTable({
        data,
        columns,
        getCoreRowModel: getCoreRowModel(),
        getSortedRowModel: getSortedRowModel(),
        state: {
            sorting,
            rowSelection: rowSelection ?? {},
        },
        onSortingChange,
        onRowSelectionChange,
        enableRowSelection,
        getRowId,
        manualSorting: true,
    });

    if (error) {
        return <ErrorState message={error.message} onRetry={() => window.location.reload()} />;
    }

    return (
        <div className="space-y-4">
            {toolbar && <div className="flex items-center justify-between">{toolbar}</div>}

            <div className="rounded-md border">
                {loading ? (
                    <TableSkeleton columns={columns.length} />
                ) : data.length === 0 ? (
                    <EmptyState
                        title={emptyTitle ?? emptyMessage}
                        action={emptyAction}
                    />
                ) : (
                    <Table>
                        <TableHeader>
                            {table.getHeaderGroups().map((group) => (
                                <TableRow key={group.id}>
                                    {group.headers.map((header) => (
                                        <TableHead key={header.id}>
                                            {header.isPlaceholder
                                                ? null
                                                : flexRender(header.column.columnDef.header, header.getContext())}
                                        </TableHead>
                                    ))}
                                </TableRow>
                            ))}
                        </TableHeader>
                        <TableBody>
                            {table.getRowModel().rows.map((row) => (
                                <TableRow
                                    key={row.id}
                                    data-state={row.getIsSelected() && 'selected'}
                                    onClick={() => onRowClick?.(row.original)}
                                    className={onRowClick ? 'cursor-pointer' : undefined}
                                >
                                    {row.getVisibleCells().map((cell) => (
                                        <TableCell key={cell.id}>
                                            {flexRender(cell.column.columnDef.cell, cell.getContext())}
                                        </TableCell>
                                    ))}
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                )}
            </div>

            {pagination}
        </div>
    );
}

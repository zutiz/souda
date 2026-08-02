import {
    type ColumnDef,
    type OnChangeFn,
    type SortingState,
    flexRender,
    getCoreRowModel,
    getSortedRowModel,
    getCanNextPage,
    getCanPreviousPage,
    getPageCount,
    getState as getTableState,
    useReactTable,
} from '@tanstack/react-table';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { TableSkeleton } from './table-skeleton';
import { EmptyState } from './empty-state';
import { ErrorState } from './error-state';
import { cn } from '@/lib/utils';
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
    emptyDescription?: string;
    emptyAction?: { label: string; onClick: () => void };
    enableRowSelection?: boolean;
    rowSelection?: Record<string, boolean>;
    onRowSelectionChange?: OnChangeFn<Record<string, boolean>>;
    getRowId?: (row: TData) => string;
    toolbar?: ReactNode;
    bulkActionsBar?: ReactNode;
    pagination?: ReactNode;
    enableStickyHeader?: boolean;
    stickyOffset?: number;
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
    emptyDescription,
    emptyAction,
    enableRowSelection = false,
    rowSelection,
    onRowSelectionChange,
    getRowId,
    toolbar,
    bulkActionsBar,
    pagination,
    enableStickyHeader = true,
    stickyOffset = 0,
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

    const selectedRowCount = Object.keys(rowSelection ?? {}).filter((key) => rowSelection?.[key]).length;

    return (
        <div className="space-y-4">
            {(toolbar || bulkActionsBar) && (
                <div className="flex flex-col gap-2">
                    {toolbar && <div className="flex items-center justify-between">{toolbar}</div>}
                    {bulkActionsBar}
                </div>
            )}

            <div className={cn(
                'rounded-md border',
                enableStickyHeader && 'overflow-hidden'
            )}>
                <div className="overflow-auto" style={{ maxHeight: stickyOffset ? `${stickyOffset}px` : undefined }}>
                    {loading ? (
                        <TableSkeleton columns={columns.length} />
                    ) : data.length === 0 ? (
                        <EmptyState
                            title={emptyTitle ?? emptyMessage}
                            description={emptyDescription}
                            action={emptyAction}
                        />
                    ) : (
                        <Table>
                            <TableHeader className={cn(
                                enableStickyHeader && 'sticky top-0 z-10 bg-muted/95 backdrop-blur supports-[backdrop-filter]:bg-muted/75'
                            )}>
                                {table.getHeaderGroups().map((group) => (
                                    <TableRow key={group.id}>
                                        {group.headers.map((header) => (
                                            <TableHead
                                                key={header.id}
                                                data-column-id={header.column.id}
                                                className={cn(
                                                    header.column.getCanSort() && 'cursor-pointer select-none'
                                                )}
                                                onClick={header.column.getToggleSortingHandler()}
                                            >
                                                <div className="flex items-center gap-1">
                                                    {header.isPlaceholder
                                                        ? null
                                                        : flexRender(header.column.columnDef.header, header.getContext())}
                                                    {{
                                                        asc: <span className="ml-1 text-primary">↑</span>,
                                                        desc: <span className="ml-1 text-primary">↓</span>,
                                                    }[header.column.getIsSorted() as string] ?? null}
                                                </div>
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
                                        className={cn(
                                            onRowClick && 'cursor-pointer',
                                            row.getIsSelected() && 'bg-muted/50'
                                        )}
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
            </div>

            {pagination}
        </div>
    );
}

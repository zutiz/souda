import { DataTable } from '@/modules/shared/components/data-table';
import { DataTablePagination } from '@/modules/shared/components/data-table-pagination';
import { DataTableToolbar } from '@/modules/shared/components/data-table-toolbar';
import { DataTableColumnToggle } from '@/modules/shared/components/data-table-column-toggle';
import { SearchInput } from '@/modules/shared/components/search-input';
import { columns } from './product-columns';
import type { Product, ProductFilters } from '../types';
import type { PaginatedResponse } from '@/modules/shared/types';
import type { SortingState } from '@tanstack/react-table';
import { useCallback, useMemo, useState } from 'react';

type ProductTableProps = {
    products: PaginatedResponse<Product>;
    filters: ProductFilters;
    loading: boolean;
    onNavigate: (params: Partial<ProductFilters>) => void;
    onSearch: (value: string) => void;
    onPageChange: (page: number) => void;
    onPageSizeChange: (size: number) => void;
    onSortChange: (sorting: SortingState) => void;
    onRowSelectionChange: (updaterOrValue: Record<string, boolean> | ((old: Record<string, boolean>) => Record<string, boolean>)) => void;
    selectedRows: Record<string, boolean>;
    toolbarExtra?: React.ReactNode;
};

export function ProductTable({
    products,
    filters,
    loading,
    onNavigate,
    onSearch,
    onPageChange,
    onPageSizeChange,
    onSortChange,
    onRowSelectionChange,
    selectedRows,
    toolbarExtra,
}: ProductTableProps) {
    const [sorting, setSorting] = useState<SortingState>(() => {
        if (filters.sort) {
            const [id, dir] = filters.sort.split(':');
            return [{ id, desc: dir === 'desc' }];
        }
        return [{ id: 'created_at', desc: true }];
    });

    const handleSortChange = useCallback(
        (updaterOrValue: SortingState | ((old: SortingState) => SortingState)) => {
            const newSorting = typeof updaterOrValue === 'function' ? updaterOrValue(sorting) : updaterOrValue;
            setSorting(newSorting);
            onSortChange(newSorting);
        },
        [sorting, onSortChange],
    );

    const columnVisibilityItems = useMemo(
        () => columns
            .filter((col: any) => col.id !== 'select' && col.id !== 'actions')
            .map((col: any) => ({
                id: col.id,
                label: (col.meta as { label?: string })?.label ?? col.id ?? '',
                isVisible: true,
            })),
        [],
    );

    return (
        <DataTable
            columns={columns}
            data={products.data}
            loading={loading}
            sorting={sorting}
            onSortingChange={handleSortChange}
            onRowClick={(row) => {
                window.location.href = `/products/${row.id}`;
            }}
            enableRowSelection
            rowSelection={selectedRows}
            onRowSelectionChange={onRowSelectionChange}
            getRowId={(row) => row.id}
            emptyTitle="No products found"
            emptyAction={
                filters.search || filters.status || filters.category_id || filters.brand_id
                    ? undefined
                    : { label: 'Create Product', onClick: () => onNavigate({}) }
            }
            toolbar={
                <DataTableToolbar className="flex-1">
                    <SearchInput
                        value={filters.search ?? ''}
                        onChange={onSearch}
                        placeholder="Search products..."
                    />
                    {toolbarExtra}
                    <div className="ml-auto">
                        <DataTableColumnToggle
                            columns={columnVisibilityItems}
                            onToggle={() => {}}
                        />
                    </div>
                </DataTableToolbar>
            }
            pagination={
                <DataTablePagination
                    pageIndex={products.currentPage - 1}
                    pageSize={products.perPage}
                    total={products.total}
                    onPageChange={onPageChange}
                    onPageSizeChange={onPageSizeChange}
                />
            }
        />
    );
}

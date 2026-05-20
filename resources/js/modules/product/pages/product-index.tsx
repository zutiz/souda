import { Head, Link, usePage } from '@inertiajs/react';
import { PlusIcon } from 'lucide-react';
import { useCallback, useState } from 'react';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { PageHeader } from '@/modules/shared/components/page-header';
import { ProductTable } from '../components/product-table';
import { ProductFilterBar } from '../components/product-filter-bar';
import { ProductBulkActions } from '../components/product-bulk-actions';
import { useProducts } from '../hooks/use-products';
import { useProductMutations } from '../hooks/use-product-mutations';
import type { ProductFilters, BulkAction } from '../types';
import type { PaginatedResponse } from '@/modules/shared/types';
import type { CategoryOption, BrandOption, StoreOption } from '../types';
import type { BreadcrumbItem } from '@/types';

type ProductIndexPageProps = {
    products: PaginatedResponse<ProductFilters>;
    filters: ProductFilters;
    categories: CategoryOption[];
    brands: BrandOption[];
    stores?: StoreOption[];
};

export default function ProductIndex() {
    const { categories = [], brands = [], stores } = usePage<ProductIndexPageProps>().props;
    const { products, filters, navigate, clearFilters } = useProducts();
    const { processing, bulkAction } = useProductMutations();
    const [selectedRows, setSelectedRows] = useState<Record<string, boolean>>({});
    const [loading, setLoading] = useState(false);

    const selectedCount = Object.keys(selectedRows).length;
    const selectedIds = Object.keys(selectedRows).filter((id) => selectedRows[id]);

    const handleNavigate = useCallback(
        (params: Partial<ProductFilters>) => {
            setLoading(true);
            navigate(params);
            setTimeout(() => setLoading(false), 300);
        },
        [navigate],
    );

    const handleSearch = useCallback(
        (value: string) => {
            setSelectedRows({});
            handleNavigate({ search: value || undefined, page: 1 });
        },
        [handleNavigate],
    );

    const handlePageChange = useCallback(
        (page: number) => {
            handleNavigate({ page: page + 1 });
        },
        [handleNavigate],
    );

    const handlePageSizeChange = useCallback(
        (perPage: number) => {
            setSelectedRows({});
            handleNavigate({ per_page: perPage, page: 1 });
        },
        [handleNavigate],
    );

    const handleSortChange = useCallback(
        (sorting: { id: string; desc: boolean }[]) => {
            const sort = sorting.length > 0 ? `${sorting[0].id}:${sorting[0].desc ? 'desc' : 'asc'}` : undefined;
            handleNavigate({ sort, page: 1 });
        },
        [handleNavigate],
    );

    const handleFilterChange = useCallback(
        (params: Partial<ProductFilters>) => {
            setSelectedRows({});
            handleNavigate({ ...params, page: 1 });
        },
        [handleNavigate],
    );

    const handleBulkAction = useCallback(
        (action: BulkAction) => {
            bulkAction(action, selectedIds);
            setSelectedRows({});
        },
        [bulkAction, selectedIds],
    );

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Products', href: '/products' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Products" />

            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4 lg:p-6">
                <PageHeader title="Products" description="Manage your product catalog">
                    <Button asChild>
                        <Link href="/products/create">
                            <PlusIcon className="size-4" />
                            Create Product
                        </Link>
                    </Button>
                </PageHeader>

                <div className="flex flex-col gap-4">
                    <ProductFilterBar
                        filters={filters}
                        categories={categories}
                        brands={brands}
                        stores={stores}
                        onFilterChange={handleFilterChange}
                        onClear={clearFilters}
                    />

                    {selectedCount > 0 && (
                        <ProductBulkActions
                            selectedCount={selectedCount}
                            processing={processing}
                            onBulkAction={handleBulkAction}
                            onClear={() => setSelectedRows({})}
                        />
                    )}

                    <ProductTable
                        products={products}
                        filters={filters}
                        loading={loading}
                        onNavigate={handleNavigate}
                        onSearch={handleSearch}
                        onPageChange={handlePageChange}
                        onPageSizeChange={handlePageSizeChange}
                        onSortChange={handleSortChange}
                        onRowSelectionChange={setSelectedRows}
                        selectedRows={selectedRows}
                    />
                </div>
            </div>
        </AppLayout>
    );
}

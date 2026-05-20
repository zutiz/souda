import { DataTableFacetedFilter } from '@/modules/shared/components/data-table-faceted-filter';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { RotateCcwIcon } from 'lucide-react';
import type { CategoryOption, BrandOption, StoreOption, ProductFilters } from '../types';

const STATUS_OPTIONS = [
    { label: 'Active', value: 'active' },
    { label: 'Draft', value: 'draft' },
    { label: 'Archived', value: 'archived' },
];

const STOCK_STATUS_OPTIONS = [
    { label: 'In Stock', value: 'in_stock' },
    { label: 'Low Stock', value: 'low_stock' },
    { label: 'Out of Stock', value: 'out_of_stock' },
];

type ProductFilterBarProps = {
    filters: ProductFilters;
    categories: CategoryOption[];
    brands: BrandOption[];
    stores?: StoreOption[];
    onFilterChange: (params: Partial<ProductFilters>) => void;
    onClear: () => void;
};

export function ProductFilterBar({
    filters,
    categories,
    brands,
    stores,
    onFilterChange,
    onClear,
}: ProductFilterBarProps) {
    const hasActiveFilters = !!(filters.status?.length || filters.category_id || filters.brand_id || filters.stock_status || filters.store_id);

    return (
        <div className="flex flex-wrap items-center gap-2">
            <DataTableFacetedFilter
                title="Status"
                options={STATUS_OPTIONS}
                selected={filters.status ?? []}
                onSelect={(values) => onFilterChange({ status: values.length > 0 ? values : undefined })}
            />

            <DataTableFacetedFilter
                title="Stock"
                options={STOCK_STATUS_OPTIONS}
                selected={filters.stock_status ? [filters.stock_status] : []}
                onSelect={(values) => onFilterChange({ stock_status: values[values.length - 1] ?? undefined })}
            />

            {categories.length > 0 && (
                <Select
                    value={filters.category_id ?? ''}
                    onValueChange={(value) => onFilterChange({ category_id: value || undefined })}
                >
                    <SelectTrigger className="h-8 w-[160px]">
                        <SelectValue placeholder="Category" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">All Categories</SelectItem>
                        {categories.map((cat) => (
                            <SelectItem key={cat.id} value={cat.id}>
                                {cat.name} ({cat.products_count})
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
            )}

            {brands.length > 0 && (
                <Select
                    value={filters.brand_id ?? ''}
                    onValueChange={(value) => onFilterChange({ brand_id: value || undefined })}
                >
                    <SelectTrigger className="h-8 w-[160px]">
                        <SelectValue placeholder="Brand" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">All Brands</SelectItem>
                        {brands.map((brand) => (
                            <SelectItem key={brand.id} value={brand.id}>
                                {brand.name} ({brand.products_count})
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
            )}

            {stores && stores.length > 0 && (
                <Select
                    value={filters.store_id ?? ''}
                    onValueChange={(value) => onFilterChange({ store_id: value || undefined })}
                >
                    <SelectTrigger className="h-8 w-[160px]">
                        <SelectValue placeholder="Store" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">All Stores</SelectItem>
                        {stores.map((store) => (
                            <SelectItem key={store.id} value={store.id}>
                                {store.name}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
            )}

            {hasActiveFilters && (
                <Button variant="ghost" size="sm" className="h-8 gap-1 px-2" onClick={onClear}>
                    <RotateCcwIcon className="size-3" />
                    Reset
                </Button>
            )}
        </div>
    );
}

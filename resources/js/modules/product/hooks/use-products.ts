import { useCallback } from 'react';
import { router, usePage } from '@inertiajs/react';
import type { PaginatedResponse } from '@/modules/shared/types';
import type { Product, ProductFilters } from '../types';

type UseProductsReturn = {
    products: PaginatedResponse<Product>;
    filters: ProductFilters;
    navigate: (params: Partial<ProductFilters>) => void;
    clearFilters: () => void;
};

export function useProducts(): UseProductsReturn {
    const { products, filters } = usePage<{
        products: PaginatedResponse<Product>;
        filters: ProductFilters;
    }>().props;

    const navigate = useCallback((params: Partial<ProductFilters>) => {
        router.get('/products', params, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    }, []);

    const clearFilters = useCallback(() => {
        router.get('/products', {}, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    }, []);

    return { products, filters, navigate, clearFilters };
}

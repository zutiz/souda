import { useCallback } from 'react';
import { router, usePage } from '@inertiajs/react';
import type { Order, OrderFilters } from '../types';

type UseOrdersReturn = {
    orders: Order[];
    filters: OrderFilters;
    navigate: (params: Partial<OrderFilters>) => void;
    clearFilters: () => void;
};

export function useOrders(): UseOrdersReturn {
    const { orders, filters } = usePage<{
        orders: Order[];
        filters: OrderFilters;
    }>().props;

    const navigate = useCallback((params: Partial<OrderFilters>) => {
        router.get(route('orders.index'), params, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    }, []);

    const clearFilters = useCallback(() => {
        router.get(route('orders.index'), {}, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    }, []);

    return { orders, filters, navigate, clearFilters };
}

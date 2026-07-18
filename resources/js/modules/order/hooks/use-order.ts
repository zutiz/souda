import { usePage } from '@inertiajs/react';
import type { Order } from '../types';

type UseOrderReturn = {
    order: Order;
};

export function useOrder(): UseOrderReturn {
    const { order } = usePage<{ order: Order }>().props;

    return { order };
}

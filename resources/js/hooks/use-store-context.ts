import { usePage } from '@inertiajs/react';
import type { Store, StorePageProps } from '@/types';

export function useStoreContext() {
    const { props } = usePage<StorePageProps>();
    const { currentStore, stores = [] } = props;

    const isStoreContext = currentStore !== null && currentStore !== undefined;

    return {
        currentStore: currentStore ?? null,
        stores,
        isStoreContext,
        storeId: currentStore?.id ?? null,
        storeSlug: currentStore?.slug ?? null,
        currency: currentStore?.currency ?? 'XOF',
    };
}

export type { Store };

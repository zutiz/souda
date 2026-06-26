import { useCallback, useState } from 'react';
import { router } from '@inertiajs/react';
import type { BulkAction } from '../types';

type UseProductMutationsReturn = {
    processing: boolean;
    bulkAction: (action: BulkAction, ids: string[]) => void;
    deleteProduct: (id: string) => void;
};

export function useProductMutations(): UseProductMutationsReturn {
    const [processing, setProcessing] = useState(false);

    const bulkAction = useCallback((action: BulkAction, ids: string[]) => {
        setProcessing(true);
        router.post(
            '/products/bulk',
            { action, ids },
            {
                preserveState: true,
                preserveScroll: true,
                onFinish: () => setProcessing(false),
            },
        );
    }, []);

    const deleteProduct = useCallback((id: string) => {
        setProcessing(true);
        router.delete(`/products/${id}`, {
            preserveState: true,
            preserveScroll: true,
            onFinish: () => setProcessing(false),
        });
    }, []);

    return { processing, bulkAction, deleteProduct };
}

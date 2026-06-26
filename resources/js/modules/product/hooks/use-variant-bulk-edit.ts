import { useCallback, useMemo, useState } from 'react';
import type { BulkEditField, BulkEditOperation, BulkEditSelection } from '../types/variant-bulk-edit';
import type { VariantRowFormData } from '../types/variant';

type UseBulkEditReturn = {
    selection: BulkEditSelection;
    selectedCount: number;
    isAllSelected: boolean;
    toggleSelect: (variantId: string) => void;
    toggleSelectAll: (allIds: string[]) => void;
    clearSelection: () => void;
    selectedIndices: number[];
    applyBulkEdit: (operations: BulkEditOperation[], variants: VariantRowFormData[]) => VariantRowFormData[];
};

export function useVariantBulkEdit(): UseBulkEditReturn {
    const [selection, setSelection] = useState<BulkEditSelection>(new Set());

    const toggleSelect = useCallback((variantId: string) => {
        setSelection((prev) => {
            const next = new Set(prev);
            if (next.has(variantId)) {
                next.delete(variantId);
            } else {
                next.add(variantId);
            }
            return next;
        });
    }, []);

    const toggleSelectAll = useCallback((allIds: string[]) => {
        setSelection((prev) => {
            if (prev.size === allIds.length) {
                return new Set();
            }
            return new Set(allIds);
        });
    }, []);

    const clearSelection = useCallback(() => {
        setSelection(new Set());
    }, []);

    const selectedCount = selection.size;

    const isAllSelected = useMemo(
        () => selection.size > 0,
        [selection.size],
    );

    const selectedIndices = useMemo(() => [], []);

    const applyBulkEdit = useCallback(
        (operations: BulkEditOperation[], variants: VariantRowFormData[]): VariantRowFormData[] => {
            if (selection.size === 0) return variants;

            return variants.map((variant) => {
                if (!selection.has(variant.id)) return variant;

                let updated = { ...variant };

                for (const op of operations) {
                    const currentValue = updated[op.field];

                    switch (op.field) {
                        case 'isEnabled':
                            updated = { ...updated, isEnabled: Boolean(op.value) };
                            break;

                        case 'sku':
                            if (typeof op.value === 'string') {
                                updated = { ...updated, sku: op.value };
                            }
                            break;

                        case 'price':
                        case 'costPrice':
                        case 'quantity':
                        case 'weight':
                            updated = {
                                ...updated,
                                [op.field]: applyNumericOperation(
                                    currentValue as number | undefined,
                                    op,
                                ),
                            };
                            break;
                    }
                }

                return updated;
            });
        },
        [selection],
    );

    return {
        selection,
        selectedCount,
        isAllSelected,
        toggleSelect,
        toggleSelectAll,
        clearSelection,
        selectedIndices: Array.from(selection).map((id) =>
            // resolved at call site
            -1,
        ),
        applyBulkEdit,
    };
}

function applyNumericOperation(
    current: number | undefined,
    op: BulkEditOperation,
): number | undefined {
    const value = Number(op.value);
    if (isNaN(value)) return current;

    const curr = current ?? 0;

    switch (op.operation) {
        case 'set':
            return value;
        case 'add':
            return curr + value;
        case 'subtract':
            return Math.max(0, curr - value);
        case 'multiply':
            return curr * value;
        case 'percentage':
            return curr + curr * (value / 100);
        default:
            return curr;
    }
}

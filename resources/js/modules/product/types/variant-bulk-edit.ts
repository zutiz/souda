import type { VariantRowFormData } from './variant';

export type BulkEditField = keyof Pick<
    VariantRowFormData,
    'price' | 'costPrice' | 'quantity' | 'weight' | 'isEnabled' | 'sku'
>;

export type BulkEditOperation = {
    field: BulkEditField;
    operation: 'set' | 'add' | 'subtract' | 'multiply' | 'percentage';
    value: number | string | boolean;
};

export type BulkEditSelection = Set<string>;

export type BulkEditState = {
    isOpen: boolean;
    selectedCount: number;
    operations: BulkEditOperation[];
};

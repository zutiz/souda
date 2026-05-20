import { useRef, useCallback, useMemo } from 'react';
import { useVirtualizer } from '@tanstack/react-virtual';
import { Checkbox } from '@/components/ui/checkbox';
import { VariantRow } from './variant-row';
import type { VariantRowFormData } from '../../types/variant';
import type { BulkEditSelection } from '../../types/variant-bulk-edit';
import { validateSkuUniqueness, validateBarcodeUniqueness } from '../../lib/variant-utils';

const ROW_HEIGHT = 44;
const OVERSCAN = 10;

type Props = {
    variants: VariantRowFormData[];
    attributeKeys: string[];
    selection: BulkEditSelection;
    productImages?: any[];
    onToggleSelect: (id: string) => void;
    onToggleSelectAll: () => void;
    onUpdateVariant: (index: number, field: keyof VariantRowFormData, value: unknown) => void;
    onRemoveVariant: (index: number) => void;
    onDuplicateVariant: (index: number) => void;
    onToggleEnabled: (index: number) => void;
};

export function VariantVirtualTable({
    variants,
    attributeKeys,
    selection,
    productImages = [],
    onToggleSelect,
    onToggleSelectAll,
    onUpdateVariant,
    onRemoveVariant,
    onDuplicateVariant,
    onToggleEnabled,
}: Props) {
    const parentRef = useRef<HTMLDivElement>(null);
    const isAllSelected = variants.length > 0 && selection.size === variants.length;

    const duplicateSkus = useMemo(() => validateSkuUniqueness(variants), [variants]);
    const duplicateBarcodes = useMemo(() => validateBarcodeUniqueness(variants), [variants]);

    const hasDupSku = useCallback(
        (v: VariantRowFormData) => {
            if (!v.sku) return false;
            const ids = duplicateSkus.get(v.sku);
            return ids !== undefined && ids.length > 1;
        },
        [duplicateSkus],
    );

    const hasDupBarcode = useCallback(
        (v: VariantRowFormData) => {
            if (!v.barcode) return false;
            const ids = duplicateBarcodes.get(v.barcode);
            return ids !== undefined && ids.length > 1;
        },
        [duplicateBarcodes],
    );

    const rowVirtualizer = useVirtualizer({
        count: variants.length,
        getScrollElement: () => parentRef.current,
        estimateSize: () => ROW_HEIGHT,
        overscan: OVERSCAN,
    });

    return (
        <div className="space-y-0">
            {/* Fixed header */}
            <div className="bg-background overflow-hidden rounded-t-lg border">
                <table className="w-full border-collapse text-xs">
                    <thead>
                        <tr className="border-b bg-muted/50">
                            <th className="w-10 px-2 py-2 text-center">
                                <Checkbox
                                    checked={isAllSelected}
                                    onCheckedChange={onToggleSelectAll}
                                />
                            </th>
                            <th className="w-10 px-2 py-2" />
                            {attributeKeys.map((key) => (
                                <th
                                    key={key}
                                    className="text-muted-foreground max-w-[100px] truncate px-2 py-2 text-left text-[11px] font-semibold uppercase tracking-wider"
                                >
                                    {key}
                                </th>
                            ))}
                            <th className="text-muted-foreground px-1.5 py-2 text-left text-[11px] font-semibold uppercase tracking-wider">
                                SKU
                            </th>
                            <th className="text-muted-foreground px-1.5 py-2 text-left text-[11px] font-semibold uppercase tracking-wider">
                                Barcode
                            </th>
                            <th className="text-muted-foreground px-1.5 py-2 text-left text-[11px] font-semibold uppercase tracking-wider">
                                Price
                            </th>
                            <th className="text-muted-foreground px-1.5 py-2 text-left text-[11px] font-semibold uppercase tracking-wider">
                                Cost
                            </th>
                            <th className="text-muted-foreground px-1.5 py-2 text-left text-[11px] font-semibold uppercase tracking-wider">
                                Qty
                            </th>
                            <th className="text-muted-foreground px-1.5 py-2 text-left text-[11px] font-semibold uppercase tracking-wider">
                                Wt
                            </th>
                            <th className="text-muted-foreground px-2 py-2 text-center text-[11px] font-semibold uppercase tracking-wider">
                                On
                            </th>
                            <th className="w-16 px-1 py-2" />
                        </tr>
                    </thead>
                </table>
            </div>

            {/* Virtualized body */}
            <div
                ref={parentRef}
                className="overflow-auto rounded-b-lg border border-t-0"
                style={{ maxHeight: Math.min(variants.length * ROW_HEIGHT, 600) }}
            >
                <div
                    style={{
                        height: `${rowVirtualizer.getTotalSize()}px`,
                        width: '100%',
                        position: 'relative',
                    }}
                >
                    <table className="border-collapse text-xs" style={{ width: '100%', tableLayout: 'fixed' }}>
                        <tbody>
                            {rowVirtualizer.getVirtualItems().map((virtualRow) => {
                                const variant = variants[virtualRow.index];
                                if (!variant) return null;

                                return (
                                    <VariantRow
                                        key={variant.id}
                                        variant={variant}
                                        index={virtualRow.index}
                                        attributeKeys={attributeKeys}
                                        isSelected={selection.has(variant.id)}
                                        hasDuplicateSku={hasDupSku(variant)}
                                        hasDuplicateBarcode={hasDupBarcode(variant)}
                                        productImages={productImages}
                                        onToggleSelect={() => onToggleSelect(variant.id)}
                                        onUpdate={(field, value) =>
                                            onUpdateVariant(virtualRow.index, field, value)
                                        }
                                        onRemove={() => onRemoveVariant(virtualRow.index)}
                                        onDuplicate={() => onDuplicateVariant(virtualRow.index)}
                                        onToggleEnabled={() => onToggleEnabled(virtualRow.index)}
                                        style={{
                                            position: 'absolute',
                                            top: 0,
                                            left: 0,
                                            width: '100%',
                                            transform: `translateY(${virtualRow.start}px)`,
                                            height: `${virtualRow.size}px`,
                                        }}
                                    />
                                );
                            })}
                        </tbody>
                    </table>
                </div>
            </div>

            {/* Footer with summary */}
            <div className="text-muted-foreground flex items-center justify-between rounded-b-lg border border-t-0 bg-muted/20 px-4 py-1.5 text-[11px]">
                <span>{variants.length} variant{variants.length !== 1 ? 's' : ''}</span>
                <div className="flex gap-4">
                    <span>{variants.filter((v) => v.isEnabled).length} enabled</span>
                    <span>
                        {variants.reduce((s, v) => s + (v.isEnabled ? (v.quantity as number) : 0), 0)} total stock
                    </span>
                </div>
            </div>
        </div>
    );
}

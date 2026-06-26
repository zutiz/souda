import { useCallback, useState } from 'react';
import { PlusIcon, Wand2Icon, TagsIcon, BarcodeIcon } from 'lucide-react';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
    DialogFooter,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { FormSection } from '@/modules/shared/components/form-section';
import { VariantGeneratorDialog } from './variant-generator-dialog';
import { VariantEmptyState } from './variant/variant-empty-state';
import { VariantVirtualTable } from './variant/variant-virtual-table';
import { VariantBulkEditBar } from './variant/variant-bulk-edit-bar';
import { VariantBulkEditDialog } from './variant/variant-bulk-edit-dialog';
import { VariantSkuGenerator } from './variant/variant-sku-generator';
import { useVariantState } from '../hooks/use-variant-state';
import { useVariantBulkEdit } from '../hooks/use-variant-bulk-edit';
import type { VariantGroup, VariantRowFormData } from '../types/variant';
import type { SkuGenerationConfig } from '../types/variant-sku';

type Props = {
    variants: VariantRowFormData[];
    variantGroups: VariantGroup[];
    parentSku?: string;
    productImages?: any[];
    errors: Partial<Record<string, string>>;
    onChange: (field: 'variants', value: VariantRowFormData[]) => void;
    onGroupsChange: (field: 'variantGroups', value: VariantGroup[]) => void;
};

export function VariantGrid({
    variants,
    variantGroups,
    parentSku,
    productImages = [],
    errors,
    onChange,
    onGroupsChange,
}: Props) {
    const [generatorOpen, setGeneratorOpen] = useState(false);
    const [skuGeneratorOpen, setSkuGeneratorOpen] = useState(false);
    const [barcodeGeneratorOpen, setBarcodeGeneratorOpen] = useState(false);
    const [bulkEditOpen, setBulkEditOpen] = useState(false);

    const state = useVariantState(variants, variantGroups, onChange, onGroupsChange);
    const bulk = useVariantBulkEdit();

    const handleGenerateFromGroups = useCallback(
        (groups: VariantGroup[]) => {
            state.generateFromGroups(groups, parentSku);
        },
        [state, parentSku],
    );

    const handleBarcodeGenerate = useCallback(() => {
        state.generateBarcodes({ format: 'ean13' });
        setBarcodeGeneratorOpen(false);
    }, [state]);

    const handleSkuGenerate = useCallback(
        (config: SkuGenerationConfig) => {
            state.generateSkus(config, variantGroups, parentSku);
            setSkuGeneratorOpen(false);
        },
        [state, variantGroups, parentSku],
    );

    const handleBulkEditApply = useCallback(
        (operations: any[]) => {
            const updated = bulk.applyBulkEdit(operations, variants);
            onChange('variants', updated);
            bulk.clearSelection();
        },
        [bulk, variants, onChange],
    );

    const handleDeleteSelected = useCallback(() => {
        const remaining = variants.filter((v) => !bulk.selection.has(v.id));
        onChange('variants', remaining);
        bulk.clearSelection();
    }, [bulk.selection, variants, onChange, bulk.clearSelection]);

    const handleToggleSelectAll = useCallback(() => {
        if (bulk.selection.size === variants.length) {
            bulk.clearSelection();
        } else {
            bulk.toggleSelectAll(variants.map((v) => v.id));
        }
    }, [bulk, variants]);

    return (
        <FormSection
            title="Variants"
            description={`Manage product variants — ${state.totalEnabled} enabled, ${state.totalStock} total stock`}
        >
            <div className="space-y-4">
                <VariantBulkEditBar
                    selectedCount={bulk.selectedCount}
                    onClearSelection={bulk.clearSelection}
                    onBulkEdit={() => setBulkEditOpen(true)}
                    onGenerateSkus={() => setSkuGeneratorOpen(true)}
                    onGenerateBarcodes={() => setBarcodeGeneratorOpen(true)}
                    onDeleteSelected={handleDeleteSelected}
                />

                {variants.length > 0 ? (
                    <VariantVirtualTable
                        variants={variants}
                        attributeKeys={state.attributeKeys}
                        selection={bulk.selection}
                        productImages={productImages}
                        onToggleSelect={bulk.toggleSelect}
                        onToggleSelectAll={handleToggleSelectAll}
                        onUpdateVariant={state.updateVariant}
                        onRemoveVariant={state.removeVariant}
                        onDuplicateVariant={state.duplicateVariant}
                        onToggleEnabled={state.toggleVariantEnabled}
                    />
                ) : (
                    <VariantEmptyState
                        onGenerate={() => setGeneratorOpen(true)}
                        onAdd={state.addVariant}
                    />
                )}

                <div className="flex flex-wrap gap-2">
                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        onClick={() => setGeneratorOpen(true)}
                    >
                        <Wand2Icon className="mr-1 size-3.5" />
                        Generate Variants
                    </Button>
                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        onClick={state.addVariant}
                    >
                        <PlusIcon className="mr-1 size-3.5" />
                        Add Manually
                    </Button>
                    {variants.length > 0 && (
                        <>
                            <Button
                                type="button"
                                variant="ghost"
                                size="sm"
                                onClick={() => setSkuGeneratorOpen(true)}
                            >
                                <TagsIcon className="mr-1 size-3.5" />
                                Generate SKUs
                            </Button>
                            <Button
                                type="button"
                                variant="ghost"
                                size="sm"
                                onClick={() => setBarcodeGeneratorOpen(true)}
                            >
                                <BarcodeIcon className="mr-1 size-3.5" />
                                Generate Barcodes
                            </Button>
                        </>
                    )}
                </div>

                {errors.variants && (
                    <p className="text-destructive text-xs">{errors.variants}</p>
                )}
            </div>

            <VariantGeneratorDialog
                open={generatorOpen}
                onOpenChange={setGeneratorOpen}
                onGenerate={handleGenerateFromGroups}
                existingGroups={variantGroups}
            />

            <VariantSkuGenerator
                open={skuGeneratorOpen}
                onOpenChange={setSkuGeneratorOpen}
                onGenerate={handleSkuGenerate}
                groups={variantGroups}
                parentSku={parentSku}
            />

            <Dialog open={barcodeGeneratorOpen} onOpenChange={setBarcodeGeneratorOpen}>
                <DialogContent className="sm:max-w-sm">
                    <DialogHeader>
                        <DialogTitle>Generate Barcodes</DialogTitle>
                        <DialogDescription>
                            Auto-generate EAN-13 barcodes for variants without one
                        </DialogDescription>
                    </DialogHeader>
                    <p className="text-muted-foreground text-sm">
                        Barcodes will only be generated for variants that don't already have one.
                        Uses EAN-13 format with auto-calculated check digit.
                    </p>
                    <DialogFooter>
                        <Button type="button" variant="outline" onClick={() => setBarcodeGeneratorOpen(false)}>
                            Cancel
                        </Button>
                        <Button type="button" onClick={handleBarcodeGenerate}>
                            Generate
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            <VariantBulkEditDialog
                open={bulkEditOpen}
                onOpenChange={setBulkEditOpen}
                onApply={handleBulkEditApply}
            />
        </FormSection>
    );
}



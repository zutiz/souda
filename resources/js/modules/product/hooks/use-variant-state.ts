import { useCallback, useMemo, useRef } from 'react';
import type { VariantGroup, VariantRowFormData } from '../types/variant';
import type { SkuGenerationConfig, BarcodeGenerationConfig } from '../types/variant-sku';
import { generateVariantsFromGroups } from '../lib/variant-combinations';
import { generateBarcode } from '../lib/variant-combinations';
import { getAttributeKeys, createEmptyVariant, areGroupsEqual } from '../lib/variant-utils';

type VariantStateActions = {
    generateFromGroups: (
        groups: VariantGroup[],
        parentSku?: string,
        skuConfig?: SkuGenerationConfig,
    ) => void;
    updateVariant: (index: number, field: keyof VariantRowFormData, value: unknown) => void;
    removeVariant: (index: number) => void;
    addVariant: () => void;
    duplicateVariant: (index: number) => void;
    batchUpdateVariants: (indices: number[], field: keyof VariantRowFormData, value: unknown) => void;
    generateBarcodes: (config: BarcodeGenerationConfig) => void;
    generateSkus: (config: SkuGenerationConfig, groups: VariantGroup[], parentSku?: string) => void;
    toggleVariantEnabled: (index: number) => void;
    removeAllVariants: () => void;
    reorderVariant: (fromIndex: number, toIndex: number) => void;
};

export function useVariantState(
    variants: VariantRowFormData[],
    variantGroups: VariantGroup[],
    onChange: (field: 'variants', value: VariantRowFormData[]) => void,
    onGroupsChange: (field: 'variantGroups', value: VariantGroup[]) => void,
): VariantStateActions & {
    attributeKeys: string[];
    totalEnabled: number;
    totalStock: number;
    groupsChanged: boolean;
} {
    const variantsRef = useRef(variants);
    variantsRef.current = variants;

    const update = useCallback(
        (updater: (prev: VariantRowFormData[]) => VariantRowFormData[]) => {
            onChange('variants', updater(variantsRef.current));
        },
        [onChange],
    );

    const generateFromGroups = useCallback(
        (groups: VariantGroup[], parentSku?: string, skuConfig?: SkuGenerationConfig) => {
            onGroupsChange('variantGroups', groups);
            const newVariants = generateVariantsFromGroups(groups, [], parentSku, skuConfig);
            onChange('variants', newVariants);
        },
        [onChange, onGroupsChange],
    );

    const updateVariant = useCallback(
        (index: number, field: keyof VariantRowFormData, value: unknown) => {
            update((prev) =>
                prev.map((v, i) => (i === index ? { ...v, [field]: value } : v)),
            );
        },
        [update],
    );

    const removeVariant = useCallback(
        (index: number) => {
            update((prev) => prev.filter((_, i) => i !== index));
        },
        [update],
    );

    const addVariant = useCallback(() => {
        update((prev) => [...prev, createEmptyVariant()]);
    }, [update]);

    const duplicateVariant = useCallback(
        (index: number) => {
            update((prev) => {
                const source = prev[index];
                if (!source) return prev;
                const clone: VariantRowFormData = {
                    ...source,
                    id: crypto.randomUUID(),
                    sku: source.sku ? `${source.sku}-COPY` : '',
                };
                const copy = [...prev];
                copy.splice(index + 1, 0, clone);
                return copy;
            });
        },
        [update],
    );

    const batchUpdateVariants = useCallback(
        (indices: number[], field: keyof VariantRowFormData, value: unknown) => {
            const indexSet = new Set(indices);
            update((prev) =>
                prev.map((v, i) => (indexSet.has(i) ? { ...v, [field]: value } : v)),
            );
        },
        [update],
    );

    const generateBarcodes = useCallback(
        (config: BarcodeGenerationConfig) => {
            update((prev) =>
                prev.map((v, i) => ({
                    ...v,
                    barcode: v.barcode || generateBarcode(config.format, i, config.prefix),
                })),
            );
        },
        [update],
    );

    const generateSkus = useCallback(
        (config: SkuGenerationConfig, groups: VariantGroup[], parentSku?: string) => {
            update((prev) => {
                const comboValues = groups.map((g) => g.values);
                const combinations = cartesianProductLocal(comboValues);

                return prev.map((v, i) => {
                    const combo = combinations[i] ?? [];
                    const sku = generateSkuLocal(config, groups, combo, i, parentSku);
                    return { ...v, sku: v.sku || sku };
                });
            });
        },
        [update],
    );

    const toggleVariantEnabled = useCallback(
        (index: number) => {
            update((prev) =>
                prev.map((v, i) =>
                    i === index ? { ...v, isEnabled: !v.isEnabled } : v,
                ),
            );
        },
        [update],
    );

    const removeAllVariants = useCallback(() => {
        onChange('variants', []);
    }, [onChange]);

    const reorderVariant = useCallback(
        (fromIndex: number, toIndex: number) => {
            update((prev) => {
                const copy = [...prev];
                const [moved] = copy.splice(fromIndex, 1);
                copy.splice(toIndex, 0, moved);
                return copy;
            });
        },
        [update],
    );

    const attributeKeys = useMemo(() => getAttributeKeys(variants), [variants]);
    const totalEnabled = useMemo(() => variants.filter((v) => v.isEnabled).length, [variants]);
    const totalStock = useMemo(
        () => variants.reduce((sum, v) => sum + (v.isEnabled ? v.quantity : 0), 0),
        [variants],
    );
    const groupsChanged = useMemo(
        () => !areGroupsEqual(variantGroups, []),
        [variantGroups],
    );

    return {
        generateFromGroups,
        updateVariant,
        removeVariant,
        addVariant,
        duplicateVariant,
        batchUpdateVariants,
        generateBarcodes,
        generateSkus,
        toggleVariantEnabled,
        removeAllVariants,
        reorderVariant,
        attributeKeys,
        totalEnabled,
        totalStock,
        groupsChanged,
    };
}

function cartesianProductLocal<T>(arrays: T[][]): T[][] {
    if (arrays.length === 0) return [];
    return arrays.reduce<T[][]>(
        (acc, curr) => acc.flatMap((a) => curr.map((b) => [...a, b])),
        [[]],
    );
}

function generateSkuLocal(
    config: SkuGenerationConfig,
    groups: VariantGroup[],
    combo: string[],
    index: number,
    parentSku?: string,
): string {
    switch (config.strategy) {
        case 'pattern': {
            const parts: string[] = [];
            if (config.includeProductSku && parentSku) {
                parts.push(parentSku);
            }
            for (const attrName of config.attributeOrder) {
                const gi = groups.findIndex((g) => g.attributeName === attrName);
                if (gi !== -1 && combo[gi]) {
                    const abbr = combo[gi]
                        .split(/\s+/)
                        .map((w) => w.charAt(0).toUpperCase())
                        .join('');
                    parts.push(abbr);
                }
            }
            return parts.join(config.separator).toUpperCase();
        }
        case 'sequential': {
            const num = (config.startFrom + index).toString().padStart(config.padding, '0');
            return `${config.prefix}${num}`;
        }
        case 'attribute': {
            const parts: string[] = [];
            for (const attrName of config.attributeOrder) {
                const gi = groups.findIndex((g) => g.attributeName === attrName);
                if (gi !== -1 && combo[gi]) {
                    parts.push(combo[gi].toUpperCase().slice(0, config.maxLength));
                }
            }
            return parts.join(config.separator);
        }
        case 'custom': {
            let sku = config.template;
            if (parentSku) sku = sku.replace(/\{sku\}/g, parentSku);
            groups.forEach((g, i) => {
                sku = sku.replace(new RegExp(`\\{${g.attributeName}\\}`, 'g'), combo[i] ?? '');
            });
            sku = sku.replace(/\{index\}/g, String(index + 1));
            return sku.toUpperCase();
        }
        default:
            return '';
    }
}

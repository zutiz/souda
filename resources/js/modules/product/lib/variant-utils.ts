import type { VariantRowFormData, VariantGroup } from '../types/variant';

export function getAttributeKeys(variants: VariantRowFormData[]): string[] {
    return Array.from(new Set(variants.flatMap((v) => Object.keys(v.attributes))));
}

export function getSkuVariantCount(
    variants: VariantRowFormData[],
    sku: string,
    excludeId?: string,
): number {
    return variants.filter(
        (v) => v.sku === sku && v.id !== excludeId,
    ).length;
}

export function getBarcodeVariantCount(
    variants: VariantRowFormData[],
    barcode: string,
    excludeId?: string,
): number {
    return variants.filter(
        (v) => v.barcode === barcode && v.id !== excludeId,
    ).length;
}

export function isVariantComplete(variant: VariantRowFormData): boolean {
    return (
        Boolean(variant.sku) &&
        variant.price != null &&
        variant.price > 0 &&
        variant.quantity >= 0
    );
}

export function getEnabledCount(variants: VariantRowFormData[]): number {
    return variants.filter((v) => v.isEnabled).length;
}

export function getTotalStock(variants: VariantRowFormData[]): number {
    return variants.reduce((sum, v) => sum + (v.isEnabled ? v.quantity : 0), 0);
}

export function validateSkuUniqueness(
    variants: VariantRowFormData[],
): Map<string, string[]> {
    const skuMap = new Map<string, string[]>();
    for (const v of variants) {
        if (!v.sku) continue;
        const existing = skuMap.get(v.sku) ?? [];
        existing.push(v.id);
        skuMap.set(v.sku, existing);
    }
    const duplicates = new Map<string, string[]>();
    for (const [sku, ids] of skuMap) {
        if (ids.length > 1) {
            duplicates.set(sku, ids);
        }
    }
    return duplicates;
}

export function validateBarcodeUniqueness(
    variants: VariantRowFormData[],
): Map<string, string[]> {
    const barcodeMap = new Map<string, string[]>();
    for (const v of variants) {
        if (!v.barcode) continue;
        const existing = barcodeMap.get(v.barcode) ?? [];
        existing.push(v.id);
        barcodeMap.set(v.barcode, existing);
    }
    const duplicates = new Map<string, string[]>();
    for (const [barcode, ids] of barcodeMap) {
        if (ids.length > 1) {
            duplicates.set(barcode, ids);
        }
    }
    return duplicates;
}

export function areGroupsEqual(a: VariantGroup[], b: VariantGroup[]): boolean {
    if (a.length !== b.length) return false;
    return a.every((ga, i) => {
        const gb = b[i];
        return (
            ga.attributeName === gb.attributeName &&
            ga.attributeId === gb.attributeId &&
            ga.values.length === gb.values.length &&
            ga.values.every((v, j) => v === gb.values[j])
        );
    });
}

export function createEmptyVariant(attributes?: Record<string, string>): VariantRowFormData {
    return {
        id: crypto.randomUUID(),
        sku: '',
        quantity: 0,
        isEnabled: true,
        attributes: attributes ?? {},
    };
}

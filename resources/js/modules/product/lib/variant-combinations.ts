import type { VariantGroup, VariantRowFormData } from '../types/variant';
import type { SkuGenerationConfig } from '../types/variant-sku';

export function cartesianProduct<T>(arrays: T[][]): T[][] {
    if (arrays.length === 0) return [];
    return arrays.reduce<T[][]>(
        (acc, curr) => acc.flatMap((a) => curr.map((b) => [...a, b])),
        [[]],
    );
}

export function generateVariantsFromGroups(
    groups: VariantGroup[],
    existingVariants: VariantRowFormData[],
    parentSku?: string,
    skuConfig?: SkuGenerationConfig,
): VariantRowFormData[] {
    const comboValues = groups.map((g) => g.values);
    const combinations = cartesianProduct(comboValues);

    return combinations.map((combo, index) => {
        const attributes: Record<string, string> = {};
        groups.forEach((g, i) => {
            attributes[g.attributeName] = combo[i];
        });

        const id = crypto.randomUUID();

        return {
            id,
            sku: skuConfig ? generateSku(skuConfig, groups, combo, index, parentSku) : '',
            price: undefined,
            costPrice: undefined,
            quantity: 0,
            weight: undefined,
            isEnabled: true,
            attributes,
        };
    });
}

export function generateSku(
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
                const groupIndex = groups.findIndex((g) => g.attributeName === attrName);
                if (groupIndex !== -1 && combo[groupIndex]) {
                    parts.push(abbreviate(combo[groupIndex]));
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
                const groupIndex = groups.findIndex((g) => g.attributeName === attrName);
                if (groupIndex !== -1 && combo[groupIndex]) {
                    const val = combo[groupIndex].toUpperCase().slice(0, config.maxLength);
                    parts.push(val);
                }
            }
            return parts.join(config.separator);
        }

        case 'custom': {
            let sku = config.template;
            if (parentSku) {
                sku = sku.replace(/\{sku\}/g, parentSku);
            }
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

function abbreviate(value: string): string {
    return value
        .split(/\s+/)
        .map((w) => w.charAt(0).toUpperCase())
        .join('');
}

export function generateBarcode(
    format: 'ean13' | 'upc' | 'code128',
    index: number,
    prefix?: string,
): string {
    switch (format) {
        case 'ean13': {
            const base = (prefix ?? '200') + index.toString().padStart(9, '0');
            const checkDigit = calculateEan13CheckDigit(base);
            return base + checkDigit;
        }
        case 'upc': {
            const base = (prefix ?? '04') + index.toString().padStart(9, '0');
            const checkDigit = calculateUpcCheckDigit(base);
            return base + checkDigit;
        }
        case 'code128': {
            return `${prefix ?? 'VAR'}${index.toString().padStart(8, '0')}`;
        }
        default:
            return '';
    }
}

function calculateEan13CheckDigit(barcode: string): string {
    let sum = 0;
    for (let i = 0; i < barcode.length; i++) {
        const digit = parseInt(barcode[i], 10);
        sum += i % 2 === 0 ? digit : digit * 3;
    }
    const check = (10 - (sum % 10)) % 10;
    return String(check);
}

function calculateUpcCheckDigit(barcode: string): string {
    let oddSum = 0;
    let evenSum = 0;
    for (let i = 0; i < barcode.length; i++) {
        const digit = parseInt(barcode[i], 10);
        if (i % 2 === 0) {
            oddSum += digit;
        } else {
            evenSum += digit;
        }
    }
    const check = (10 - ((oddSum * 3 + evenSum) % 10)) % 10;
    return String(check);
}

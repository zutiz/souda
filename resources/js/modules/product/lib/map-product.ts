import type { ProductFormData } from '../types/product-form';
import type { VariantRowFormData, AttributeFormData, VariantGroup } from '../types/variant';

export function mapProductToFormData(product: Record<string, any>): Partial<ProductFormData> {
    const dimensions = product.dimensions ?? {};

    return {
        name: product.name ?? '',
        description: product.description ?? '',
        status: product.status ?? 'draft',
        categoryId: product.category_id != null ? String(product.category_id) : null,
        brandId: product.brand_id != null ? String(product.brand_id) : null,
        price: product.base_price != null ? product.base_price / 100 : 0,
        comparePrice: product.compare_at_price != null ? product.compare_at_price / 100 : null,
        costPrice: product.cost_price != null ? product.cost_price / 100 : null,
        isTaxable: product.tax_inclusive ?? true,
        sku: product.sku ?? '',
        barcode: product.barcode ?? null,
        trackStock: product.track_inventory ?? true,
        quantity: product.quantity ?? 0,
        lowStockThreshold: product.low_stock_threshold ?? 5,
        allowBackorders: product.allow_backorders ?? false,
        weight: dimensions.weight ?? null,
        length: dimensions.length ?? null,
        width: dimensions.width ?? null,
        height: dimensions.height ?? null,
        freeShipping: product.free_shipping ?? false,
        metaTitle: product.meta_title ?? null,
        metaDescription: product.meta_description ?? null,
        slug: product.slug ?? null,
        images: mapImages(product.media ?? product.images ?? []),
        variants: mapVariants(product.variants ?? []),
        variantGroups: mapVariantGroups(product.variant_groups ?? []),
        attributes: mapAttributes(product.attribute_values ?? []),
    };
}

function mapImages(media: any[]): any[] {
    return media.map((m: any, i: number) => ({
        id: m.id ?? `existing_${i}`,
        preview: m.original_url ?? m.url ?? m.preview ?? '',
        isMain: i === 0,
    }));
}

function mapVariants(variants: any[]): VariantRowFormData[] {
    return variants.map((v: any) => ({
        id: v.id ?? crypto.randomUUID(),
        sku: v.sku ?? '',
        barcode: v.barcode ?? '',
        price: v.price != null ? v.price / 100 : undefined,
        costPrice: v.cost_price != null ? v.cost_price / 100 : undefined,
        quantity: v.quantity ?? 0,
        weight: v.weight ?? undefined,
        isEnabled: v.is_enabled ?? v.status !== 'archived',
        image: v.image ?? undefined,
        attributes: v.attributes ?? {},
    }));
}

function mapVariantGroups(groups: any[]): VariantGroup[] {
    return (groups ?? []).map((g: any) => ({
        attributeId: g.attribute_id ?? g.attributeId ?? crypto.randomUUID(),
        attributeName: g.attribute_name ?? g.attributeName ?? '',
        values: g.values ?? [],
    }));
}

function mapAttributes(values: any[]): AttributeFormData[] {
    return (values ?? []).map((v: any) => ({
        id: v.attribute_id ?? v.id ?? crypto.randomUUID(),
        value: v.value ?? v.name ?? '',
    }));
}

export function mapFormToPayload(data: ProductFormData): Record<string, any> {
    const dimensions: Record<string, number> = {};
    if (data.weight != null) dimensions.weight = data.weight;
    if (data.length != null) dimensions.length = data.length;
    if (data.width != null) dimensions.width = data.width;
    if (data.height != null) dimensions.height = data.height;

    return {
        name: data.name,
        description: data.description || null,
        slug: data.slug || null,
        status: data.status,
        type: 'simple',
        category_id: data.categoryId ? Number(data.categoryId) : null,
        brand_id: data.brandId ? Number(data.brandId) : null,
        base_price: Math.round(data.price * 100),
        compare_at_price: data.comparePrice != null ? Math.round(data.comparePrice * 100) : null,
        cost_price: data.costPrice != null ? Math.round(data.costPrice * 100) : null,
        tax_inclusive: data.isTaxable,
        sku: data.sku || null,
        barcode: data.barcode || null,
        track_inventory: data.trackStock,
        low_stock_threshold: data.lowStockThreshold,
        allow_backorders: data.allowBackorders,
        dimensions: Object.keys(dimensions).length > 0 ? dimensions : null,
        free_shipping: data.freeShipping,
        meta_title: data.metaTitle || null,
        meta_description: data.metaDescription || null,
        variants: data.variants?.map((v) => ({
            ...v,
            price: v.price != null ? Math.round(v.price * 100) : undefined,
            cost_price: v.costPrice != null ? Math.round(v.costPrice * 100) : undefined,
        })),
        variant_groups: data.variantGroups,
        attribute_values: data.attributes,
    };
}

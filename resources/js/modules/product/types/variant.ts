import { z } from 'zod';

export const variantGroupSchema = z.object({
    attributeId: z.string().min(1, 'Attribute is required'),
    attributeName: z.string(),
    values: z.array(z.string()).min(1, 'At least one value is required'),
});

export const variantRowSchema = z.object({
    id: z.string(),
    sku: z.string().optional(),
    barcode: z.string().optional(),
    price: z.coerce.number().positive('Price must be positive').optional(),
    costPrice: z.coerce.number().optional(),
    quantity: z.coerce.number().int().min(0).default(0),
    weight: z.coerce.number().optional(),
    isEnabled: z.boolean().default(true),
    image: z.any().optional(),
    attributes: z.record(z.string(), z.string()),
});

export const attributeSchema = z.object({
    id: z.string().min(1),
    value: z.string().min(1, 'Value is required'),
});

export type VariantGroupFormData = z.input<typeof variantGroupSchema>;
export type VariantRowFormData = z.input<typeof variantRowSchema>;
export type AttributeFormData = z.input<typeof attributeSchema>;

export type VariantGroup = {
    attributeId: string;
    attributeName: string;
    values: string[];
};

import { z } from 'zod';
import { variantRowSchema, attributeSchema } from './variant';

export const productFormSchema = z.object({
    name: z.string().min(1, 'Product name is required').max(255, 'Name is too long'),
    description: z.string().optional(),
    status: z.enum(['draft', 'active']),
    categoryId: z.string().nullable().optional(),
    brandId: z.string().nullable().optional(),

    price: z.coerce.number().positive('Price must be greater than 0'),
    comparePrice: z.coerce.number().optional().nullable(),
    costPrice: z.coerce.number().optional().nullable(),
    isTaxable: z.boolean().default(true),

    sku: z.string().min(1, 'SKU is required').max(100),
    barcode: z.string().optional().nullable(),
    trackStock: z.boolean().default(true),
    quantity: z.coerce.number().int().min(0).default(0),
    lowStockThreshold: z.coerce.number().int().min(0).default(5),
    allowBackorders: z.boolean().default(false),

    weight: z.coerce.number().optional().nullable(),
    length: z.coerce.number().optional().nullable(),
    width: z.coerce.number().optional().nullable(),
    height: z.coerce.number().optional().nullable(),
    freeShipping: z.boolean().default(false),

    metaTitle: z.string().max(70).optional().nullable(),
    metaDescription: z.string().max(320).optional().nullable(),
    slug: z.string().optional().nullable(),

    images: z.array(z.any()).optional().default([]),
    variants: z.array(variantRowSchema).optional().default([]),
    variantGroups: z
        .array(
            z.object({
                attributeId: z.string(),
                attributeName: z.string(),
                values: z.array(z.string()),
            }),
        )
        .optional()
        .default([]),
    attributes: z.array(attributeSchema).optional().default([]),
});

export type ProductFormData = z.input<typeof productFormSchema>;
export type ProductFormOutput = z.output<typeof productFormSchema>;

export const defaultProductFormValues: ProductFormData = {
    name: '',
    description: '',
    status: 'draft',
    categoryId: null,
    brandId: null,
    price: 0,
    comparePrice: null,
    costPrice: null,
    isTaxable: true,
    sku: '',
    barcode: null,
    trackStock: true,
    quantity: 0,
    lowStockThreshold: 5,
    allowBackorders: false,
    weight: null,
    length: null,
    width: null,
    height: null,
    freeShipping: false,
    metaTitle: null,
    metaDescription: null,
    slug: null,
    images: [],
    variants: [],
    variantGroups: [],
    attributes: [],
};

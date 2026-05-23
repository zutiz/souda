import { useMemo, useState } from 'react';
import { useWatch } from 'react-hook-form';
import { ProductGeneralSection } from './product-general-section';
import { ProductPricingSection } from './product-pricing-section';
import { ProductInventorySection } from './product-inventory-section';
import { ProductShippingSection } from './product-shipping-section';
import { ProductSeoSection } from './product-seo-section';
import { ProductImageUpload } from './product-image-upload';
import { VariantGrid } from './variant-grid';
import { AttributeManager } from './attribute-manager';
import { PageHeader } from '@/modules/shared/components/page-header';
import { FormActions } from '@/modules/shared/components/form-actions';
import { useProductForm } from '../hooks/use-product-form';
import type { ProductFormData } from '../types/product-form';
import type { VariantGroup, VariantRowFormData, AttributeFormData } from '../types/variant';

type Props = {
    mode: 'create' | 'edit';
    initialData?: Partial<ProductFormData>;
    submitRoute: string;
    method?: 'post' | 'put';
    categories: { id: string; name: string; parent_id: string | null }[];
    brands: { id: string; name: string }[];
    onCancel?: () => void;
    onSuccess?: () => void;
};

export function ProductFormPage({
    mode,
    initialData,
    submitRoute,
    method = 'post',
    categories,
    brands,
    onCancel,
    onSuccess,
}: Props) {
    const { form, errors, processing, onChange, handleSubmit } = useProductForm({
        initialData,
        route: submitRoute,
        method,
        onSuccess,
    });

    const images = useWatch({ control: form.control, name: 'images' });
    const variants = useWatch({ control: form.control, name: 'variants' });
    const variantGroups = useWatch({ control: form.control, name: 'variantGroups' });
    const attributes = useWatch({ control: form.control, name: 'attributes' });
    const sku = useWatch({ control: form.control, name: 'sku' });

    const [shippingOpen, setShippingOpen] = useState(false);
    const [seoOpen, setSeoOpen] = useState(false);

    const nestedOnChange = useMemo(
        () => ({
            general: <K extends keyof ProductFormData>(field: K, value: ProductFormData[K]) =>
                onChange(field, value),
            pricing: <K extends keyof ProductFormData>(field: K, value: ProductFormData[K]) =>
                onChange(field, value),
            inventory: <K extends keyof ProductFormData>(field: K, value: ProductFormData[K]) =>
                onChange(field, value),
            shipping: <K extends keyof ProductFormData>(field: K, value: ProductFormData[K]) =>
                onChange(field, value),
            seo: <K extends keyof ProductFormData>(field: K, value: ProductFormData[K]) =>
                onChange(field, value),
        }),
        [onChange],
    );

    return (
        <form onSubmit={handleSubmit} noValidate>
            <PageHeader
                title={mode === 'create' ? 'Create Product' : 'Edit Product'}
                description={mode === 'create' ? 'Add a new product to your catalog' : 'Update product details'}
            />

            <div className="mx-auto max-w-4xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
                <ProductGeneralSection
                    form={form}
                    categories={categories}
                    brands={brands}
                    errors={errors}
                    onChange={nestedOnChange.general}
                />

                <ProductPricingSection
                    form={form}
                    errors={errors}
                    onChange={nestedOnChange.pricing}
                />

                <ProductInventorySection
                    form={form}
                    errors={errors}
                    onChange={nestedOnChange.inventory}
                />

                <ProductImageUpload
                    images={images ?? []}
                    errors={errors}
                    onChange={onChange}
                />

                <VariantGrid
                    variants={(variants ?? []) as VariantRowFormData[]}
                    variantGroups={(variantGroups ?? []) as VariantGroup[]}
                    parentSku={sku}
                    productImages={images}
                    errors={errors}
                    onChange={onChange as (field: 'variants', value: VariantRowFormData[]) => void}
                    onGroupsChange={onChange as (field: 'variantGroups', value: VariantGroup[]) => void}
                />

                <AttributeManager
                    attributes={(attributes ?? []) as AttributeFormData[]}
                    errors={errors}
                    onChange={onChange as (field: 'attributes', value: AttributeFormData[]) => void}
                />

                <ProductShippingSection
                    form={form}
                    open={shippingOpen}
                    onToggle={() => setShippingOpen((p) => !p)}
                    errors={errors}
                    onChange={nestedOnChange.shipping}
                />

                <ProductSeoSection
                    form={form}
                    open={seoOpen}
                    onToggle={() => setSeoOpen((p) => !p)}
                    errors={errors}
                    onChange={nestedOnChange.seo}
                />
            </div>

            <FormActions
                onCancel={onCancel}
                submitLabel={mode === 'create' ? 'Create Product' : 'Save Changes'}
                processing={processing}
            />
        </form>
    );
}

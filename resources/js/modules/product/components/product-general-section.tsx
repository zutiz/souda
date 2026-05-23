import { useWatch, type UseFormReturn } from 'react-hook-form';
import { FormSection } from '@/modules/shared/components/form-section';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import { cn } from '@/lib/utils';
import type { ProductFormData } from '../types/product-form';

type Props = {
    form: UseFormReturn<ProductFormData>;
    categories: { id: string; name: string; parent_id: string | null }[];
    brands: { id: string; name: string }[];
    errors: Partial<Record<string, string>>;
    onChange: <K extends keyof ProductFormData>(field: K, value: ProductFormData[K]) => void;
};

export function ProductGeneralSection({ form, categories, brands, errors, onChange }: Props) {
    const name = useWatch({ control: form.control, name: 'name' }) as string;
    const status = useWatch({ control: form.control, name: 'status' }) as 'draft' | 'active';
    const description = useWatch({ control: form.control, name: 'description' }) as string | undefined;
    const categoryId = useWatch({ control: form.control, name: 'categoryId' }) as string | null | undefined;
    const brandId = useWatch({ control: form.control, name: 'brandId' }) as string | null | undefined;

    return (
        <FormSection title="General Information" description="Basic product details">
            <div className="grid gap-5 sm:grid-cols-2">
                <FieldGroup>
                    <Label htmlFor="name">Product Name</Label>
                    <Input
                        id="name"
                        value={name}
                        onChange={(e) => onChange('name', e.target.value)}
                        placeholder="Enter product name"
                    />
                    <FieldError error={errors.name} />
                </FieldGroup>

                <FieldGroup>
                    <Label htmlFor="status">Status</Label>
                    <select
                        id="status"
                        value={status}
                        onChange={(e) => onChange('status', e.target.value as 'draft' | 'active')}
                        className="border-input flex h-9 w-full rounded-md border bg-transparent px-3 py-1 text-sm shadow-xs"
                    >
                        <option value="draft">Draft</option>
                        <option value="active">Active</option>
                    </select>
                    <FieldError error={errors.status} />
                </FieldGroup>
            </div>

            <FieldGroup>
                <Label htmlFor="description">Description</Label>
                <Textarea
                    id="description"
                    value={description ?? ''}
                    onChange={(e) => onChange('description', e.target.value)}
                    placeholder="Enter product description"
                    rows={4}
                />
                <FieldError error={errors.description} />
            </FieldGroup>

            <div className="grid gap-5 sm:grid-cols-2">
                <FieldGroup>
                    <Label htmlFor="categoryId">Category</Label>
                    <select
                        id="categoryId"
                        value={categoryId ?? ''}
                        onChange={(e) => onChange('categoryId', e.target.value || null)}
                        className="border-input flex h-9 w-full rounded-md border bg-transparent px-3 py-1 text-sm shadow-xs"
                    >
                        <option value="">No category</option>
                        {categories.map((cat) => (
                            <option key={cat.id} value={cat.id}>
                                {cat.parent_id ? '  ─ ' : ''}{cat.name}
                            </option>
                        ))}
                    </select>
                    <FieldError error={errors.categoryId} />
                </FieldGroup>

                <FieldGroup>
                    <Label htmlFor="brandId">Brand</Label>
                    <select
                        id="brandId"
                        value={brandId ?? ''}
                        onChange={(e) => onChange('brandId', e.target.value || null)}
                        className="border-input flex h-9 w-full rounded-md border bg-transparent px-3 py-1 text-sm shadow-xs"
                    >
                        <option value="">No brand</option>
                        {brands.map((brand) => (
                            <option key={brand.id} value={brand.id}>
                                {brand.name}
                            </option>
                        ))}
                    </select>
                    <FieldError error={errors.brandId} />
                </FieldGroup>
            </div>
        </FormSection>
    );
}

function FieldGroup({ children, className }: { children: React.ReactNode; className?: string }) {
    return <div className={cn('space-y-2', className)}>{children}</div>;
}

function FieldError({ error }: { error?: string }) {
    if (!error) return null;
    return <p className="text-destructive text-xs">{error}</p>;
}

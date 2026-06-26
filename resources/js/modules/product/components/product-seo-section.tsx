import { useWatch, type UseFormReturn } from 'react-hook-form';
import { FormSectionCollapsible } from '@/modules/shared/components/form-section';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import { cn } from '@/lib/utils';
import type { ProductFormData } from '../types/product-form';

type Props = {
    form: UseFormReturn<ProductFormData>;
    open: boolean;
    onToggle: () => void;
    errors: Partial<Record<string, string>>;
    onChange: <K extends keyof ProductFormData>(field: K, value: ProductFormData[K]) => void;
};

export function ProductSeoSection({ form, open, onToggle, errors, onChange }: Props) {
    const name = useWatch({ control: form.control, name: 'name' }) as string;
    const metaTitle = useWatch({ control: form.control, name: 'metaTitle' }) as string | null;
    const slug = useWatch({ control: form.control, name: 'slug' }) as string | null;
    const metaDescription = useWatch({ control: form.control, name: 'metaDescription' }) as string | null;

    return (
        <FormSectionCollapsible
            title="SEO"
            description="Search engine optimization settings"
            open={open}
            onToggle={onToggle}
        >
            <FieldGroup>
                <Label htmlFor="slug">URL Slug</Label>
                <Input
                    id="slug"
                    value={slug ?? ''}
                    onChange={(e) => onChange('slug', e.target.value || null)}
                    placeholder={name ? name.toLowerCase().replace(/\s+/g, '-').replace(/[^a-z0-9-]/g, '') : 'auto-generated from name'}
                />
                {!slug && name && (
                    <p className="text-muted-foreground text-xs">
                        Auto: /products/{name.toLowerCase().replace(/\s+/g, '-').replace(/[^a-z0-9-]/g, '')}
                    </p>
                )}
            </FieldGroup>

            <FieldGroup>
                <Label htmlFor="metaTitle">Meta Title</Label>
                <Input
                    id="metaTitle"
                    value={metaTitle ?? ''}
                    onChange={(e) => onChange('metaTitle', e.target.value || null)}
                    placeholder={name || 'Product name used as fallback'}
                    maxLength={70}
                />
                <div className="flex justify-between">
                    <FieldError error={errors.metaTitle} />
                    <span className="text-muted-foreground text-xs">{70 - (metaTitle ?? name ?? '').length} chars left</span>
                </div>
            </FieldGroup>

            <FieldGroup>
                <Label htmlFor="metaDescription">Meta Description</Label>
                <Textarea
                    id="metaDescription"
                    value={metaDescription ?? ''}
                    onChange={(e) => onChange('metaDescription', e.target.value || null)}
                    placeholder="Brief description for search results"
                    rows={2}
                    maxLength={320}
                />
                <div className="flex justify-between">
                    <FieldError error={errors.metaDescription} />
                    <span className="text-muted-foreground text-xs">
                        {320 - (metaDescription ?? '').length} chars left
                    </span>
                </div>
            </FieldGroup>
        </FormSectionCollapsible>
    );
}

function FieldGroup({ children, className }: { children: React.ReactNode; className?: string }) {
    return <div className={cn('space-y-2', className)}>{children}</div>;
}

function FieldError({ error }: { error?: string }) {
    if (!error) return null;
    return <p className="text-destructive text-xs">{error}</p>;
}

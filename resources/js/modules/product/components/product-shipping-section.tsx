import { useWatch, type UseFormReturn } from 'react-hook-form';
import { FormSectionCollapsible } from '@/modules/shared/components/form-section';
import { Input } from '@/components/ui/input';
import { Switch } from '@/components/ui/switch';
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

export function ProductShippingSection({ form, open, onToggle, errors, onChange }: Props) {
    const weight = useWatch({ control: form.control, name: 'weight' }) as number | null;
    const length = useWatch({ control: form.control, name: 'length' }) as number | null;
    const width = useWatch({ control: form.control, name: 'width' }) as number | null;
    const height = useWatch({ control: form.control, name: 'height' }) as number | null;
    const freeShipping = useWatch({ control: form.control, name: 'freeShipping' }) as boolean;

    return (
        <FormSectionCollapsible
            title="Shipping"
            description="Weight, dimensions, and shipping preferences"
            open={open}
            onToggle={onToggle}
        >
            <div className="grid gap-5 sm:grid-cols-4">
                <FieldGroup>
                    <Label htmlFor="weight">Weight (kg)</Label>
                    <Input
                        id="weight"
                        type="number"
                        step="0.01"
                        min="0"
                        value={weight ?? ''}
                        onChange={(e) => onChange('weight', e.target.valueAsNumber || null)}
                    />
                    <FieldError error={errors.weight} />
                </FieldGroup>
                <FieldGroup>
                    <Label htmlFor="length">Length (cm)</Label>
                    <Input
                        id="length"
                        type="number"
                        step="0.1"
                        min="0"
                        value={length ?? ''}
                        onChange={(e) => onChange('length', e.target.valueAsNumber || null)}
                    />
                </FieldGroup>
                <FieldGroup>
                    <Label htmlFor="width">Width (cm)</Label>
                    <Input
                        id="width"
                        type="number"
                        step="0.1"
                        min="0"
                        value={width ?? ''}
                        onChange={(e) => onChange('width', e.target.valueAsNumber || null)}
                    />
                </FieldGroup>
                <FieldGroup>
                    <Label htmlFor="height">Height (cm)</Label>
                    <Input
                        id="height"
                        type="number"
                        step="0.1"
                        min="0"
                        value={height ?? ''}
                        onChange={(e) => onChange('height', e.target.valueAsNumber || null)}
                    />
                </FieldGroup>
            </div>

            <div className="flex items-center gap-3">
                <Switch
                    id="freeShipping"
                    checked={freeShipping}
                    onCheckedChange={(checked) => onChange('freeShipping', checked)}
                />
                <Label htmlFor="freeShipping" className="cursor-pointer">Free shipping</Label>
            </div>
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

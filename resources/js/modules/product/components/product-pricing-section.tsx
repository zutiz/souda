import { useWatch, type UseFormReturn } from 'react-hook-form';
import { FormSection } from '@/modules/shared/components/form-section';
import { Input } from '@/components/ui/input';
import { Switch } from '@/components/ui/switch';
import { Label } from '@/components/ui/label';
import { cn } from '@/lib/utils';
import type { ProductFormData } from '../types/product-form';

type Props = {
    form: UseFormReturn<ProductFormData>;
    errors: Partial<Record<string, string>>;
    onChange: <K extends keyof ProductFormData>(field: K, value: ProductFormData[K]) => void;
};

export function ProductPricingSection({ form, errors, onChange }: Props) {
    const price = useWatch({ control: form.control, name: 'price' }) as number;
    const comparePrice = useWatch({ control: form.control, name: 'comparePrice' }) as number | null;
    const costPrice = useWatch({ control: form.control, name: 'costPrice' }) as number | null;
    const isTaxable = useWatch({ control: form.control, name: 'isTaxable' }) as boolean;

    const hasDiscount = comparePrice != null && price != null && comparePrice > price;

    return (
        <FormSection title="Pricing" description="Set product pricing and tax configuration">
            <div className="grid gap-5 sm:grid-cols-3">
                <FieldGroup>
                    <Label htmlFor="price">Price *</Label>
                    <div className="relative">
                        <span className="text-muted-foreground absolute top-1/2 left-3 -translate-y-1/2 text-sm">$</span>
                        <Input
                            id="price"
                            type="number"
                            step="0.01"
                            min="0"
                            value={price ?? ''}
                            onChange={(e) => onChange('price', e.target.valueAsNumber || 0)}
                            className="pl-7"
                        />
                    </div>
                    <FieldError error={errors.price} />
                </FieldGroup>

                <FieldGroup>
                    <Label htmlFor="comparePrice">Compare Price</Label>
                    <div className="relative">
                        <span className="text-muted-foreground absolute top-1/2 left-3 -translate-y-1/2 text-sm">$</span>
                        <Input
                            id="comparePrice"
                            type="number"
                            step="0.01"
                            min="0"
                            value={comparePrice ?? ''}
                            onChange={(e) => onChange('comparePrice', e.target.valueAsNumber || null)}
                            className="pl-7"
                        />
                    </div>
                    {hasDiscount && (
                        <p className="text-positive text-xs">
                            {Math.round((1 - price! / comparePrice!) * 100)}% off
                        </p>
                    )}
                    <FieldError error={errors.comparePrice} />
                </FieldGroup>

                <FieldGroup>
                    <Label htmlFor="costPrice">Cost Price</Label>
                    <div className="relative">
                        <span className="text-muted-foreground absolute top-1/2 left-3 -translate-y-1/2 text-sm">$</span>
                        <Input
                            id="costPrice"
                            type="number"
                            step="0.01"
                            min="0"
                            value={costPrice ?? ''}
                            onChange={(e) => onChange('costPrice', e.target.valueAsNumber || null)}
                            className="pl-7"
                        />
                    </div>
                    {price != null && price > 0 && costPrice != null && (
                        <p className="text-muted-foreground text-xs">
                            Margin: {formatMargin(price, costPrice)}
                        </p>
                    )}
                    <FieldError error={errors.costPrice} />
                </FieldGroup>
            </div>

            <div className="flex items-center gap-3">
                <Switch
                    id="isTaxable"
                    checked={isTaxable}
                    onCheckedChange={(checked) => onChange('isTaxable', checked)}
                />
                <Label htmlFor="isTaxable" className="cursor-pointer">Charge tax on this product</Label>
            </div>
        </FormSection>
    );
}

function formatMargin(price: number, cost: number): string {
    const margin = ((price - cost) / price) * 100;
    return `${Math.round(margin)}%`;
}

function FieldGroup({ children, className }: { children: React.ReactNode; className?: string }) {
    return <div className={cn('space-y-2', className)}>{children}</div>;
}

function FieldError({ error }: { error?: string }) {
    if (!error) return null;
    return <p className="text-destructive text-xs">{error}</p>;
}

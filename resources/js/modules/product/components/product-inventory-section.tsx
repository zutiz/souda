import type { UseFormReturn } from 'react-hook-form';
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
    onGenerateSku?: () => void;
};

export function ProductInventorySection({ form, errors, onChange, onGenerateSku }: Props) {
    const trackStock = form.watch('trackStock');
    const sku = form.watch('sku');

    return (
        <FormSection title="Inventory" description="SKU, barcode, and stock tracking">
            <div className="grid gap-5 sm:grid-cols-2">
                <FieldGroup>
                    <Label htmlFor="sku">SKU *</Label>
                    <div className="flex gap-2">
                        <Input
                            id="sku"
                            value={sku}
                            onChange={(e) => onChange('sku', e.target.value)}
                            placeholder="e.g. TSH-001-BLK"
                            className="flex-1"
                        />
                        {onGenerateSku && (
                            <button
                                type="button"
                                onClick={onGenerateSku}
                                className="text-muted-foreground hover:text-foreground shrink-0 px-2 text-xs underline-offset-2 hover:underline"
                            >
                                Generate
                            </button>
                        )}
                    </div>
                    <FieldError error={errors.sku} />
                </FieldGroup>

                <FieldGroup>
                    <Label htmlFor="barcode">Barcode</Label>
                    <Input
                        id="barcode"
                        value={form.watch('barcode') ?? ''}
                        onChange={(e) => onChange('barcode', e.target.value || null)}
                        placeholder="UPC, EAN, or ISBN"
                    />
                    <FieldError error={errors.barcode} />
                </FieldGroup>
            </div>

            <div className="flex items-center gap-3">
                <Switch
                    id="trackStock"
                    checked={trackStock}
                    onCheckedChange={(checked) => onChange('trackStock', checked)}
                />
                <Label htmlFor="trackStock" className="cursor-pointer">Track stock quantity</Label>
            </div>

            {trackStock && (
                <div className="grid gap-5 sm:grid-cols-3">
                    <FieldGroup>
                        <Label htmlFor="quantity">Quantity</Label>
                        <Input
                            id="quantity"
                            type="number"
                            min="0"
                            value={form.watch('quantity')}
                            onChange={(e) => onChange('quantity', e.target.valueAsNumber || 0)}
                        />
                        <FieldError error={errors.quantity} />
                    </FieldGroup>

                    <FieldGroup>
                        <Label htmlFor="lowStockThreshold">Low Stock Threshold</Label>
                        <Input
                            id="lowStockThreshold"
                            type="number"
                            min="0"
                            value={form.watch('lowStockThreshold')}
                            onChange={(e) => onChange('lowStockThreshold', e.target.valueAsNumber || 0)}
                        />
                        <FieldError error={errors.lowStockThreshold} />
                    </FieldGroup>

                    <FieldGroup>
                        <Label htmlFor="allowBackorders">Allow Backorders</Label>
                        <div className="flex h-9 items-center">
                            <Switch
                                id="allowBackorders"
                                checked={form.watch('allowBackorders')}
                                onCheckedChange={(checked) => onChange('allowBackorders', checked)}
                            />
                            <Label htmlFor="allowBackorders" className="ml-3 cursor-pointer text-sm">
                                {form.watch('allowBackorders') ? 'Allowed' : 'Not allowed'}
                            </Label>
                        </div>
                    </FieldGroup>
                </div>
            )}
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

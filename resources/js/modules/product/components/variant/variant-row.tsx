import { memo, useCallback } from 'react';
import { Trash2Icon, CopyIcon } from 'lucide-react';
import { Input } from '@/components/ui/input';
import { Switch } from '@/components/ui/switch';
import { Checkbox } from '@/components/ui/checkbox';
import { Button } from '@/components/ui/button';
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip';
import { VariantImageCell } from './variant-image-cell';
import type { VariantRowFormData } from '../../types/variant';
import { cn } from '@/lib/utils';

type Props = {
    variant: VariantRowFormData;
    index: number;
    attributeKeys: string[];
    isSelected: boolean;
    hasDuplicateSku: boolean;
    hasDuplicateBarcode: boolean;
    productImages?: any[];
    onToggleSelect: () => void;
    onUpdate: (field: keyof VariantRowFormData, value: unknown) => void;
    onRemove: () => void;
    onDuplicate: () => void;
    onToggleEnabled: () => void;
    style?: React.CSSProperties;
};

export const VariantRow = memo(function VariantRow({
    variant,
    index,
    attributeKeys,
    isSelected,
    hasDuplicateSku,
    hasDuplicateBarcode,
    onToggleSelect,
    onUpdate,
    onRemove,
    onDuplicate,
    onToggleEnabled,
    style,
}: Props) {
    return (
        <tr
            className={cn(
                'border-b text-xs transition-colors last:border-b-0 hover:bg-muted/30',
                !variant.isEnabled && 'opacity-50',
            )}
            style={style}
        >
            <td className="w-10 px-2 py-1.5 text-center">
                <Checkbox
                    checked={isSelected}
                    onCheckedChange={onToggleSelect}
                />
            </td>

            <td className="px-2 py-1.5">
                <VariantImageCell
                    image={variant.image}
                    onImageChange={(file) => onUpdate('image', file)}
                    productImages={productImages}
                />
            </td>

            {attributeKeys.map((key) => (
                <td
                    key={key}
                    className="text-muted-foreground max-w-[100px] truncate px-2 py-1.5 font-medium"
                >
                    {variant.attributes[key] ?? '—'}
                </td>
            ))}

            <td className="px-1.5 py-1.5">
                <Tooltip>
                    <TooltipTrigger asChild>
                        <Input
                            value={variant.sku ?? ''}
                            onChange={(e) => onUpdate('sku', e.target.value)}
                            placeholder="SKU"
                            className={cn(
                                'h-7 w-24 text-[11px]',
                                hasDuplicateSku && 'ring-destructive/50 ring-2',
                            )}
                        />
                    </TooltipTrigger>
                    {hasDuplicateSku && (
                        <TooltipContent side="bottom">
                            Duplicate SKU detected
                        </TooltipContent>
                    )}
                </Tooltip>
            </td>

            <td className="px-1.5 py-1.5">
                <Tooltip>
                    <TooltipTrigger asChild>
                        <Input
                            value={variant.barcode ?? ''}
                            onChange={(e) => onUpdate('barcode', e.target.value || null)}
                            placeholder="Barcode"
                            className={cn(
                                'h-7 w-24 text-[11px]',
                                hasDuplicateBarcode && 'ring-destructive/50 ring-2',
                            )}
                        />
                    </TooltipTrigger>
                    {hasDuplicateBarcode && (
                        <TooltipContent side="bottom">
                            Duplicate barcode detected
                        </TooltipContent>
                    )}
                </Tooltip>
            </td>

            <td className="px-1.5 py-1.5">
                <Input
                    type="number"
                    step="0.01"
                    min="0"
                    value={variant.price ?? '' as any}
                    onChange={(e) =>
                        onUpdate('price', e.target.valueAsNumber || undefined)
                    }
                    placeholder="0.00"
                    className="h-7 w-20 text-[11px]"
                />
            </td>

            <td className="px-1.5 py-1.5">
                <Input
                    type="number"
                    step="0.01"
                    min="0"
                    value={variant.costPrice ?? '' as any}
                    onChange={(e) =>
                        onUpdate('costPrice', e.target.valueAsNumber || undefined)
                    }
                    placeholder="0.00"
                    className="h-7 w-20 text-[11px]"
                />
            </td>

            <td className="px-1.5 py-1.5">
                <Input
                    type="number"
                    min="0"
                    value={variant.quantity as any}
                    onChange={(e) =>
                        onUpdate('quantity', e.target.valueAsNumber || 0)
                    }
                    className="h-7 w-16 text-[11px]"
                />
            </td>

            <td className="px-1.5 py-1.5">
                <Input
                    type="number"
                    step="0.1"
                    min="0"
                    value={variant.weight ?? '' as any}
                    onChange={(e) =>
                        onUpdate('weight', e.target.valueAsNumber || undefined)
                    }
                    placeholder="0"
                    className="h-7 w-16 text-[11px]"
                />
            </td>

            <td className="px-2 py-1.5 text-center">
                <Switch
                    checked={variant.isEnabled}
                    onCheckedChange={onToggleEnabled}
                    className="scale-75"
                />
            </td>

            <td className="px-1 py-1.5">
                <div className="flex items-center gap-0.5">
                    <Button
                        type="button"
                        variant="ghost"
                        size="icon"
                        className="text-muted-foreground hover:text-foreground size-6"
                        onClick={onDuplicate}
                        title="Duplicate"
                    >
                        <CopyIcon className="size-3" />
                    </Button>
                    <Button
                        type="button"
                        variant="ghost"
                        size="icon"
                        className="text-destructive size-6"
                        onClick={onRemove}
                        title="Remove"
                    >
                        <Trash2Icon className="size-3" />
                    </Button>
                </div>
            </td>
        </tr>
    );
});

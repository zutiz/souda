import { useCallback } from 'react';
import { PlusIcon, Trash2Icon } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { FormSection } from '@/modules/shared/components/form-section';
import type { AttributeFormData } from '../types/variant';

type Props = {
    attributes: AttributeFormData[];
    errors: Partial<Record<string, string>>;
    onChange: (field: 'attributes', value: AttributeFormData[]) => void;
};

export function AttributeManager({ attributes, errors, onChange }: Props) {
    const updateAttribute = useCallback(
        (index: number, field: keyof AttributeFormData, value: string) => {
            const updated = attributes.map((attr, i) =>
                i === index ? { ...attr, [field]: value } : attr,
            );
            onChange('attributes', updated);
        },
        [attributes, onChange],
    );

    const addAttribute = useCallback(() => {
        onChange('attributes', [
            ...attributes,
            { id: crypto.randomUUID(), value: '' },
        ]);
    }, [attributes, onChange]);

    const removeAttribute = useCallback(
        (index: number) => {
            onChange(
                'attributes',
                attributes.filter((_, i) => i !== index),
            );
        },
        [attributes, onChange],
    );

    return (
        <FormSection title="Attributes" description="Additional product attributes (e.g. material, color code)">
            <div className="space-y-3">
                {attributes.map((attr, i) => (
                    <div key={attr.id} className="flex items-start gap-2">
                        <div className="grid flex-1 grid-cols-2 gap-2">
                            <div>
                                <Label htmlFor={`attr-key-${i}`} className="sr-only">
                                    Attribute name
                                </Label>
                                <Input
                                    id={`attr-key-${i}`}
                                    value={attr.id === attr.value ? '' : attr.id}
                                    onChange={(e) => updateAttribute(i, 'id', e.target.value)}
                                    placeholder="Name (e.g. Material)"
                                />
                            </div>
                            <div>
                                <Label htmlFor={`attr-value-${i}`} className="sr-only">
                                    Attribute value
                                </Label>
                                <Input
                                    id={`attr-value-${i}`}
                                    value={attr.value}
                                    onChange={(e) => updateAttribute(i, 'value', e.target.value)}
                                    placeholder="Value (e.g. Cotton)"
                                />
                            </div>
                        </div>
                        <Button
                            type="button"
                            variant="ghost"
                            size="icon"
                            className="text-destructive mt-0.5 size-9 shrink-0"
                            onClick={() => removeAttribute(i)}
                        >
                            <Trash2Icon className="size-4" />
                        </Button>
                    </div>
                ))}

                <Button type="button" variant="outline" size="sm" onClick={addAttribute}>
                    <PlusIcon className="mr-1 size-3.5" />
                    Add Attribute
                </Button>

                {errors.attributes && (
                    <p className="text-destructive text-xs">{errors.attributes}</p>
                )}
            </div>
        </FormSection>
    );
}

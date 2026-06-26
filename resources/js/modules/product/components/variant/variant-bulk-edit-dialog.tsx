import { useState, useCallback } from 'react';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
    DialogFooter,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import type { BulkEditField, BulkEditOperation } from '../../types/variant-bulk-edit';

type EditableField = {
    field: BulkEditField;
    label: string;
    type: 'number' | 'text' | 'boolean';
};

const EDITABLE_FIELDS: EditableField[] = [
    { field: 'price', label: 'Price', type: 'number' },
    { field: 'costPrice', label: 'Cost Price', type: 'number' },
    { field: 'quantity', label: 'Quantity', type: 'number' },
    { field: 'weight', label: 'Weight', type: 'number' },
    { field: 'sku', label: 'SKU', type: 'text' },
    { field: 'isEnabled', label: 'Enabled', type: 'boolean' },
];

type Props = {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    onApply: (operations: BulkEditOperation[]) => void;
};

export function VariantBulkEditDialog({ open, onOpenChange, onApply }: Props) {
    const [selectedField, setSelectedField] = useState<BulkEditField>('price');
    const [operation, setOperation] = useState<BulkEditOperation['operation']>('set');
    const [value, setValue] = useState<string>('');

    const handleApply = useCallback(() => {
        const parsedValue = selectedField === 'isEnabled'
            ? value === 'true'
            : selectedField === 'sku'
                ? value
                : Number(value);

        const operations: BulkEditOperation[] = [
            { field: selectedField, operation, value: parsedValue },
        ];

        onApply(operations);
        onOpenChange(false);
        setSelectedField('price');
        setOperation('set');
        setValue('');
    }, [selectedField, operation, value, onApply, onOpenChange]);

    const showNumericOps = EDITABLE_FIELDS.find((f) => f.field === selectedField)?.type === 'number';

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="sm:max-w-sm">
                <DialogHeader>
                    <DialogTitle>Bulk Edit Variants</DialogTitle>
                    <DialogDescription>
                        Apply changes to all {selectedField ? 'selected' : ''} variants
                    </DialogDescription>
                </DialogHeader>

                <div className="space-y-4">
                    <div className="space-y-2">
                        <Label>Field</Label>
                        <Select
                            value={selectedField}
                            onValueChange={(v) => {
                                setSelectedField(v as BulkEditField);
                                setOperation('set');
                                setValue('');
                            }}
                        >
                            <SelectTrigger>
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                {EDITABLE_FIELDS.map((f) => (
                                    <SelectItem key={f.field} value={f.field}>
                                        {f.label}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </div>

                    {showNumericOps && (
                        <div className="space-y-2">
                            <Label>Operation</Label>
                            <Select
                                value={operation}
                                onValueChange={(v) => setOperation(v as BulkEditOperation['operation'])}
                            >
                                <SelectTrigger>
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="set">Set to</SelectItem>
                                    <SelectItem value="add">Add</SelectItem>
                                    <SelectItem value="subtract">Subtract</SelectItem>
                                    <SelectItem value="multiply">Multiply by</SelectItem>
                                    <SelectItem value="percentage">Add %</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    )}

                    {selectedField !== 'isEnabled' && (
                        <div className="space-y-2">
                            <Label>Value</Label>
                            {selectedField === 'sku' ? (
                                <Input
                                    value={value}
                                    onChange={(e) => setValue(e.target.value)}
                                    placeholder="New SKU value"
                                />
                            ) : (
                                <Input
                                    type="number"
                                    step="0.01"
                                    value={value}
                                    onChange={(e) => setValue(e.target.value)}
                                    placeholder="0"
                                />
                            )}
                        </div>
                    )}

                    {selectedField === 'isEnabled' && (
                        <div className="flex items-center gap-3">
                            <Switch
                                id="bulk-enabled"
                                checked={value === 'true'}
                                onCheckedChange={(checked) => setValue(checked ? 'true' : 'false')}
                            />
                            <Label htmlFor="bulk-enabled" className="cursor-pointer">
                                {value === 'true' ? 'Enabled' : 'Disabled'}
                            </Label>
                        </div>
                    )}
                </div>

                <DialogFooter>
                    <Button type="button" variant="outline" onClick={() => onOpenChange(false)}>
                        Cancel
                    </Button>
                    <Button type="button" onClick={handleApply} disabled={!value && selectedField !== 'isEnabled'}>
                        Apply
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}

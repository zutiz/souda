import { useState, useCallback } from 'react';
import { PlusIcon, Trash2Icon, XIcon } from 'lucide-react';
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
import { cn } from '@/lib/utils';
import type { VariantGroup } from '../types/variant';

type Props = {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    onGenerate: (groups: VariantGroup[]) => void;
    existingGroups: VariantGroup[];
};

export function VariantGeneratorDialog({ open, onOpenChange, onGenerate, existingGroups }: Props) {
    const [groups, setGroups] = useState<VariantGroup[]>(
        existingGroups.length > 0
            ? existingGroups
            : [{ attributeId: crypto.randomUUID(), attributeName: '', values: [''] }],
    );

    const addGroup = useCallback(() => {
        setGroups((prev) => [
            ...prev,
            { attributeId: crypto.randomUUID(), attributeName: '', values: [''] },
        ]);
    }, []);

    const removeGroup = useCallback((index: number) => {
        setGroups((prev) => prev.filter((_, i) => i !== index));
    }, []);

    const updateGroupName = useCallback((index: number, name: string) => {
        setGroups((prev) =>
            prev.map((g, i) => (i === index ? { ...g, attributeName: name } : g)),
        );
    }, []);

    const updateGroupValue = useCallback(
        (groupIndex: number, valueIndex: number, value: string) => {
            setGroups((prev) =>
                prev.map((g, i) =>
                    i === groupIndex
                        ? {
                              ...g,
                              values: g.values.map((v, j) => (j === valueIndex ? value : v)),
                          }
                        : g,
                ),
            );
        },
        [],
    );

    const addValue = useCallback((groupIndex: number) => {
        setGroups((prev) =>
            prev.map((g, i) =>
                i === groupIndex ? { ...g, values: [...g.values, ''] } : g,
            ),
        );
    }, []);

    const removeValue = useCallback((groupIndex: number, valueIndex: number) => {
        setGroups((prev) =>
            prev.map((g, i) =>
                i === groupIndex
                    ? { ...g, values: g.values.filter((_, j) => j !== valueIndex) }
                    : g,
            ),
        );
    }, []);

    const handleGenerate = useCallback(() => {
        const validGroups = groups.filter(
            (g) => g.attributeName.trim().length > 0 && g.values.some((v) => v.trim().length > 0),
        );

        if (validGroups.length === 0) return;

        const cleaned = validGroups.map((g) => ({
            ...g,
            values: g.values.filter((v) => v.trim().length > 0),
        }));

        onGenerate(cleaned);
        onOpenChange(false);
    }, [groups, onGenerate, onOpenChange]);

    const handleClose = useCallback(() => {
        setGroups(
            existingGroups.length > 0
                ? existingGroups
                : [{ attributeId: crypto.randomUUID(), attributeName: '', values: [''] }],
        );
        onOpenChange(false);
    }, [onOpenChange, existingGroups]);

    const variantCount = groups
        .filter((g) => g.attributeName.trim().length > 0)
        .reduce((count, g) => count * g.values.filter((v) => v.trim().length > 0).length, 1);

    return (
        <Dialog open={open} onOpenChange={handleClose}>
            <DialogContent className="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>Generate Variants</DialogTitle>
                    <DialogDescription>
                        Define attribute groups and their values. Variants will be created for every
                        combination.
                    </DialogDescription>
                </DialogHeader>

                <div className="space-y-4">
                    {groups.map((group, gi) => (
                        <div key={group.attributeId} className="rounded-lg border p-4">
                            <div className="mb-3 flex items-center justify-between">
                                <Label>Attribute {gi + 1}</Label>
                                {groups.length > 1 && (
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="sm"
                                        onClick={() => removeGroup(gi)}
                                        className="text-destructive size-6"
                                    >
                                        <Trash2Icon className="size-3.5" />
                                    </Button>
                                )}
                            </div>

                            <div className="space-y-2">
                                <Input
                                    placeholder="e.g. Size, Color, Material"
                                    value={group.attributeName}
                                    onChange={(e) => updateGroupName(gi, e.target.value)}
                                />

                                <Label className="text-muted-foreground text-xs">Values</Label>
                                {group.values.map((value, vi) => (
                                    <div key={vi} className="flex items-center gap-2">
                                        <Input
                                            placeholder={`Value ${vi + 1}`}
                                            value={value}
                                            onChange={(e) =>
                                                updateGroupValue(gi, vi, e.target.value)
                                            }
                                        />
                                        {group.values.length > 1 && (
                                            <Button
                                                type="button"
                                                variant="ghost"
                                                size="icon"
                                                className="size-8 shrink-0"
                                                onClick={() => removeValue(gi, vi)}
                                            >
                                                <XIcon className="size-3.5" />
                                            </Button>
                                        )}
                                    </div>
                                ))}

                                <Button
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    onClick={() => addValue(gi)}
                                >
                                    <PlusIcon className="mr-1 size-3.5" />
                                    Add value
                                </Button>
                            </div>
                        </div>
                    ))}

                    <Button type="button" variant="outline" onClick={addGroup} className="w-full">
                        <PlusIcon className="mr-1 size-4" />
                        Add attribute group
                    </Button>
                </div>

                <DialogFooter className="flex items-center justify-between sm:justify-between">
                    <p className="text-muted-foreground text-sm">
                        {variantCount > 0 && `~${variantCount} variant${variantCount !== 1 ? 's' : ''} will be generated`}
                    </p>
                    <div className="flex gap-2">
                        <Button type="button" variant="outline" onClick={handleClose}>
                            Cancel
                        </Button>
                        <Button type="button" onClick={handleGenerate}>
                            Generate
                        </Button>
                    </div>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}

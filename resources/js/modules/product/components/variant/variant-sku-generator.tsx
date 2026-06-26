import { useState, useCallback } from 'react';
import { Settings2Icon } from 'lucide-react';
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
import type { SkuGenerationConfig } from '../../types/variant-sku';
import type { VariantGroup } from '../../types/variant';

type Props = {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    onGenerate: (config: SkuGenerationConfig) => void;
    groups: VariantGroup[];
    parentSku?: string;
};

export function VariantSkuGenerator({ open, onOpenChange, onGenerate, groups, parentSku }: Props) {
    const [strategy, setStrategy] = useState<SkuGenerationConfig['strategy']>('pattern');
    const [patternConfig, setPatternConfig] = useState({
        separator: '-',
        includeProductSku: true,
        attributeOrder: groups.map((g) => g.attributeName),
    });
    const [sequentialConfig, setSequentialConfig] = useState({
        prefix: parentSku ?? 'SKU',
        padding: 4,
        startFrom: 1,
    });
    const [attributeConfig, setAttributeConfig] = useState({
        separator: '-',
        attributeOrder: groups.map((g) => g.attributeName),
        maxLength: 6,
    });
    const [customConfig, setCustomConfig] = useState({
        template: `{sku}-{Color}-{Size}`,
    });

    const handleGenerate = useCallback(() => {
        let config: SkuGenerationConfig;

        switch (strategy) {
            case 'pattern':
                config = {
                    strategy: 'pattern',
                    ...patternConfig,
                };
                break;
            case 'sequential':
                config = {
                    strategy: 'sequential',
                    ...sequentialConfig,
                };
                break;
            case 'attribute':
                config = {
                    strategy: 'attribute',
                    ...attributeConfig,
                };
                break;
            case 'custom':
                config = {
                    strategy: 'custom',
                    ...customConfig,
                };
                break;
        }

        onGenerate(config);
        onOpenChange(false);
    }, [strategy, patternConfig, sequentialConfig, attributeConfig, customConfig, onGenerate, onOpenChange]);

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Generate SKUs</DialogTitle>
                    <DialogDescription>
                        Auto-generate SKUs for all variants based on a strategy
                    </DialogDescription>
                </DialogHeader>

                <div className="space-y-4">
                    <div className="space-y-2">
                        <Label>Strategy</Label>
                        <Select value={strategy} onValueChange={(v) => setStrategy(v as SkuGenerationConfig['strategy'])}>
                            <SelectTrigger>
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="pattern">Pattern (abbreviated attributes)</SelectItem>
                                <SelectItem value="sequential">Sequential numbering</SelectItem>
                                <SelectItem value="attribute">Full attribute values</SelectItem>
                                <SelectItem value="custom">Custom template</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    {strategy === 'pattern' && (
                        <div className="space-y-3">
                            <Label>Separator</Label>
                            <Input
                                value={patternConfig.separator}
                                onChange={(e) =>
                                    setPatternConfig((p) => ({ ...p, separator: e.target.value }))
                                }
                                maxLength={2}
                                className="w-16"
                            />
                            <div className="flex items-center gap-2">
                                <Switch
                                    id="includeProductSku"
                                    checked={patternConfig.includeProductSku}
                                    onCheckedChange={(checked) =>
                                        setPatternConfig((p) => ({ ...p, includeProductSku: checked }))
                                    }
                                />
                                <Label htmlFor="includeProductSku" className="cursor-pointer text-sm">
                                    Include parent SKU as prefix
                                </Label>
                            </div>
                            <p className="text-muted-foreground text-xs">
                                Preview: {parentSku ? `${parentSku}-` : ''}{groups.map((g) => g.attributeName.charAt(0).toUpperCase()).join('-')}...
                            </p>
                        </div>
                    )}

                    {strategy === 'sequential' && (
                        <div className="grid grid-cols-3 gap-3">
                            <div className="space-y-2">
                                <Label>Prefix</Label>
                                <Input
                                    value={sequentialConfig.prefix}
                                    onChange={(e) =>
                                        setSequentialConfig((p) => ({ ...p, prefix: e.target.value }))
                                    }
                                />
                            </div>
                            <div className="space-y-2">
                                <Label>Padding</Label>
                                <Input
                                    type="number"
                                    min={1}
                                    max={10}
                                    value={sequentialConfig.padding}
                                    onChange={(e) =>
                                        setSequentialConfig((p) => ({
                                            ...p,
                                            padding: e.target.valueAsNumber || 4,
                                        }))
                                    }
                                />
                            </div>
                            <div className="space-y-2">
                                <Label>Start from</Label>
                                <Input
                                    type="number"
                                    min={1}
                                    value={sequentialConfig.startFrom}
                                    onChange={(e) =>
                                        setSequentialConfig((p) => ({
                                            ...p,
                                            startFrom: e.target.valueAsNumber || 1,
                                        }))
                                    }
                                />
                            </div>
                        </div>
                    )}

                    {strategy === 'custom' && (
                        <div className="space-y-2">
                            <Label>Template</Label>
                            <Input
                                value={customConfig.template}
                                onChange={(e) =>
                                    setCustomConfig((p) => ({ template: e.target.value }))
                                }
                                placeholder="{sku}-{Color}-{Size}"
                            />
                            <p className="text-muted-foreground text-xs">
                                Use {'{sku}'} for parent SKU, {'{AttributeName}'} for attribute values, {'{index}'} for number
                            </p>
                        </div>
                    )}
                </div>

                <DialogFooter>
                    <Button type="button" variant="outline" onClick={() => onOpenChange(false)}>
                        Cancel
                    </Button>
                    <Button type="button" onClick={handleGenerate}>
                        Generate SKUs
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}

import { BoxIcon, PlusIcon, Wand2Icon } from 'lucide-react';
import { Button } from '@/components/ui/button';

type Props = {
    onGenerate: () => void;
    onAdd: () => void;
};

export function VariantEmptyState({ onGenerate, onAdd }: Props) {
    return (
        <div className="flex flex-col items-center gap-4 rounded-lg border border-dashed p-8 text-center">
            <BoxIcon className="text-muted-foreground size-10" />
            <div>
                <p className="text-sm font-medium">No variants yet</p>
                <p className="text-muted-foreground mt-1 text-xs">
                    Create variants from attribute combinations or add them individually
                </p>
            </div>
            <div className="flex gap-2">
                <Button type="button" variant="default" size="sm" onClick={onGenerate}>
                    <Wand2Icon className="mr-1 size-3.5" />
                    Generate Variants
                </Button>
                <Button type="button" variant="outline" size="sm" onClick={onAdd}>
                    <PlusIcon className="mr-1 size-3.5" />
                    Add Manually
                </Button>
            </div>
        </div>
    );
}

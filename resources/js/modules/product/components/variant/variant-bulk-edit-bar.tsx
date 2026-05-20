import { useCallback } from 'react';
import { XIcon, PenBoxIcon, BarcodeIcon, TagsIcon, Trash2Icon } from 'lucide-react';
import { Button } from '@/components/ui/button';

type Props = {
    selectedCount: number;
    onClearSelection: () => void;
    onBulkEdit: () => void;
    onGenerateSkus: () => void;
    onGenerateBarcodes: () => void;
    onDeleteSelected: () => void;
};

export function VariantBulkEditBar({
    selectedCount,
    onClearSelection,
    onBulkEdit,
    onGenerateSkus,
    onGenerateBarcodes,
    onDeleteSelected,
}: Props) {
    if (selectedCount === 0) return null;

    return (
        <div className="bg-primary/5 border-primary/20 flex items-center justify-between rounded-lg border px-4 py-2">
            <div className="flex items-center gap-2">
                <span className="text-sm font-medium">
                    {selectedCount} selected
                </span>
                <Button
                    type="button"
                    variant="ghost"
                    size="sm"
                    onClick={onClearSelection}
                    className="text-muted-foreground h-7 px-2 text-xs"
                >
                    <XIcon className="mr-1 size-3" />
                    Clear
                </Button>
            </div>
            <div className="flex items-center gap-1.5">
                <Button
                    type="button"
                    variant="secondary"
                    size="sm"
                    onClick={onBulkEdit}
                    className="h-7 text-xs"
                >
                    <PenBoxIcon className="mr-1 size-3" />
                    Edit
                </Button>
                <Button
                    type="button"
                    variant="secondary"
                    size="sm"
                    onClick={onGenerateSkus}
                    className="h-7 text-xs"
                >
                    <TagsIcon className="mr-1 size-3" />
                    SKUs
                </Button>
                <Button
                    type="button"
                    variant="secondary"
                    size="sm"
                    onClick={onGenerateBarcodes}
                    className="h-7 text-xs"
                >
                    <BarcodeIcon className="mr-1 size-3" />
                    Barcodes
                </Button>
                <Button
                    type="button"
                    variant="destructive"
                    size="sm"
                    onClick={onDeleteSelected}
                    className="h-7 text-xs"
                >
                    <Trash2Icon className="mr-1 size-3" />
                    Delete
                </Button>
            </div>
        </div>
    );
}

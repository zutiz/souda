import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { XIcon, Trash2Icon } from 'lucide-react';
import { ConfirmDialog } from '@/modules/shared/components/confirm-dialog';
import { useState } from 'react';
import type { BulkAction } from '../types';

type ProductBulkActionsProps = {
    selectedCount: number;
    processing: boolean;
    onBulkAction: (action: BulkAction) => void;
    onClear: () => void;
};

export function ProductBulkActions({ selectedCount, processing, onBulkAction, onClear }: ProductBulkActionsProps) {
    const [deleteOpen, setDeleteOpen] = useState(false);
    const [statusAction, setStatusAction] = useState<string>('');

    if (selectedCount === 0) return null;

    const handleStatusChange = (value: string) => {
        if (value) {
            onBulkAction(value as BulkAction);
            setStatusAction('');
        }
    };

    return (
        <>
            <div className="bg-muted/50 flex items-center gap-3 rounded-md border px-4 py-2">
                <span className="text-sm font-medium">
                    {selectedCount} selected
                </span>

                <Select value={statusAction} onValueChange={handleStatusChange}>
                    <SelectTrigger className="h-8 w-[160px]">
                        <SelectValue placeholder="Change status" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="activate">Activate</SelectItem>
                        <SelectItem value="archive">Archive</SelectItem>
                    </SelectContent>
                </Select>

                <Button
                    variant="outline"
                    size="sm"
                    className="h-8 gap-2 text-destructive"
                    onClick={() => setDeleteOpen(true)}
                >
                    <Trash2Icon className="size-3" />
                    Delete
                </Button>

                <Button variant="ghost" size="sm" className="h-8 gap-1" onClick={onClear}>
                    <XIcon className="size-3" />
                    Clear
                </Button>
            </div>

            <ConfirmDialog
                open={deleteOpen}
                onOpenChange={setDeleteOpen}
                title="Delete products?"
                description={`This will permanently delete ${selectedCount} product(s) and all associated data. This action cannot be undone.`}
                confirmLabel="Delete"
                variant="destructive"
                loading={processing}
                onConfirm={() => {
                    onBulkAction('delete');
                    setDeleteOpen(false);
                }}
            />
        </>
    );
}

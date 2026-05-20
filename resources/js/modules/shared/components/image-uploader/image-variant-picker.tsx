import { useState, useCallback } from 'react';
import { CheckIcon, ImageIcon, UploadIcon } from 'lucide-react';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import type { ImageFileState } from '../../lib/image-utils';

type Props = {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    productImages: ImageFileState[];
    currentImage?: string | null;
    onSelect: (image: ImageFileState | null) => void;
};

export function ImageVariantPicker({
    open,
    onOpenChange,
    productImages,
    currentImage,
    onSelect,
}: Props) {
    const [selectedId, setSelectedId] = useState<string | null>(null);
    const [dragOver, setDragOver] = useState(false);

    const handleSelect = useCallback(() => {
        if (!selectedId) return;
        const img = productImages.find((i) => i.id === selectedId);
        if (img) onSelect(img);
        onOpenChange(false);
    }, [selectedId, productImages, onSelect, onOpenChange]);

    const handleRemove = useCallback(() => {
        onSelect(null);
        onOpenChange(false);
    }, [onSelect, onOpenChange]);

    const hasImages = productImages.length > 0;

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>Variant Image</DialogTitle>
                    <DialogDescription>
                        Select an image from the product gallery or upload a new one for this variant
                    </DialogDescription>
                </DialogHeader>

                <div className="space-y-4">
                    {hasImages ? (
                        <div className="grid grid-cols-4 gap-2 sm:grid-cols-5">
                            {productImages.map((img) => (
                                <button
                                    key={img.id}
                                    type="button"
                                    onClick={() =>
                                        setSelectedId(
                                            selectedId === img.id ? null : img.id,
                                        )
                                    }
                                    className={cn(
                                        'group relative aspect-square overflow-hidden rounded-lg border transition-all',
                                        selectedId === img.id &&
                                            'ring-primary ring-2',
                                    )}
                                >
                                    <img
                                        src={img.preview}
                                        alt=""
                                        className="size-full object-cover"
                                    />
                                    {selectedId === img.id && (
                                        <div className="bg-primary/80 absolute inset-0 flex items-center justify-center">
                                            <CheckIcon className="text-primary-foreground size-5" />
                                        </div>
                                    )}
                                </button>
                            ))}
                        </div>
                    ) : (
                        <div
                            onDragOver={(e) => {
                                e.preventDefault();
                                setDragOver(true);
                            }}
                            onDragLeave={() => setDragOver(false)}
                            className={cn(
                                'border-muted-foreground/25 flex cursor-pointer flex-col items-center gap-2 rounded-lg border-2 border-dashed p-6 text-center transition-colors hover:border-muted-foreground/50',
                                dragOver && 'border-primary bg-primary/5',
                            )}
                        >
                            <ImageIcon className="text-muted-foreground size-8" />
                            <p className="text-muted-foreground text-sm">
                                No product images yet. Upload images in the gallery first.
                            </p>
                        </div>
                    )}

                    <div className="flex justify-between">
                        {currentImage && (
                            <Button
                                type="button"
                                variant="ghost"
                                size="sm"
                                onClick={handleRemove}
                                className="text-destructive"
                            >
                                Remove image
                            </Button>
                        )}
                        <div className="ml-auto flex gap-2">
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() => onOpenChange(false)}
                            >
                                Cancel
                            </Button>
                            <Button
                                type="button"
                                onClick={handleSelect}
                                disabled={!selectedId}
                            >
                                Select
                            </Button>
                        </div>
                    </div>
                </div>
            </DialogContent>
        </Dialog>
    );
}

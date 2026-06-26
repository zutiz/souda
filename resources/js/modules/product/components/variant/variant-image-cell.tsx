import { useCallback, useRef, useState } from 'react';
import { ImageIcon, Trash2Icon, UploadIcon, CheckIcon } from 'lucide-react';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import { cn } from '@/lib/utils';
import type { ImageFileState } from '@/modules/shared/lib/image-utils';

type Props = {
    image: unknown;
    onImageChange: (file: File | null) => void;
    size?: 'sm' | 'md';
    productImages?: ImageFileState[];
};

export function VariantImageCell({ image, onImageChange, size = 'sm', productImages = [] }: Props) {
    const inputRef = useRef<HTMLInputElement>(null);
    const [preview, setPreview] = useState<string | null>(() => {
        if (!image) return null;
        if (image instanceof File) return URL.createObjectURL(image);
        if (typeof image === 'object' && image !== null) {
            const img = image as { preview?: string; url?: string };
            return img.preview ?? img.url ?? null;
        }
        return typeof image === 'string' ? image : null;
    });
    const [isDragOver, setIsDragOver] = useState(false);
    const [pickerOpen, setPickerOpen] = useState(false);

    const handleFile = useCallback(
        (file: File | null) => {
            if (file) {
                setPreview(URL.createObjectURL(file));
                onImageChange(file);
            } else {
                if (preview?.startsWith('blob:')) URL.revokeObjectURL(preview);
                setPreview(null);
                onImageChange(null);
            }
        },
        [onImageChange, preview],
    );

    const handleSelectProductImage = useCallback(
        (img: ImageFileState) => {
            setPreview(img.preview);
            if (img.file) {
                onImageChange(img.file);
            } else if (img.compressedFile) {
                onImageChange(img.compressedFile);
            } else {
                // existing uploaded image - fetch it
                fetch(img.preview)
                    .then((res) => res.blob())
                    .then((blob) => {
                        const file = new File([blob], `variant_${img.id}.webp`, { type: 'image/webp' });
                        onImageChange(file);
                    })
                    .catch(() => {
                        // fallback: use a data URL
                    });
            }
            setPickerOpen(false);
        },
        [onImageChange],
    );

    const handleRemove = useCallback(
        (e: React.MouseEvent) => {
            e.stopPropagation();
            handleFile(null);
        },
        [handleFile],
    );

    const dimClass = size === 'sm' ? 'size-8' : 'size-12';

    if (preview) {
        return (
            <div className="group relative">
                <img
                    src={preview}
                    alt="Variant"
                    className={cn(dimClass, 'rounded object-cover ring-1 ring-black/5')}
                />
                <button
                    type="button"
                    onClick={handleRemove}
                    className="bg-background absolute -top-1 -right-1 flex size-4 items-center justify-center rounded-full opacity-0 shadow-xs transition-opacity group-hover:opacity-100"
                >
                    <Trash2Icon className="size-2.5 text-red-500" />
                </button>
            </div>
        );
    }

    return (
        <Popover open={pickerOpen} onOpenChange={setPickerOpen}>
            <PopoverTrigger asChild>
                <div
                    onDrop={(e) => {
                        e.preventDefault();
                        setIsDragOver(false);
                        const file = e.dataTransfer.files[0];
                        if (file?.type.startsWith('image/')) handleFile(file);
                    }}
                    onDragOver={(e) => { e.preventDefault(); setIsDragOver(true); }}
                    onDragLeave={() => setIsDragOver(false)}
                    onClick={() => {
                        if (productImages.length > 0) {
                            setPickerOpen(true);
                        } else {
                            inputRef.current?.click();
                        }
                    }}
                    className={cn(
                        dimClass,
                        'border-muted-foreground/25 hover:border-muted-foreground/50 flex cursor-pointer items-center justify-center rounded border border-dashed transition-colors',
                        isDragOver && 'border-primary bg-primary/5',
                    )}
                >
                    <input
                        ref={inputRef}
                        type="file"
                        accept="image/png,image/jpeg,image/webp"
                        className="hidden"
                        onChange={(e) => {
                            const file = e.target.files?.[0] ?? null;
                            handleFile(file);
                            e.target.value = '';
                        }}
                    />
                    <UploadIcon className="text-muted-foreground size-3" />
                </div>
            </PopoverTrigger>

            {productImages.length > 0 && (
                <PopoverContent side="right" align="start" className="w-48 p-2">
                    <div className="mb-1.5 px-0.5">
                        <p className="text-muted-foreground text-[11px] font-medium">
                            Product images
                        </p>
                    </div>
                    <div className="grid grid-cols-3 gap-1.5">
                        {productImages.slice(0, 9).map((img) => (
                            <button
                                key={img.id}
                                type="button"
                                onClick={() => handleSelectProductImage(img)}
                                className="group/img relative aspect-square overflow-hidden rounded border transition-all hover:ring-2 hover:ring-primary"
                            >
                                <img
                                    src={img.preview}
                                    alt=""
                                    className="size-full object-cover"
                                />
                            </button>
                        ))}
                    </div>
                    {productImages.length > 9 && (
                        <p className="text-muted-foreground mt-1 text-center text-[10px]">
                            +{productImages.length - 9} more
                        </p>
                    )}
                    <div className="mt-2 border-t pt-1.5">
                        <button
                            type="button"
                            onClick={() => {
                                setPickerOpen(false);
                                inputRef.current?.click();
                            }}
                            className="text-muted-foreground hover:text-foreground flex w-full items-center gap-1.5 rounded px-1 py-1 text-[11px] transition-colors"
                        >
                            <UploadIcon className="size-3" />
                            Upload new
                        </button>
                    </div>
                </PopoverContent>
            )}
        </Popover>
    );
}

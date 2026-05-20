import { memo } from 'react';
import { StarIcon, Trash2Icon, GripVerticalIcon, AlertCircleIcon } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip';
import { cn } from '@/lib/utils';
import { formatFileSize, compressionRatio, type ImageFileState } from '../../lib/image-utils';

type Props = {
    image: ImageFileState;
    index: number;
    isDragOver: boolean;
    onSetMain: (id: string) => void;
    onRemove: (id: string) => void;
};

export const ImagePreviewCard = memo(function ImagePreviewCard({
    image,
    index,
    isDragOver,
    onSetMain,
    onRemove,
}: Props) {
    return (
        <div
            className={cn(
                'group relative overflow-hidden rounded-lg border transition-all',
                image.isMain && 'ring-primary ring-2',
                isDragOver && 'ring-primary scale-105 ring-2',
                image.status === 'error' && 'ring-destructive ring-2',
            )}
        >
            <div className="relative aspect-square w-full overflow-hidden bg-muted/30">
                <img
                    src={image.preview}
                    alt={`Image ${index + 1}`}
                    className="size-full object-cover"
                    loading="lazy"
                />

                {image.status === 'error' && (
                    <div className="bg-destructive/10 absolute inset-0 flex items-center justify-center">
                        <Tooltip>
                            <TooltipTrigger asChild>
                                <AlertCircleIcon className="text-destructive size-5" />
                            </TooltipTrigger>
                            <TooltipContent side="bottom">
                                {image.error || 'Upload failed'}
                            </TooltipContent>
                        </Tooltip>
                    </div>
                )}

                <div className="absolute top-1 left-1 opacity-0 transition-opacity group-hover:opacity-100">
                    <div className="bg-background/80 flex size-6 cursor-grab items-center justify-center rounded active:cursor-grabbing">
                        <GripVerticalIcon className="text-muted-foreground size-3" />
                    </div>
                </div>
            </div>

            <div className="flex items-center justify-between gap-1 px-1.5 py-1">
                <div className="min-w-0 flex-1">
                    {image.compressedSize && image.originalSize ? (
                        <p className="text-muted-foreground truncate text-[10px]">
                            {formatFileSize(image.compressedSize)}
                            {image.originalSize !== image.compressedSize && (
                                <span className="text-positive ml-1">
                                    -{compressionRatio(image.originalSize, image.compressedSize)}
                                </span>
                            )}
                        </p>
                    ) : image.originalSize ? (
                        <p className="text-muted-foreground truncate text-[10px]">
                            {formatFileSize(image.originalSize)}
                        </p>
                    ) : null}
                    {image.width && image.height && (
                        <p className="text-muted-foreground truncate text-[10px]">
                            {image.width}×{image.height}
                        </p>
                    )}
                </div>
            </div>

            {/* Main badge */}
            {image.isMain && (
                <span className="bg-primary text-primary-foreground absolute top-1.5 left-8 rounded px-1.5 py-0.5 text-[10px] font-medium leading-none">
                    Main
                </span>
            )}

            {image.status !== 'error' && (
                <div className="from-background/80 absolute right-0 bottom-0 left-0 flex items-center justify-between bg-gradient-to-t to-transparent p-1.5 opacity-0 transition-opacity group-hover:opacity-100">
                    <div />
                    <div className="flex gap-0.5">
                        {!image.isMain && (
                            <Button
                                type="button"
                                size="icon"
                                variant="ghost"
                                className="bg-background/80 hover:bg-background size-6 rounded-sm"
                                onClick={() => onSetMain(image.id)}
                                title="Set as main image"
                            >
                                <StarIcon className="size-3" />
                            </Button>
                        )}
                        <Button
                            type="button"
                            size="icon"
                            variant="ghost"
                            className="bg-background/80 hover:bg-background size-6 rounded-sm text-red-500"
                            onClick={() => onRemove(image.id)}
                            title="Remove image"
                        >
                            <Trash2Icon className="size-3" />
                        </Button>
                    </div>
                </div>
            )}
        </div>
    );
});

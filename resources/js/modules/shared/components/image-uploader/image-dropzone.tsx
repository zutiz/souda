import { useCallback, useRef, useState } from 'react';
import { ImageIcon, LoaderCircleIcon } from 'lucide-react';
import { cn } from '@/lib/utils';
import { formatFileSize } from '../../lib/image-utils';
import type { ImageValidationRule } from '../../lib/image-utils';
import { DEFAULT_IMAGE_RULES } from '../../lib/image-utils';

type Props = {
    onFilesSelected: (files: FileList) => void;
    isProcessing: boolean;
    processingProgress: number;
    disabled?: boolean;
    rules?: Partial<ImageValidationRule>;
    compact?: boolean;
    className?: string;
};

export function ImageDropzone({
    onFilesSelected,
    isProcessing,
    processingProgress,
    disabled = false,
    rules,
    compact = false,
    className,
}: Props) {
    const inputRef = useRef<HTMLInputElement>(null);
    const [isDragOver, setIsDragOver] = useState(false);

    const handleDrop = useCallback(
        (e: React.DragEvent) => {
            e.preventDefault();
            setIsDragOver(false);
            if (!disabled && e.dataTransfer.files.length > 0) {
                onFilesSelected(e.dataTransfer.files);
            }
        },
        [onFilesSelected, disabled],
    );

    const handleChange = useCallback(
        (e: React.ChangeEvent<HTMLInputElement>) => {
            if (e.target.files && e.target.files.length > 0) {
                onFilesSelected(e.target.files);
                e.target.value = '';
            }
        },
        [onFilesSelected],
    );

    const mergedRules = { ...DEFAULT_IMAGE_RULES, ...rules };

    if (compact) {
        return (
            <div
                onDrop={handleDrop}
                onDragOver={(e) => { e.preventDefault(); setIsDragOver(true); }}
                onDragLeave={() => setIsDragOver(false)}
                onClick={() => !disabled && inputRef.current?.click()}
                className={cn(
                    'border-muted-foreground/25 hover:border-muted-foreground/50 flex cursor-pointer items-center justify-center gap-2 rounded-lg border-2 border-dashed p-4 text-sm transition-colors',
                    isDragOver && 'border-primary bg-primary/5',
                    disabled && 'pointer-events-none opacity-50',
                    className,
                )}
            >
                <input
                    ref={inputRef}
                    type="file"
                    multiple
                    accept={mergedRules.allowedTypes.join(',')}
                    className="hidden"
                    onChange={handleChange}
                    disabled={disabled}
                />
                {isProcessing ? (
                    <>
                        <LoaderCircleIcon className="size-4 animate-spin" />
                        <span className="text-muted-foreground">
                            Processing... {processingProgress}%
                        </span>
                    </>
                ) : (
                    <>
                        <ImageIcon className="text-muted-foreground size-4" />
                        <span className="text-muted-foreground">Add images</span>
                    </>
                )}
            </div>
        );
    }

    return (
        <div
            onDrop={handleDrop}
            onDragOver={(e) => { e.preventDefault(); setIsDragOver(true); }}
            onDragLeave={() => setIsDragOver(false)}
            onClick={() => !disabled && inputRef.current?.click()}
            className={cn(
                'border-muted-foreground/25 hover:border-muted-foreground/50 flex cursor-pointer flex-col items-center gap-3 rounded-lg border-2 border-dashed p-8 transition-colors',
                isDragOver && 'border-primary bg-primary/5',
                disabled && 'pointer-events-none opacity-50',
                className,
            )}
        >
            <input
                ref={inputRef}
                type="file"
                multiple
                accept={mergedRules.allowedTypes.join(',')}
                className="hidden"
                onChange={handleChange}
                disabled={disabled}
            />

            {isProcessing ? (
                <>
                    <LoaderCircleIcon className="text-muted-foreground size-8 animate-spin" />
                    <div className="text-center">
                        <p className="text-sm font-medium">Processing images...</p>
                        <p className="text-muted-foreground text-xs">{processingProgress}% complete</p>
                    </div>
                    <div className="bg-muted h-1.5 w-full max-w-xs overflow-hidden rounded-full">
                        <div
                            className="bg-primary h-full rounded-full transition-all duration-300"
                            style={{ width: `${processingProgress}%` }}
                        />
                    </div>
                </>
            ) : (
                <>
                    <ImageIcon className="text-muted-foreground size-10" />
                    <div className="text-center">
                        <p className="text-sm font-medium">
                            Drop images here or click to browse
                        </p>
                        <p className="text-muted-foreground mt-0.5 text-xs">
                            {mergedRules.allowedTypes.map((t) => t.split('/')[1]).join(', ').toUpperCase()}
                            {' · '}Up to {formatFileSize(mergedRules.maxFileSize)}
                            {' · '}Max {mergedRules.maxCount}
                        </p>
                    </div>
                </>
            )}
        </div>
    );
}

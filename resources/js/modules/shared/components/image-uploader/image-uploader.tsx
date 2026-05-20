import { useCallback } from 'react';
import { ImageDropzone } from './image-dropzone';
import { ImagePreviewGrid } from './image-preview-grid';
import { useImageUpload } from '../../hooks/use-image-upload';
import type { ImageFileState, ImageValidationRule } from '../../lib/image-utils';
import type { CompressionOptions } from '../../lib/image-compression';

type Props = {
    value: any[];
    onChange: (images: any[]) => void;
    errors?: Partial<Record<string, string>>;
    rules?: Partial<ImageValidationRule>;
    compression?: CompressionOptions;
    maxCount?: number;
    compact?: boolean;
};

export function ImageUploader({
    value,
    onChange,
    errors: externalErrors,
    rules,
    compression,
    maxCount,
    compact = false,
}: Props) {
    const {
        images,
        errors,
        isProcessing,
        overallProgress,
        addFiles,
        removeImage,
        setMainImage,
        reorderImages,
    } = useImageUpload({
        rules,
        compression,
        maxCount,
        initialImages: mapToImageState(value),
    });

    const syncToParent = useCallback(
        (updated: ImageFileState[]) => {
            onChange(updated);
        },
        [onChange],
    );

    const handleAddFiles = useCallback(
        async (files: FileList) => {
            await addFiles(files);
        },
        [addFiles],
    );

    const handleRemove = useCallback(
        (id: string) => {
            removeImage(id);
        },
        [removeImage],
    );

    const handleSetMain = useCallback(
        (id: string) => {
            setMainImage(id);
        },
        [setMainImage],
    );

    const handleReorder = useCallback(
        (fromIndex: number, toIndex: number) => {
            reorderImages(fromIndex, toIndex);
        },
        [reorderImages],
    );

    const displayErrors = [
        ...errors.map((e) => e.message),
        ...(externalErrors?.images ? [externalErrors.images] : []),
    ];

    return (
        <div className="space-y-4">
            <ImageDropzone
                onFilesSelected={handleAddFiles}
                isProcessing={isProcessing}
                processingProgress={overallProgress}
                rules={rules}
                compact={compact}
            />

            {displayErrors.length > 0 && (
                <div className="space-y-1">
                    {displayErrors.map((msg, i) => (
                        <p key={i} className="text-destructive text-xs">
                            {msg}
                        </p>
                    ))}
                </div>
            )}

            <ImagePreviewGrid
                images={images}
                onReorder={handleReorder}
                onSetMain={handleSetMain}
                onRemove={handleRemove}
            />
        </div>
    );
}

function mapToImageState(arr: any[]): ImageFileState[] {
    return arr.map((item: any) => {
        if (item && typeof item === 'object' && 'id' in item && 'preview' in item) {
            return item as ImageFileState;
        }

        const id = `ext_${Math.random().toString(36).slice(2, 9)}`;
        const preview =
            item?.preview ??
            (typeof item === 'string' ? item : item instanceof File ? URL.createObjectURL(item) : '');

        return {
            id: item?.id ?? id,
            file: item?.file ?? (item instanceof File ? item : undefined),
            compressedFile: item?.compressedFile,
            preview,
            isMain: item?.isMain ?? false,
            status: 'done',
            progress: 100,
            width: item?.width,
            height: item?.height,
            originalSize: item?.originalSize,
            compressedSize: item?.compressedSize,
        };
    });
}

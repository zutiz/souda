import { useCallback, useMemo, useRef, useState } from 'react';
import { validateFiles, type ValidationError } from '../lib/image-validation';
import { compressImages, getImageDimensions, type CompressionOptions } from '../lib/image-compression';
import { DEFAULT_COMPRESSION } from '../lib/image-compression';
import {
    createImageState,
    revokeImagePreview,
    type ImageFileState,
    type ImageValidationRule,
} from '../lib/image-utils';
import { DEFAULT_IMAGE_RULES } from '../lib/image-utils';

type UseImageUploadOptions = {
    rules?: Partial<ImageValidationRule>;
    compression?: CompressionOptions;
    initialImages?: ImageFileState[];
    maxCount?: number;
};

type UseImageUploadReturn = {
    images: ImageFileState[];
    errors: ValidationError[];
    isProcessing: boolean;
    overallProgress: number;
    addFiles: (files: FileList | File[]) => Promise<void>;
    removeImage: (id: string) => void;
    setMainImage: (id: string) => void;
    setImageError: (id: string, error: string) => void;
    moveImage: (fromIndex: number, toIndex: number) => void;
    reorderImages: (fromIndex: number, toIndex: number) => void;
    clearAll: () => void;
    resetState: () => void;
    getFiles: () => File[];
    getFormData: () => { images: ImageFileState[] };
};

export function useImageUpload(options: UseImageUploadOptions = {}): UseImageUploadReturn {
    const rules: ImageValidationRule = { ...DEFAULT_IMAGE_RULES, ...options.rules };
    const compression = options.compression ?? DEFAULT_COMPRESSION;
    const maxCount = options.maxCount ?? rules.maxCount;

    const [images, setImages] = useState<ImageFileState[]>(options.initialImages ?? []);
    const [errors, setErrors] = useState<ValidationError[]>([]);
    const [isProcessing, setIsProcessing] = useState(false);
    const [processingProgress, setProcessingProgress] = useState(0);
    const imagesRef = useRef(images);
    imagesRef.current = images;

    const clearErrors = useCallback(() => setErrors([]), []);

    const addFiles = useCallback(
        async (files: FileList | File[]) => {
            clearErrors();
            const fileArray = Array.from(files);

            const validationErrors = validateFiles(fileArray, imagesRef.current.length, rules);
            if (validationErrors.length > 0) {
                setErrors(validationErrors);
                return;
            }

            setIsProcessing(true);
            setProcessingProgress(0);

            try {
                const { files: compressedFiles, errors: compressionErrors } = await compressImages(
                    fileArray,
                    compression,
                    (current, total) => {
                        setProcessingProgress(Math.round((current / total) * 50));
                    },
                );

                if (compressionErrors.length > 0) {
                    setErrors(
                        compressionErrors.map((err) => ({
                            type: 'size' as const,
                            message: err.message,
                        })),
                    );
                }

                const newStates: ImageFileState[] = [];
                for (let i = 0; i < compressedFiles.length; i++) {
                    const cf = compressedFiles[i];
                    const state = createImageState(
                        cf,
                        imagesRef.current.length === 0 && newStates.length === 0,
                    );
                    state.status = 'ready';
                    state.compressedFile = cf;

                    try {
                        const dims = await getImageDimensions(cf);
                        state.width = dims.width;
                        state.height = dims.height;
                    } catch {
                        // dimensions unavailable
                    }

                    state.compressedSize = cf.size;
                    state.progress = 100;
                    newStates.push(state);
                }

                setProcessingProgress(100);
                setImages((prev) => [...prev, ...newStates]);
            } finally {
                setIsProcessing(false);
            }
        },
        [rules, compression, clearErrors],
    );

    const removeImage = useCallback((id: string) => {
        setImages((prev) => {
            const index = prev.findIndex((img) => img.id === id);
            if (index === -1) return prev;

            const removed = prev[index];
            revokeImagePreview(removed.preview);

            const updated = prev.filter((_, i) => i !== index);

            if (removed.isMain && updated.length > 0) {
                updated[0] = { ...updated[0], isMain: true };
            }

            return updated;
        });
    }, []);

    const setMainImage = useCallback((id: string) => {
        setImages((prev) =>
            prev.map((img) => ({ ...img, isMain: img.id === id })),
        );
    }, []);

    const setImageError = useCallback((id: string, error: string) => {
        setImages((prev) =>
            prev.map((img) =>
                img.id === id ? { ...img, status: 'error' as const, error } : img,
            ),
        );
    }, []);

    const reorderImages = useCallback((fromIndex: number, toIndex: number) => {
        setImages((prev) => {
            if (
                fromIndex < 0 || fromIndex >= prev.length ||
                toIndex < 0 || toIndex >= prev.length ||
                fromIndex === toIndex
            ) return prev;

            const copy = [...prev];
            const [moved] = copy.splice(fromIndex, 1);
            copy.splice(toIndex, 0, moved);
            return copy;
        });
    }, []);

    const moveImage = reorderImages;

    const clearAll = useCallback(() => {
        imagesRef.current.forEach((img) => revokeImagePreview(img.preview));
        setImages([]);
        setErrors([]);
    }, []);

    const resetState = useCallback(() => {
        clearAll();
        setProcessingProgress(0);
        setIsProcessing(false);
    }, [clearAll]);

    const getFiles = useCallback((): File[] => {
        return imagesRef.current
            .filter((img) => img.compressedFile || img.file)
            .map((img) => img.compressedFile ?? img.file!);
    }, []);

    const getFormData = useCallback(() => {
        return { images: imagesRef.current };
    }, []);

    const overallProgress = useMemo(() => {
        if (images.length === 0) return 0;
        const total = images.reduce((sum, img) => sum + img.progress, 0);
        return Math.round(total / images.length);
    }, [images]);

    return {
        images,
        errors,
        isProcessing,
        overallProgress,
        addFiles,
        removeImage,
        setMainImage,
        setImageError,
        moveImage,
        reorderImages,
        clearAll,
        resetState,
        getFiles,
        getFormData,
    };
}

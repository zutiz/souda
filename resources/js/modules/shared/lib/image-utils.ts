export type ImageFileState = {
    id: string;
    file?: File;
    compressedFile?: File;
    preview: string;
    compressedPreview?: string;
    isMain: boolean;
    status: 'pending' | 'validating' | 'compressing' | 'ready' | 'uploading' | 'done' | 'error';
    progress: number;
    error?: string;
    width?: number;
    height?: number;
    originalSize?: number;
    compressedSize?: number;
};

export type ImageValidationRule = {
    maxFileSize: number;
    allowedTypes: string[];
    maxWidth: number;
    maxHeight: number;
    maxCount: number;
    minWidth: number;
    minHeight: number;
};

export const DEFAULT_IMAGE_RULES: ImageValidationRule = {
    maxFileSize: 10 * 1024 * 1024,
    allowedTypes: ['image/png', 'image/jpeg', 'image/webp', 'image/avif'],
    maxWidth: 4096,
    maxHeight: 4096,
    maxCount: 20,
    minWidth: 64,
    minHeight: 64,
};

let counter = 0;

export function createImageId(): string {
    counter += 1;
    return `img_${Date.now()}_${counter}`;
}

export function createImageState(file: File, isMain: boolean = false): ImageFileState {
    return {
        id: createImageId(),
        file,
        preview: URL.createObjectURL(file),
        isMain,
        status: 'pending',
        progress: 0,
        originalSize: file.size,
    };
}

export function revokeImagePreview(preview: string): void {
    if (preview && preview.startsWith('blob:')) {
        URL.revokeObjectURL(preview);
    }
}

export function formatFileSize(bytes: number): string {
    if (bytes < 1024) return `${bytes} B`;
    if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;
    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
}

export function formatDimensions(w: number, h: number): string {
    return `${w}×${h}`;
}

export function compressionRatio(original: number, compressed: number): string {
    if (original === 0) return '0%';
    const ratio = ((original - compressed) / original) * 100;
    return `${Math.round(ratio)}%`;
}

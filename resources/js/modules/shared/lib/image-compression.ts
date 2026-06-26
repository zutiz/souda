export type CompressionOptions = {
    maxWidth: number;
    maxHeight: number;
    quality: number;
    targetType: 'image/jpeg' | 'image/webp';
};

export const DEFAULT_COMPRESSION: CompressionOptions = {
    maxWidth: 2048,
    maxHeight: 2048,
    quality: 0.85,
    targetType: 'image/webp',
};

type LoadImageResult = {
    img: HTMLImageElement;
    width: number;
    height: number;
};

function loadImage(file: File): Promise<LoadImageResult> {
    return new Promise((resolve, reject) => {
        const img = new Image();
        const url = URL.createObjectURL(file);

        img.onload = () => {
            URL.revokeObjectURL(url);
            resolve({ img, width: img.naturalWidth, height: img.naturalHeight });
        };

        img.onerror = () => {
            URL.revokeObjectURL(url);
            reject(new Error('Failed to load image'));
        };

        img.src = url;
    });
}

function calculateDimensions(
    width: number,
    height: number,
    maxWidth: number,
    maxHeight: number,
): { width: number; height: number } {
    let w = width;
    let h = height;

    if (w > maxWidth) {
        h = (h / w) * maxWidth;
        w = maxWidth;
    }

    if (h > maxHeight) {
        w = (w / h) * maxHeight;
        h = maxHeight;
    }

    return { width: Math.round(w), height: Math.round(h) };
}

export async function compressImage(
    file: File,
    options: CompressionOptions = DEFAULT_COMPRESSION,
): Promise<File> {
    const { img, width, height } = await loadImage(file);

    if (width <= options.maxWidth && height <= options.maxHeight && file.type === options.targetType) {
        return file;
    }

    const { width: newWidth, height: newHeight } = calculateDimensions(
        width,
        height,
        options.maxWidth,
        options.maxHeight,
    );

    const canvas = document.createElement('canvas');
    canvas.width = newWidth;
    canvas.height = newHeight;

    const ctx = canvas.getContext('2d');
    if (!ctx) throw new Error('Failed to get canvas context');

    ctx.imageSmoothingEnabled = true;
    ctx.imageSmoothingQuality = 'high';
    ctx.drawImage(img, 0, 0, newWidth, newHeight);

    return new Promise((resolve, reject) => {
        canvas.toBlob(
            (blob) => {
                if (!blob) {
                    reject(new Error('Compression failed'));
                    return;
                }

                const compressedFile = new File([blob], file.name.replace(/\.[^.]+$/, '.webp'), {
                    type: options.targetType,
                    lastModified: Date.now(),
                });

                resolve(compressedFile);
            },
            options.targetType,
            options.quality,
        );
    });
}

export async function compressImages(
    files: File[],
    options: CompressionOptions = DEFAULT_COMPRESSION,
    onProgress?: (current: number, total: number) => void,
): Promise<{ files: File[]; errors: Error[] }> {
    const results: File[] = [];
    const errors: Error[] = [];

    for (let i = 0; i < files.length; i++) {
        try {
            const compressed = await compressImage(files[i], options);
            results.push(compressed);
        } catch (err) {
            errors.push(err instanceof Error ? err : new Error(String(err)));
            results.push(files[i]);
        }

        onProgress?.(i + 1, files.length);
    }

    return { files: results, errors };
}

export async function getImageDimensions(
    file: File,
): Promise<{ width: number; height: number }> {
    const { width, height } = await loadImage(file);
    return { width, height };
}

import type { ImageValidationRule } from './image-utils';
import { DEFAULT_IMAGE_RULES } from './image-utils';

export type ValidationError = {
    file?: File;
    type: 'type' | 'size' | 'dimensions' | 'count';
    message: string;
};

export function validateFileType(
    file: File,
    allowedTypes: string[] = DEFAULT_IMAGE_RULES.allowedTypes,
): ValidationError | null {
    if (!allowedTypes.includes(file.type)) {
        return {
            file,
            type: 'type',
            message: `Unsupported file type "${file.type || 'unknown'}". Allowed: ${allowedTypes.map((t) => t.split('/')[1]).join(', ')}`,
        };
    }
    return null;
}

export function validateFileSize(
    file: File,
    maxSize: number = DEFAULT_IMAGE_RULES.maxFileSize,
): ValidationError | null {
    if (file.size > maxSize) {
        return {
            file,
            type: 'size',
            message: `File too large (${(file.size / (1024 * 1024)).toFixed(1)} MB). Maximum: ${(maxSize / (1024 * 1024)).toFixed(0)} MB`,
        };
    }
    return null;
}

export function validateImageCount(
    currentCount: number,
    newCount: number,
    maxCount: number = DEFAULT_IMAGE_RULES.maxCount,
): ValidationError | null {
    if (currentCount + newCount > maxCount) {
        return {
            type: 'count',
            message: `Maximum ${maxCount} images allowed. You have ${currentCount} and tried to add ${newCount} more.`,
        };
    }
    return null;
}

export function validateImageDimensions(
    width: number,
    height: number,
    rules: Pick<ImageValidationRule, 'maxWidth' | 'maxHeight' | 'minWidth' | 'minHeight'> = DEFAULT_IMAGE_RULES,
): ValidationError | null {
    if (width < rules.minWidth || height < rules.minHeight) {
        return {
            type: 'dimensions',
            message: `Image too small (${width}×${height}). Minimum: ${rules.minWidth}×${rules.minHeight}px`,
        };
    }
    if (width > rules.maxWidth || height > rules.maxHeight) {
        return {
            type: 'dimensions',
            message: `Image too large (${width}×${height}). Maximum: ${rules.maxWidth}×${rules.maxHeight}px`,
        };
    }
    return null;
}

export function validateFiles(
    files: File[],
    currentCount: number,
    rules: ImageValidationRule = DEFAULT_IMAGE_RULES,
): ValidationError[] {
    const errors: ValidationError[] = [];

    const countError = validateImageCount(currentCount, files.length, rules.maxCount);
    if (countError) {
        errors.push(countError);
        return errors;
    }

    for (const file of files) {
        const typeError = validateFileType(file, rules.allowedTypes);
        if (typeError) {
            errors.push(typeError);
            continue;
        }

        const sizeError = validateFileSize(file, rules.maxFileSize);
        if (sizeError) {
            errors.push(sizeError);
        }
    }

    return errors;
}

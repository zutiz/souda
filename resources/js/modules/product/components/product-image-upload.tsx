import { useCallback } from 'react';
import { ImageUploader } from '@/modules/shared/components/image-uploader/image-uploader';
import { FormSection } from '@/modules/shared/components/form-section';
import type { ImageFileState } from '@/modules/shared/lib/image-utils';

type Props = {
    images: any[];
    errors: Partial<Record<string, string>>;
    onChange: (field: 'images', value: any[]) => void;
};

export function ProductImageUpload({ images, errors, onChange }: Props) {
    const handleChange = useCallback(
        (updated: ImageFileState[]) => {
            onChange('images', updated);
        },
        [onChange],
    );

    return (
        <FormSection title="Images" description="Upload and arrange product images">
            <ImageUploader
                value={images}
                onChange={handleChange}
                errors={errors}
                maxCount={20}
            />
        </FormSection>
    );
}

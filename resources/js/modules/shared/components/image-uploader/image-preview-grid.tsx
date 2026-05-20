import { useCallback } from 'react';
import { ImagePreviewCard } from './image-preview-card';
import { useImageSorting } from '../../hooks/use-image-sorting';
import type { ImageFileState } from '../../lib/image-utils';

type Props = {
    images: ImageFileState[];
    onReorder: (fromIndex: number, toIndex: number) => void;
    onSetMain: (id: string) => void;
    onRemove: (id: string) => void;
};

export function ImagePreviewGrid({ images, onReorder, onSetMain, onRemove }: Props) {
    const { getDropTargetProps } = useImageSorting({ onReorder });

    if (images.length === 0) return null;

    return (
        <div className="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5">
            {images.map((image, index) => (
                <div key={image.id} {...getDropTargetProps(index)}>
                    <ImagePreviewCard
                        image={image}
                        index={index}
                        isDragOver={false}
                        onSetMain={onSetMain}
                        onRemove={onRemove}
                    />
                </div>
            ))}
        </div>
    );
}

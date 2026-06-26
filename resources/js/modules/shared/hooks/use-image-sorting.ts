import { useCallback, useRef, useState } from 'react';

type DragState = {
    fromIndex: number;
    toIndex: number;
    isDragging: boolean;
};

type UseImageSortingOptions = {
    onReorder: (fromIndex: number, toIndex: number) => void;
};

type UseImageSortingReturn = {
    dragState: DragState;
    getDragHandlers: (index: number) => {
        draggable: boolean;
        onDragStart: (e: React.DragEvent) => void;
        onDragOver: (e: React.DragEvent) => void;
        onDragEnd: (e: React.DragEvent) => void;
        onDragEnter: (e: React.DragEvent) => void;
        onDragLeave: (e: React.DragEvent) => void;
    };
    getDropTargetProps: (index: number) => {
        onDragOver: (e: React.DragEvent) => void;
        onDrop: (e: React.DragEvent) => void;
        onDragEnter: (e: React.DragEvent) => void;
        onDragLeave: (e: React.DragEvent) => void;
    };
};

export function useImageSorting({ onReorder }: UseImageSortingOptions): UseImageSortingReturn {
    const [dragState, setDragState] = useState<DragState>({
        fromIndex: -1,
        toIndex: -1,
        isDragging: false,
    });
    const dragImageRef = useRef<string | null>(null);

    const getDragHandlers = useCallback(
        (index: number) => ({
            draggable: true,
            onDragStart: (e: React.DragEvent) => {
                setDragState({ fromIndex: index, toIndex: index, isDragging: true });
                if (e.currentTarget instanceof HTMLElement) {
                    dragImageRef.current = e.currentTarget.innerHTML;
                    e.dataTransfer.effectAllowed = 'move';
                }
            },
            onDragOver: (e: React.DragEvent) => {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';
            },
            onDragEnd: () => {
                setDragState({ fromIndex: -1, toIndex: -1, isDragging: false });
                dragImageRef.current = null;
            },
            onDragEnter: (e: React.DragEvent) => {
                e.preventDefault();
                if (dragState.isDragging && index !== dragState.fromIndex) {
                    setDragState((prev) => ({ ...prev, toIndex: index }));
                }
            },
            onDragLeave: () => {},
        }),
        [dragState.isDragging, dragState.fromIndex],
    );

    const getDropTargetProps = useCallback(
        (index: number) => ({
            onDragOver: (e: React.DragEvent) => {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';
                if (dragState.isDragging && index !== dragState.fromIndex) {
                    setDragState((prev) => ({ ...prev, toIndex: index }));
                }
            },
            onDrop: (e: React.DragEvent) => {
                e.preventDefault();
                if (dragState.fromIndex !== -1 && dragState.fromIndex !== index) {
                    onReorder(dragState.fromIndex, index);
                }
                setDragState({ fromIndex: -1, toIndex: -1, isDragging: false });
            },
            onDragEnter: (e: React.DragEvent) => {
                e.preventDefault();
            },
            onDragLeave: () => {},
        }),
        [dragState, onReorder],
    );

    return {
        dragState,
        getDragHandlers,
        getDropTargetProps,
    };
}

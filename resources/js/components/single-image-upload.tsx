import { useRef, useState } from 'react';
import { Upload, X } from 'lucide-react';

type Props = {
    currentImage?: string | null;
    onFileChange: (file: File | null) => void;
    accept?: string;
    label?: string;
};

export function SingleImageUpload({ currentImage, onFileChange, accept = 'image/jpg,image/jpeg,image/png,image/webp', label }: Props) {
    const inputRef = useRef<HTMLInputElement>(null);
    const [preview, setPreview] = useState<string | null>(currentImage ?? null);

    const handleFile = (file: File | null) => {
        if (file) {
            const reader = new FileReader();
            reader.onload = (ev) => setPreview(ev.target?.result as string);
            reader.readAsDataURL(file);
        }
        onFileChange(file);
    };

    const handleRemove = () => {
        setPreview(null);
        if (inputRef.current) inputRef.current.value = '';
        onFileChange(null);
    };

    return (
        <div className="flex items-center gap-4">
            {preview && (
                <div className="flex aspect-square size-16 items-center justify-center rounded-lg border">
                    <img src={preview} alt="" className="size-12 rounded object-contain" />
                </div>
            )}
            <button
                type="button"
                onClick={() => inputRef.current?.click()}
                className="flex cursor-pointer items-center gap-2 rounded-md border border-input px-4 py-2 text-sm font-medium text-muted-foreground hover:bg-accent hover:text-accent-foreground"
            >
                <Upload className="h-4 w-4" />
                {label ?? (preview ? 'Change' : 'Upload')}
            </button>
            <input
                ref={inputRef}
                type="file"
                accept={accept}
                className="hidden"
                onChange={(e) => {
                    const file = e.target.files?.[0];
                    if (file) handleFile(file);
                }}
            />
            {preview && (
                <button type="button" onClick={handleRemove} className="flex items-center gap-1 text-sm text-muted-foreground hover:text-destructive">
                    <X className="h-4 w-4" />
                    Remove
                </button>
            )}
        </div>
    );
}

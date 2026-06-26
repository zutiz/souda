import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

type FormActionsProps = {
    cancelLabel?: string;
    submitLabel?: string;
    secondaryLabel?: string;
    onCancel?: () => void;
    onSecondary?: () => void;
    processing?: boolean;
    className?: string;
};

export function FormActions({
    cancelLabel = 'Cancel',
    submitLabel = 'Save',
    secondaryLabel,
    onCancel,
    onSecondary,
    processing = false,
    className,
}: FormActionsProps) {
    return (
        <div
            className={cn(
                'border-t bg-background sticky bottom-0 z-10 flex items-center justify-between gap-4 px-6 py-4',
                className,
            )}
        >
            <div>
                {onCancel && (
                    <Button type="button" variant="ghost" onClick={onCancel} disabled={processing}>
                        {cancelLabel}
                    </Button>
                )}
            </div>
            <div className="flex items-center gap-3">
                {secondaryLabel && onSecondary && (
                    <Button type="button" variant="outline" onClick={onSecondary} disabled={processing}>
                        {secondaryLabel}
                    </Button>
                )}
                <Button type="submit" disabled={processing}>
                    {processing ? 'Saving...' : submitLabel}
                </Button>
            </div>
        </div>
    );
}

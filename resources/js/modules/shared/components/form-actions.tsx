import { Loader2 } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

type FormActionsProps = {
    cancelLabel?: string;
    submitLabel?: string;
    secondaryLabel?: string;
    onCancel?: () => void;
    onSecondary?: () => void;
    onSaveDraft?: () => void;
    processing?: boolean;
    className?: string;
    showCancel?: boolean;
    showSaveDraft?: boolean;
    variant?: 'sticky' | 'inline';
};

export function FormActions({
    cancelLabel = 'Cancel',
    submitLabel = 'Save changes',
    secondaryLabel,
    onCancel,
    onSecondary,
    onSaveDraft,
    processing = false,
    className,
    showCancel = true,
    showSaveDraft = false,
    variant = 'inline',
}: FormActionsProps) {
    if (variant === 'sticky') {
        return (
            <div
                className={cn(
                    'border-t bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60 sticky bottom-0 z-20 flex items-center justify-between gap-4 px-6 py-4 shadow-[0_-1px_0_0_var(--border)]',
                    className,
                )}
            >
                <div>{showCancel && onCancel && (
                    <Button type="button" variant="ghost" onClick={onCancel} disabled={processing}>
                        {cancelLabel}
                    </Button>
                )}</div>

                <div className="flex items-center gap-2">
                    {showSaveDraft && onSaveDraft && (
                        <Button
                            type="button"
                            variant="outline"
                            onClick={onSaveDraft}
                            disabled={processing}
                            className="gap-2"
                        >
                            Save draft
                        </Button>
                    )}
                    {secondaryLabel && onSecondary && (
                        <Button type="button" variant="outline" onClick={onSecondary} disabled={processing}>
                            {secondaryLabel}
                        </Button>
                    )}
                    <Button type="submit" disabled={processing} className="gap-2 min-w-[100px]">
                        {processing && <Loader2 className="size-4 animate-spin" />}
                        {processing ? 'Saving...' : submitLabel}
                    </Button>
                </div>
            </div>
        );
    }

    return (
        <div
            className={cn(
                'flex items-center justify-end gap-3 pt-6 border-t',
                className,
            )}
        >
            {showCancel && onCancel && (
                <Button type="button" variant="ghost" onClick={onCancel} disabled={processing}>
                    {cancelLabel}
                </Button>
            )}
            {showSaveDraft && onSaveDraft && (
                <Button type="button" variant="outline" onClick={onSaveDraft} disabled={processing}>
                    Save draft
                </Button>
            )}
            {secondaryLabel && onSecondary && (
                <Button type="button" variant="outline" onClick={onSecondary} disabled={processing}>
                    {secondaryLabel}
                </Button>
            )}
            <Button type="submit" disabled={processing} className="gap-2 min-w-[100px]">
                {processing && <Loader2 className="size-4 animate-spin" />}
                {processing ? 'Saving...' : submitLabel}
            </Button>
        </div>
    );
}

// Compact inline actions for smaller forms
export function FormActionsCompact({
    onCancel,
    onSubmit,
    submitLabel = 'Save',
    cancelLabel = 'Cancel',
    processing = false,
    className,
}: {
    onCancel?: () => void;
    onSubmit?: () => void;
    submitLabel?: string;
    cancelLabel?: string;
    processing?: boolean;
    className?: string;
}) {
    return (
        <div className={cn('flex items-center gap-2', className)}>
            {onCancel && (
                <Button type="button" size="sm" variant="ghost" onClick={onCancel} disabled={processing}>
                    {cancelLabel}
                </Button>
            )}
            <Button
                type="submit"
                size="sm"
                onClick={onSubmit}
                disabled={processing}
                className="gap-1.5"
            >
                {processing && <Loader2 className="size-3.5 animate-spin" />}
                {processing ? 'Saving' : submitLabel}
            </Button>
        </div>
    );
}

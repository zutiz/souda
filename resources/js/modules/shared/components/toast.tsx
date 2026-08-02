import { useEffect, useState } from 'react';
import { X, CheckCircle, AlertCircle, AlertTriangle, Info, Loader2 } from 'lucide-react';
import { cn } from '@/lib/utils';
import type { Toast as ToastType, ToastType as ToastVariant } from '../hooks/use-toast';

const toastIcons: Record<ToastVariant, React.ComponentType<{ className?: string }>> = {
    success: CheckCircle,
    error: AlertCircle,
    warning: AlertTriangle,
    info: Info,
};

const toastStyles: Record<ToastVariant, string> = {
    success: 'border-green-200 bg-green-50 dark:border-green-900 dark:bg-green-950 text-green-900 dark:text-green-100',
    error: 'border-red-200 bg-red-50 dark:border-red-900 dark:bg-red-950 text-red-900 dark:text-red-100',
    warning: 'border-yellow-200 bg-yellow-50 dark:border-yellow-900 dark:bg-yellow-950 text-yellow-900 dark:text-yellow-100',
    info: 'border-blue-200 bg-blue-50 dark:border-blue-900 dark:bg-blue-950 text-blue-900 dark:text-blue-100',
};

const iconStyles: Record<ToastVariant, string> = {
    success: 'text-green-600 dark:text-green-400',
    error: 'text-red-600 dark:text-red-400',
    warning: 'text-yellow-600 dark:text-yellow-400',
    info: 'text-blue-600 dark:text-blue-400',
};

export function Toast({
    id,
    type,
    title,
    description,
    action,
    onDismiss,
}: {
    id: string;
    type: ToastType;
    title: string;
    description?: string;
    action?: { label: string; onClick: () => void };
    onDismiss: (id: string) => void;
}) {
    const [isVisible, setIsVisible] = useState(false);
    const [isLeaving, setIsLeaving] = useState(false);

    useEffect(() => {
        // Animate in
        requestAnimationFrame(() => setIsVisible(true));
    }, []);

    const handleDismiss = () => {
        setIsLeaving(true);
        setTimeout(() => onDismiss(id), 200);
    };

    const Icon = toastIcons[type];

    return (
        <div
            className={cn(
                'relative pointer-events-auto w-full max-w-sm rounded-lg border p-4 shadow-lg transition-all duration-200',
                toastStyles[type],
                isVisible && !isLeaving ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-2'
            )}
            role="alert"
        >
            <div className="flex gap-3">
                <Icon className={cn('size-5 shrink-0 mt-0.5', iconStyles[type])} />
                <div className="flex-1 space-y-1.5">
                    <p className="text-sm font-medium">{title}</p>
                    {description && (
                        <p className="text-sm opacity-80">{description}</p>
                    )}
                    {action && (
                        <button
                            onClick={action.onClick}
                            className="text-sm font-medium underline underline-offset-2 hover:opacity-80 transition-opacity"
                        >
                            {action.label}
                        </button>
                    )}
                </div>
                <button
                    onClick={handleDismiss}
                    className="shrink-0 rounded-sm p-0.5 opacity-60 hover:opacity-100 transition-opacity"
                >
                    <X className="size-4" />
                </button>
            </div>
        </div>
    );
}

// Compact inline toast for form feedback
export function InlineToast({
    type,
    title,
    onDismiss,
}: {
    type: ToastType;
    title: string;
    onDismiss?: () => void;
}) {
    const Icon = toastIcons[type];

    return (
        <div
            className={cn(
                'flex items-center gap-2 rounded-md border px-3 py-2 text-sm',
                toastStyles[type]
            )}
            role="alert"
        >
            <Icon className={cn('size-4 shrink-0', iconStyles[type])} />
            <span className="flex-1 font-medium">{title}</span>
            {onDismiss && (
                <button
                    onClick={onDismiss}
                    className="shrink-0 rounded-sm p-0.5 opacity-60 hover:opacity-100"
                >
                    <X className="size-3.5" />
                </button>
            )}
        </div>
    );
}

// Loading toast with spinner
export function LoadingToast({
    title,
    description,
}: {
    title: string;
    description?: string;
}) {
    return (
        <div
            className={cn(
                'pointer-events-auto w-full max-w-sm rounded-lg border bg-background p-4 shadow-lg'
            )}
        >
            <div className="flex gap-3">
                <Loader2 className="size-5 shrink-0 animate-spin text-primary" />
                <div className="flex-1 space-y-1.5">
                    <p className="text-sm font-medium">{title}</p>
                    {description && (
                        <p className="text-sm text-muted-foreground">{description}</p>
                    )}
                </div>
            </div>
        </div>
    );
}

// Progress toast with bar
export function ProgressToast({
    title,
    progress,
    description,
}: {
    title: string;
    progress: number;
    description?: string;
}) {
    return (
        <div
            className={cn(
                'pointer-events-auto w-full max-w-sm rounded-lg border bg-background p-4 shadow-lg'
            )}
        >
            <div className="flex gap-3">
                <div className="flex-1 space-y-1.5">
                    <p className="text-sm font-medium">{title}</p>
                    {description && (
                        <p className="text-sm text-muted-foreground">{description}</p>
                    )}
                    <div className="h-1.5 rounded-full bg-muted overflow-hidden">
                        <div
                            className="h-full rounded-full bg-primary transition-all duration-300"
                            style={{ width: `${Math.min(100, Math.max(0, progress))}%` }}
                        />
                    </div>
                </div>
            </div>
        </div>
    );
}
import { useToast, type Toast as ToastType } from '../hooks/use-toast';
import { Toast } from './toast';
import { cn } from '@/lib/utils';

export function Toaster({
    position = 'bottom-right',
    className,
}: {
    position?: 'top-left' | 'top-right' | 'top-center' | 'bottom-left' | 'bottom-right' | 'bottom-center';
    className?: string;
}) {
    const { toasts, dismiss } = useToast();

    const positionClasses = {
        'top-left': 'top-4 left-4 flex-col-reverse',
        'top-right': 'top-4 right-4 flex-col',
        'top-center': 'top-4 left-1/2 -translate-x-1/2 flex-col',
        'bottom-left': 'bottom-4 left-4 flex-col',
        'bottom-right': 'bottom-4 right-4 flex-col-reverse',
        'bottom-center': 'bottom-4 left-1/2 -translate-x-1/2 flex-col-reverse',
    };

    return (
        <div
            className={cn(
                'fixed z-[100] flex gap-2 p-4 pointer-events-none',
                positionClasses[position],
                className
            )}
            aria-live="polite"
            aria-label="Notifications"
        >
            {toasts.map((toast) => (
                <Toast
                    key={toast.id}
                    id={toast.id}
                    type={toast.type}
                    title={toast.title}
                    description={toast.description}
                    action={toast.action}
                    onDismiss={dismiss}
                />
            ))}
        </div>
    );
}

// Simple inline toaster for use within components
export function InlineToaster({ toasts }: { toasts: ToastType[] }) {
    if (toasts.length === 0) return null;

    return (
        <div className="flex flex-col gap-2" aria-live="polite">
            {toasts.map((toast) => (
                <Toast
                    key={toast.id}
                    id={toast.id}
                    type={toast.type}
                    title={toast.title}
                    description={toast.description}
                    action={toast.action}
                    onDismiss={() => {}}
                />
            ))}
        </div>
    );
}
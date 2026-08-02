import { useState, useCallback, useEffect } from 'react';

export type ToastType = 'success' | 'error' | 'warning' | 'info';

export type Toast = {
    id: string;
    type: ToastType;
    title: string;
    description?: string;
    duration?: number;
    action?: {
        label: string;
        onClick: () => void;
    };
};

type ToastOptions = Omit<Toast, 'id'>;

function generateId(): string {
    return Math.random().toString(36).substring(2, 9);
}

// Global toast state for outside component access
let toastListeners: ((toasts: Toast[]) => void)[] = [];
let globalToasts: Toast[] = [];

function notifyListeners() {
    toastListeners.forEach((listener) => listener([...globalToasts]));
}

export function useToast() {
    const [toasts, setToasts] = useState<Toast[]>([]);

    useEffect(() => {
        const listener = (newToasts: Toast[]) => setToasts(newToasts);
        toastListeners.push(listener);
        setToasts([...globalToasts]);

        return () => {
            toastListeners = toastListeners.filter((l) => l !== listener);
        };
    }, []);

    const dismiss = useCallback((id: string) => {
        globalToasts = globalToasts.filter((t) => t.id !== id);
        notifyListeners();
    }, []);

    const toast = useCallback((options: ToastOptions) => {
        const newToast: Toast = {
            id: generateId(),
            ...options,
        };
        globalToasts = [...globalToasts, newToast];
        notifyListeners();

        // Auto-dismiss after duration (default 5000ms)
        const duration = options.duration ?? 5000;
        if (duration > 0) {
            setTimeout(() => {
                dismiss(newToast.id);
            }, duration);
        }

        return newToast.id;
    }, [dismiss]);

    const success = useCallback((title: string, description?: string) => {
        return toast({ type: 'success', title, description });
    }, [toast]);

    const error = useCallback((title: string, description?: string) => {
        return toast({ type: 'error', title, description, duration: 8000 });
    }, [toast]);

    const warning = useCallback((title: string, description?: string) => {
        return toast({ type: 'warning', title, description });
    }, [toast]);

    const info = useCallback((title: string, description?: string) => {
        return toast({ type: 'info', title, description });
    }, [toast]);

    return {
        toasts,
        toast,
        success,
        error,
        warning,
        info,
        dismiss,
    };
}

// Static methods for use outside React components
export const toast = {
    success: (title: string, description?: string) => {
        const id = generateId();
        const newToast: Toast = { id, type: 'success', title, description };
        globalToasts = [...globalToasts, newToast];
        notifyListeners();
        setTimeout(() => {
            globalToasts = globalToasts.filter((t) => t.id !== id);
            notifyListeners();
        }, 5000);
        return id;
    },
    error: (title: string, description?: string) => {
        const id = generateId();
        const newToast: Toast = { id, type: 'error', title, description, duration: 8000 };
        globalToasts = [...globalToasts, newToast];
        notifyListeners();
        setTimeout(() => {
            globalToasts = globalToasts.filter((t) => t.id !== id);
            notifyListeners();
        }, 8000);
        return id;
    },
    warning: (title: string, description?: string) => {
        const id = generateId();
        const newToast: Toast = { id, type: 'warning', title, description };
        globalToasts = [...globalToasts, newToast];
        notifyListeners();
        setTimeout(() => {
            globalToasts = globalToasts.filter((t) => t.id !== id);
            notifyListeners();
        }, 5000);
        return id;
    },
    info: (title: string, description?: string) => {
        const id = generateId();
        const newToast: Toast = { id, type: 'info', title, description };
        globalToasts = [...globalToasts, newToast];
        notifyListeners();
        setTimeout(() => {
            globalToasts = globalToasts.filter((t) => t.id !== id);
            notifyListeners();
        }, 5000);
        return id;
    },
    custom: (options: ToastOptions) => {
        const id = generateId();
        const newToast: Toast = { id, ...options };
        globalToasts = [...globalToasts, newToast];
        notifyListeners();
        if (options.duration !== 0) {
            setTimeout(() => {
                globalToasts = globalToasts.filter((t) => t.id !== id);
                notifyListeners();
            }, options.duration ?? 5000);
        }
        return id;
    },
    dismiss: (id: string) => {
        globalToasts = globalToasts.filter((t) => t.id !== id);
        notifyListeners();
    },
    dismissAll: () => {
        globalToasts = [];
        notifyListeners();
    },
};

// Confirm dialog helper
export function useConfirm() {
    const [isOpen, setIsOpen] = useState(false);
    const [config, setConfig] = useState<{
        title: string;
        description?: string;
        confirmLabel?: string;
        cancelLabel?: string;
        variant?: 'default' | 'destructive';
        onConfirm: () => void | Promise<void>;
        onCancel?: () => void;
    } | null>(null);

    const confirm = useCallback((options: Omit<NonNullable<typeof config>, 'onConfirm' | 'onCancel'> & { onConfirm: () => void | Promise<void> }) => {
        setConfig({
            ...options,
            onConfirm: options.onConfirm,
            onCancel: options.onCancel,
        });
        setIsOpen(true);
    }, []);

    const confirmAction = useCallback(async () => {
        if (config?.onConfirm) {
            await config.onConfirm();
        }
        setIsOpen(false);
        setConfig(null);
    }, [config]);

    const cancelAction = useCallback(() => {
        config?.onCancel?.();
        setIsOpen(false);
        setConfig(null);
    }, [config]);

    return {
        isOpen,
        config,
        confirm,
        confirmAction,
        cancel: cancelAction,
    };
}
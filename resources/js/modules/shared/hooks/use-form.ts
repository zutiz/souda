import { useState, useCallback, useEffect, useRef } from 'react';

// Debounced value hook for search inputs
export function useDebouncedValue<T>(value: T, delay: number): T {
    const [debouncedValue, setDebouncedValue] = useState<T>(value);

    useEffect(() => {
        const handler = setTimeout(() => {
            setDebouncedValue(value);
        }, delay);

        return () => {
            clearTimeout(handler);
        };
    }, [value, delay]);

    return debouncedValue;
}

// Auto-save hook for form drafts
export function useAutoSave<T>({
    data,
    onSave,
    interval = 30000, // 30 seconds
    enabled = true,
}: {
    data: T;
    onSave: (data: T) => void | Promise<void>;
    interval?: number;
    enabled?: boolean;
}) {
    const [isSaving, setIsSaving] = useState(false);
    const [lastSaved, setLastSaved] = useState<Date | null>(null);
    const dataRef = useRef(data);
    const onSaveRef = useRef(onSave);

    // Update refs when props change
    useEffect(() => {
        dataRef.current = data;
    }, [data]);

    useEffect(() => {
        onSaveRef.current = onSave;
    }, [onSave]);

    const save = useCallback(async () => {
        if (!enabled) return;

        setIsSaving(true);
        try {
            await onSaveRef.current(dataRef.current);
            setLastSaved(new Date());
        } finally {
            setIsSaving(false);
        }
    }, [enabled]);

    // Auto-save on interval
    useEffect(() => {
        if (!enabled) return;

        const intervalId = setInterval(save, interval);
        return () => clearInterval(intervalId);
    }, [enabled, interval, save]);

    return { isSaving, lastSaved, save };
}

// Form validation hook
export function useFormValidation<T extends Record<string, unknown>>(
    schema: {
        [K in keyof T]?: (value: T[K]) => string | null;
    }
) {
    type Errors = Partial<Record<keyof T, string>>;

    const [errors, setErrors] = useState<Errors>({});
    const [touched, setTouched] = useState<Partial<Record<keyof T, boolean>>>({});

    const validate = useCallback(
        (data: T): boolean => {
            const newErrors: Errors = {};
            let isValid = true;

            for (const key in schema) {
                const validator = schema[key as keyof T];
                if (validator) {
                    const error = validator(data[key as keyof T]);
                    if (error) {
                        newErrors[key as keyof T] = error;
                        isValid = false;
                    }
                }
            }

            setErrors(newErrors);
            return isValid;
        },
        [schema]
    );

    const validateField = useCallback(
        (field: keyof T, value: T[keyof T]) => {
            const validator = schema[field];
            if (validator) {
                const error = validator(value);
                setErrors((prev) => ({
                    ...prev,
                    [field]: error ?? undefined,
                }));
                return !error;
            }
            return true;
        },
        [schema]
    );

    const touchField = useCallback((field: keyof T) => {
        setTouched((prev) => ({ ...prev, [field]: true }));
    }, []);

    const reset = useCallback(() => {
        setErrors({});
        setTouched({});
    }, []);

    return {
        errors,
        touched,
        validate,
        validateField,
        touchField,
        reset,
    };
}

// Multi-step form wizard hook
export function useWizardForm<T extends string>(steps: T[]) {
    const [currentStep, setCurrentStep] = useState<T>(steps[0]);
    const [completedSteps, setCompletedSteps] = useState<Set<T>>(new Set());

    const currentStepIndex = steps.indexOf(currentStep);
    const isFirstStep = currentStepIndex === 0;
    const isLastStep = currentStepIndex === steps.length - 1;

    const goToStep = useCallback((step: T) => {
        setCurrentStep(step);
    }, []);

    const nextStep = useCallback(() => {
        const nextIndex = currentStepIndex + 1;
        if (nextIndex < steps.length) {
            setCompletedSteps((prev) => new Set([...prev, currentStep]));
            setCurrentStep(steps[nextIndex]);
        }
    }, [currentStep, currentStepIndex, steps]);

    const previousStep = useCallback(() => {
        const prevIndex = currentStepIndex - 1;
        if (prevIndex >= 0) {
            setCurrentStep(steps[prevIndex]);
        }
    }, [currentStepIndex, steps]);

    const markStepComplete = useCallback((step: T) => {
        setCompletedSteps((prev) => new Set([...prev, step]));
    }, []);

    const isStepCompleted = useCallback(
        (step: T) => completedSteps.has(step),
        [completedSteps]
    );

    const progress = ((currentStepIndex + 1) / steps.length) * 100;

    return {
        currentStep,
        currentStepIndex,
        isFirstStep,
        isLastStep,
        progress,
        goToStep,
        nextStep,
        previousStep,
        markStepComplete,
        isStepCompleted,
        steps,
    };
}

// Confirmation dialog hook
export function useConfirmDialog({
    onConfirm,
    onCancel,
}: {
    onConfirm?: () => void;
    onCancel?: () => void;
}) {
    const [isOpen, setIsOpen] = useState(false);
    const [isLoading, setIsLoading] = useState(false);
    const [data, setData] = useState<unknown>(null);

    const open = useCallback((data?: unknown) => {
        setData(data ?? null);
        setIsOpen(true);
    }, []);

    const close = useCallback(() => {
        setIsOpen(false);
        setData(null);
        onCancel?.();
    }, [onCancel]);

    const confirm = useCallback(async () => {
        setIsLoading(true);
        try {
            await onConfirm?.();
            setIsOpen(false);
            setData(null);
        } finally {
            setIsLoading(false);
        }
    }, [onConfirm]);

    return {
        isOpen,
        isLoading,
        data,
        open,
        close,
        confirm,
    };
}
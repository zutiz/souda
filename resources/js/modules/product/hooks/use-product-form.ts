import { useCallback, useMemo, useState } from 'react';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { router } from '@inertiajs/react';
import {
    productFormSchema,
    defaultProductFormValues,
    type ProductFormData,
    type ProductFormOutput,
} from '../types/product-form';

type FormErrors = Partial<Record<string, string>>;

type Options = {
    initialData?: Partial<ProductFormData>;
    route: string;
    method?: 'post' | 'put';
    onSuccess?: () => void;
    onError?: (errors: FormErrors) => void;
};

export function useProductForm({ initialData, route, method = 'post', onSuccess, onError }: Options) {
    const [processing, setProcessing] = useState(false);
    const [serverErrors, setServerErrors] = useState<FormErrors>({});

    const form = useForm<ProductFormData>({
        resolver: zodResolver(productFormSchema),
        defaultValues: { ...defaultProductFormValues, ...initialData },
    });

    const errors = useMemo<FormErrors>(() => {
        const merged: FormErrors = { ...serverErrors };

        for (const [key, value] of Object.entries(form.formState.errors)) {
            if (value?.message) {
                merged[key] = value.message;
            }
        }

        return merged;
    }, [serverErrors, form.formState.errors]);

    const onChange = useCallback(
        <K extends keyof ProductFormData>(field: K, value: ProductFormData[K]) => {
            form.setValue(field, value, { shouldValidate: false, shouldDirty: true });
            setServerErrors((prev) => {
                const next = { ...prev };
                delete next[field];
                return next;
            });
        },
        [form],
    );

    const handleSubmit = useCallback(
        (e?: React.BaseSyntheticEvent) => {
            e?.preventDefault();

            form.handleSubmit(
                (data: ProductFormOutput) => {
                    setProcessing(true);
                    setServerErrors({});

                    const submit = method === 'put' ? router.put : router.post;
                    const payload = data as unknown as Record<string, unknown>;

                    submit(route, payload, {
                        preserveScroll: true,
                        onSuccess: () => {
                            setProcessing(false);
                            onSuccess?.();
                        },
                        onError: (inertiaErrors) => {
                            setProcessing(false);
                            const mapped = mapServerErrors(inertiaErrors);
                            setServerErrors(mapped);
                            onError?.(mapped);
                        },
                        onFinish: () => {
                            setProcessing(false);
                        },
                    });
                },
                () => {},
            )(e);
        },
        [form, route, method, onSuccess, onError],
    );

    const resetForm = useCallback(
        (data?: Partial<ProductFormData>) => {
            form.reset({ ...defaultProductFormValues, ...data });
            setServerErrors({});
        },
        [form],
    );

    return {
        form,
        errors,
        processing,
        onChange,
        handleSubmit,
        resetForm,
    };
}

function mapServerErrors(inertiaErrors: Record<string, string>): FormErrors {
    const mapped: FormErrors = {};

    for (const [key, message] of Object.entries(inertiaErrors)) {
        const cleanKey = key
            .replace(/\.\d+\./, '.')
            .replace(/\.\d+$/, '');
        mapped[cleanKey] = message;
    }

    return mapped;
}

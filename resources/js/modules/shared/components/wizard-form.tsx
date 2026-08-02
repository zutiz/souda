import { type ReactNode, useState, useCallback } from 'react';
import { Check, ChevronLeft, ChevronRight, Save } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

type WizardStep = {
    id: string;
    title: string;
    description?: string;
    icon?: React.ComponentType<{ className?: string }>;
};

type WizardFormProps = {
    steps: WizardStep[];
    currentStep: string;
    onStepChange: (stepId: string) => void;
    onNext?: () => void | Promise<void>;
    onPrevious?: () => void;
    onSaveDraft?: () => void | Promise<void>;
    children: ReactNode;
    className?: string;
    nextLabel?: string;
    previousLabel?: string;
    saveDraftLabel?: string;
    isLoading?: boolean;
    showSaveDraft?: boolean;
};

export function WizardForm({
    steps,
    currentStep,
    onStepChange,
    onNext,
    onPrevious,
    onSaveDraft,
    children,
    className,
    nextLabel = 'Next',
    previousLabel = 'Previous',
    saveDraftLabel = 'Save Draft',
    isLoading = false,
    showSaveDraft = true,
}: WizardFormProps) {
    const currentStepIndex = steps.findIndex((s) => s.id === currentStep);
    const isFirstStep = currentStepIndex === 0;
    const isLastStep = currentStepIndex === steps.length - 1;

    const handleNext = useCallback(async () => {
        if (onNext) {
            await onNext();
        } else {
            const nextIndex = currentStepIndex + 1;
            if (nextIndex < steps.length) {
                onStepChange(steps[nextIndex].id);
            }
        }
    }, [currentStepIndex, steps, onNext, onStepChange]);

    const handlePrevious = useCallback(() => {
        if (onPrevious) {
            onPrevious();
        } else {
            const prevIndex = currentStepIndex - 1;
            if (prevIndex >= 0) {
                onStepChange(steps[prevIndex].id);
            }
        }
    }, [currentStepIndex, steps, onPrevious, onStepChange]);

    return (
        <div className={cn('flex flex-col gap-6', className)}>
            {/* Progress Indicator */}
            <div className="flex items-center justify-between">
                {steps.map((step, index) => {
                    const isCompleted = index < currentStepIndex;
                    const isCurrent = step.id === currentStep;
                    const Icon = step.icon;

                    return (
                        <div key={step.id} className="flex items-center">
                            {/* Step indicator */}
                            <button
                                type="button"
                                onClick={() => !isCompleted && onStepChange(step.id)}
                                disabled={isCompleted}
                                className={cn(
                                    'flex items-center justify-center size-8 rounded-full border-2 transition-colors',
                                    isCompleted && 'bg-primary border-primary text-primary-foreground cursor-pointer',
                                    isCurrent && 'border-primary bg-primary/10 text-primary',
                                    !isCompleted && !isCurrent && 'border-muted-foreground/30 text-muted-foreground'
                                )}
                            >
                                {isCompleted ? (
                                    <Check className="size-4" />
                                ) : Icon ? (
                                    <Icon className="size-4" />
                                ) : (
                                    <span className="text-sm font-medium">{index + 1}</span>
                                )}
                            </button>

                            {/* Step label */}
                            <span
                                className={cn(
                                    'ml-2 text-sm font-medium hidden sm:inline',
                                    isCurrent && 'text-foreground',
                                    !isCurrent && 'text-muted-foreground'
                                )}
                            >
                                {step.title}
                            </span>

                            {/* Connector line */}
                            {index < steps.length - 1 && (
                                <div
                                    className={cn(
                                        'w-8 sm:w-16 h-0.5 mx-2',
                                        index < currentStepIndex ? 'bg-primary' : 'bg-muted'
                                    )}
                                />
                            )}
                        </div>
                    );
                })}
            </div>

            {/* Step Content */}
            <div className="rounded-lg border bg-card">
                <div className="p-6">
                    {/* Step Header */}
                    <div className="mb-6">
                        <h2 className="text-xl font-semibold">
                            {steps[currentStepIndex]?.title}
                        </h2>
                        {steps[currentStepIndex]?.description && (
                            <p className="mt-1 text-muted-foreground text-sm">
                                {steps[currentStepIndex].description}
                            </p>
                        )}
                    </div>

                    {/* Children */}
                    {children}
                </div>

                {/* Navigation Footer */}
                <div className="border-t bg-muted/30 px-6 py-4">
                    <div className="flex items-center justify-between">
                        <div>
                            {!isFirstStep && (
                                <Button
                                    type="button"
                                    variant="ghost"
                                    onClick={handlePrevious}
                                    disabled={isLoading}
                                    className="gap-2"
                                >
                                    <ChevronLeft className="size-4" />
                                    {previousLabel}
                                </Button>
                            )}
                        </div>

                        <div className="flex items-center gap-2">
                            {showSaveDraft && onSaveDraft && !isFirstStep && (
                                <Button
                                    type="button"
                                    variant="outline"
                                    onClick={onSaveDraft}
                                    disabled={isLoading}
                                    className="gap-2"
                                >
                                    <Save className="size-4" />
                                    {saveDraftLabel}
                                </Button>
                            )}

                            {!isLastStep && (
                                <Button
                                    type="button"
                                    onClick={handleNext}
                                    disabled={isLoading}
                                    className="gap-2"
                                >
                                    {nextLabel}
                                    <ChevronRight className="size-4" />
                                </Button>
                            )}

                            {isLastStep && (
                                <Button
                                    type="submit"
                                    disabled={isLoading}
                                    className="gap-2"
                                >
                                    {isLoading ? 'Saving...' : 'Complete'}
                                    <Check className="size-4" />
                                </Button>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}

// Compact step indicator for simpler forms
export function WizardSteps({
    steps,
    currentStep,
    onStepChange,
    className,
}: {
    steps: WizardStep[];
    currentStep: string;
    onStepChange: (stepId: string) => void;
    className?: string;
}) {
    const currentStepIndex = steps.findIndex((s) => s.id === currentStep);

    return (
        <div className={cn('flex items-center gap-2', className)}>
            {steps.map((step, index) => {
                const isCompleted = index < currentStepIndex;
                const isCurrent = step.id === currentStep;

                return (
                    <div key={step.id} className="flex items-center">
                        <button
                            type="button"
                            onClick={() => !isCompleted && onStepChange(step.id)}
                            className={cn(
                                'flex items-center gap-1.5 text-sm font-medium transition-colors',
                                isCurrent && 'text-foreground',
                                isCompleted && 'text-primary cursor-pointer hover:text-primary/80',
                                !isCompleted && !isCurrent && 'text-muted-foreground'
                            )}
                        >
                            <span
                                className={cn(
                                    'flex size-5 items-center justify-center rounded-full text-xs',
                                    isCompleted && 'bg-primary text-primary-foreground',
                                    isCurrent && 'bg-primary/10 text-primary border border-primary',
                                    !isCompleted && !isCurrent && 'bg-muted text-muted-foreground'
                                )}
                            >
                                {isCompleted ? <Check className="size-3" /> : index + 1}
                            </span>
                            <span className="hidden sm:inline">{step.title}</span>
                        </button>

                        {index < steps.length - 1 && (
                            <ChevronRight className="mx-1 size-4 text-muted-foreground/50 hidden sm:inline" />
                        )}
                    </div>
                );
            })}
        </div>
    );
}
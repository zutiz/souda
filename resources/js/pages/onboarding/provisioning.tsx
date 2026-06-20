import { Head } from '@inertiajs/react';
import { useEffect, useState, useRef } from 'react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Spinner } from '@/components/ui/spinner';
import AuthLayout from '@/layouts/auth-layout';

type BusinessType = {
    id: number;
    slug: string;
    name: string;
    description: string;
    icon: string | null;
};

type Props = {
    businessType: BusinessType | null;
    tenantId: string | null;
};

const stepLabels = [
    'Initializing workspace',
    'Assigning business type',
    'Enabling modules',
    'Creating permissions and roles',
    'Seeding default data',
    'Configuring product fields',
    'Setting up dashboard',
    'Configuring point of sale',
    'Setting up team',
    'Finalizing configuration',
];

export default function Provisioning({ businessType, tenantId }: Props) {
    const [currentStep, setCurrentStep] = useState(0);
    const [status, setStatus] = useState('Starting provisioning...');
    const [complete, setComplete] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const pollingRef = useRef<ReturnType<typeof setInterval> | null>(null);

    useEffect(() => {
        async function startProvisioning() {
            try {
                const response = await fetch('/onboarding/run', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                });
                const data = await response.json();

                if (data.status === 'completed') {
                    setCurrentStep(stepLabels.length);
                    setStatus('Complete!');
                    setComplete(true);
                    setTimeout(() => { window.location.href = data.redirect; }, 1500);
                    return;
                }

                if (data.status === 'already_completed') {
                    window.location.href = data.redirect;
                    return;
                }

                if (data.status === 'failed') {
                    setError(data.error ?? 'Provisioning failed.');
                    return;
                }
            } catch (err) {
                setError('Failed to start provisioning.');
            }
        }

        startProvisioning();

        // Poll for progress if provisioning is handled asynchronously
        if (tenantId) {
            pollingRef.current = setInterval(async () => {
                try {
                    const response = await fetch(`/onboarding/${tenantId}/progress`);
                    const data = await response.json();

                    if (data.status === 'completed') {
                        setCurrentStep(stepLabels.length);
                        setStatus('Complete!');
                        setComplete(true);
                        if (pollingRef.current) clearInterval(pollingRef.current);
                        setTimeout(() => { window.location.href = '/dashboard'; }, 1500);
                        return;
                    }

                    if (data.status === 'failed') {
                        setError('Provisioning failed.');
                        if (pollingRef.current) clearInterval(pollingRef.current);
                        return;
                    }

                    if (data.progress?.length > 0) {
                        const lastStep = data.progress[data.progress.length - 1];
                        if (lastStep.index !== undefined) {
                            setCurrentStep(lastStep.index + 1);
                            setStatus(stepLabels[lastStep.index + 1] ?? 'Completing setup...');
                        }
                    }
                } catch {
                    // Polling error, ignore
                }
            }, 2000);
        }

        return () => {
            if (pollingRef.current) clearInterval(pollingRef.current);
        };
    }, []);

    const progress = Math.round((currentStep / stepLabels.length) * 100);

    return (
        <AuthLayout
            title={businessType ? `Setting up your ${businessType.name}` : 'Setting up your workspace'}
            description="We're configuring everything for your business type."
        >
            <Head title="Setting up" />

            <Card>
                <CardHeader>
                    <CardTitle>{businessType?.name ?? 'Workspace'} Setup</CardTitle>
                    <CardDescription>{status}</CardDescription>
                </CardHeader>
                <CardContent className="space-y-6">
                    <div className="h-2 w-full overflow-hidden rounded-full bg-neutral-200 dark:bg-neutral-700">
                        <div
                            className="h-full rounded-full bg-primary transition-all duration-500"
                            style={{ width: `${progress}%` }}
                        />
                    </div>

                    <div className="space-y-2">
                        {stepLabels.map((label, i) => (
                            <div key={i} className="flex items-center gap-3 text-sm">
                                <div
                                    className={`flex size-6 shrink-0 items-center justify-center rounded-full text-xs font-medium ${
                                        i < currentStep
                                            ? 'bg-primary text-primary-foreground'
                                            : i === currentStep
                                              ? 'border-2 border-primary text-primary'
                                              : 'bg-neutral-100 text-neutral-400 dark:bg-neutral-800'
                                    }`}
                                >
                                    {i < currentStep ? '✓' : i + 1}
                                </div>
                                <span
                                    className={
                                        i <= currentStep
                                            ? 'text-foreground'
                                            : 'text-muted-foreground'
                                    }
                                >
                                    {label}
                                </span>
                            </div>
                        ))}
                    </div>

                    {error && (
                        <div className="rounded-md border border-destructive/30 bg-destructive/5 p-3 text-sm text-destructive">
                            {error}
                            <div className="mt-2">
                                <Button
                                    variant="outline"
                                    size="sm"
                                    onClick={() => window.location.reload()}
                                >
                                    Try Again
                                </Button>
                            </div>
                        </div>
                    )}

                    {complete && (
                        <p className="text-center text-sm text-green-600 dark:text-green-400">
                            Redirecting to dashboard...
                        </p>
                    )}
                </CardContent>
            </Card>
        </AuthLayout>
    );
}

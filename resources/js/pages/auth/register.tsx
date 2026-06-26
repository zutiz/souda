import { Form, Head } from '@inertiajs/react';
import { Github } from 'lucide-react';
import { useState } from 'react';
import { redirect as socialRedirect } from '@/actions/App/Http/Controllers/Auth/SocialAuthController';
import InputError from '@/components/input-error';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthLayout from '@/layouts/auth-layout';
import { login } from '@/routes';
import { store } from '@/routes/register';

type Props = {
    socialProviders: Array<{
        key: string;
        label: string;
    }>;
    businessTypes?: Array<{
        id: number;
        slug: string;
        name: string;
        description: string;
        icon: string | null;
    }>;
};

function SocialProviderIcon({ providerKey }: { providerKey: string }) {
    if (providerKey === 'github') {
        return <Github className="size-4" />;
    }

    if (providerKey === 'google') {
        return (
            <svg aria-hidden="true" viewBox="0 0 24 24" className="size-4">
                <path
                    fill="#4285F4"
                    d="M21.35 11.1H12v2.98h5.35c-.23 1.5-1.74 4.4-5.35 4.4-3.22 0-5.85-2.67-5.85-5.97s2.63-5.97 5.85-5.97c1.83 0 3.06.78 3.76 1.46l2.56-2.47C16.7 3.99 14.57 3 12 3 7.03 3 3 7.03 3 12s4.03 9 9 9 8.64-3.52 8.64-8.48c0-.57-.06-1-.14-1.42z"
                />
                <path
                    fill="#34A853"
                    d="M3 7.5l2.45 1.8C6.1 7.24 8.87 5.7 12 5.7c1.83 0 3.06.78 3.76 1.46l2.56-2.47C16.7 3.99 14.57 3 12 3 8.04 3 4.6 5.26 3 8.57V7.5z"
                />
                <path
                    fill="#FBBC05"
                    d="M12 21c2.5 0 4.6-.82 6.13-2.24l-2.83-2.31c-.76.53-1.74.9-3.3.9-3.59 0-5.1-2.43-5.31-3.87L3.22 15.1C4.79 18.48 8.16 21 12 21z"
                />
                <path
                    fill="#EA4335"
                    d="M21.35 11.1H12v2.98h5.35c-.23 1.5-1.74 4.4-5.35 4.4-3.59 0-5.1-2.43-5.31-3.87L3.22 15.1C4.79 18.48 8.16 21 12 21c4.97 0 8.64-3.52 8.64-8.48 0-.57-.06-1-.14-1.42z"
                />
            </svg>
        );
    }

    return null;
}

export default function Register({ socialProviders, businessTypes = [] }: Props) {
    const [selectedType, setSelectedType] = useState<string>('');

    return (
        <AuthLayout
            title="Create an account"
            description="Enter your details below to create your account"
        >
            <Head title="Register" />
            <Form
                {...store.form()}
                resetOnSuccess={['password', 'password_confirmation']}
                disableWhileProcessing
                className="flex flex-col gap-6"
            >
                {({ processing, errors }) => (
                    <>
                        {socialProviders.length > 0 && (
                            <div className="grid gap-2">
                                {socialProviders.map((provider) => (
                                    <Button
                                        key={provider.key}
                                        type="button"
                                        variant="outline"
                                        className="w-full gap-2"
                                        onClick={() => {
                                            window.location.assign(
                                                socialRedirect(provider.key)
                                                    .url,
                                            );
                                        }}
                                    >
                                        <SocialProviderIcon
                                            providerKey={provider.key}
                                        />
                                        Continue with {provider.label}
                                    </Button>
                                ))}
                            </div>
                        )}

                        {socialProviders.length > 0 && (
                            <div className="relative">
                                <div className="absolute inset-0 flex items-center">
                                    <span className="w-full border-t" />
                                </div>
                                <div className="relative flex justify-center text-xs uppercase">
                                    <span className="bg-background px-2 text-muted-foreground">
                                        or create with email
                                    </span>
                                </div>
                            </div>
                        )}

                        <div className="grid gap-6">
                            <div className="grid gap-2">
                                <Label htmlFor="name">Name</Label>
                                <Input
                                    id="name"
                                    type="text"
                                    required
                                    autoFocus
                                    tabIndex={1}
                                    autoComplete="name"
                                    name="name"
                                    placeholder="Full name"
                                />
                                <InputError
                                    message={errors.name}
                                    className="mt-2"
                                />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="email">Email address</Label>
                                <Input
                                    id="email"
                                    type="email"
                                    required
                                    tabIndex={2}
                                    autoComplete="email"
                                    name="email"
                                    placeholder="email@example.com"
                                />
                                <InputError message={errors.email} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="password">Password</Label>
                                <Input
                                    id="password"
                                    type="password"
                                    required
                                    tabIndex={3}
                                    autoComplete="new-password"
                                    name="password"
                                    placeholder="Password"
                                />
                                <InputError message={errors.password} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="password_confirmation">
                                    Confirm password
                                </Label>
                                <Input
                                    id="password_confirmation"
                                    type="password"
                                    required
                                    tabIndex={4}
                                    autoComplete="new-password"
                                    name="password_confirmation"
                                    placeholder="Confirm password"
                                />
                                <InputError
                                    message={errors.password_confirmation}
                                />
                            </div>

                            {businessTypes.length > 0 && (
                                <div className="grid gap-2">
                                    <Label>Business Type</Label>
                                    <select
                                        name="business_type_slug"
                                        value={selectedType}
                                        onChange={(e) => setSelectedType(e.target.value)}
                                        className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                        tabIndex={5}
                                    >
                                        <option value="">Select your industry...</option>
                                        {businessTypes.map((type) => (
                                            <option key={type.id} value={type.slug}>
                                                {type.name}
                                            </option>
                                        ))}
                                    </select>
                                    <InputError message={errors.business_type_slug} />
                                </div>
                            )}

                            <Button
                                type="submit"
                                className="mt-2 w-full"
                                tabIndex={6}
                                data-test="register-user-button"
                            >
                                {processing && <Spinner />}
                                Create account
                            </Button>
                        </div>

                        <div className="text-center text-sm text-muted-foreground">
                            Already have an account?{' '}
                            <TextLink href={login()} tabIndex={7}>
                                Log in
                            </TextLink>
                        </div>
                    </>
                )}
            </Form>
        </AuthLayout>
    );
}

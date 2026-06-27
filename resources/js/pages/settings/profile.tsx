import { Transition } from '@headlessui/react';
import { Form, Head, Link, usePage } from '@inertiajs/react';
import { useState } from 'react';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import { BusinessTypeSelect } from '@/components/business-type-select';
import DeleteUser from '@/components/delete-user';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { SingleImageUpload } from '@/components/single-image-upload';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { update as updateBusiness } from '@/routes/business';
import { edit } from '@/routes/profile';
import { send } from '@/routes/verification';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Profile settings',
        href: edit().url,
    },
];

interface BusinessType {
    id: number;
    slug: string;
    name: string;
    description: string;
    icon: string | null;
}

export default function Profile({
    mustVerifyEmail,
    status,
}: {
    mustVerifyEmail: boolean;
    status?: string;
}) {
    const { props } = usePage<{
        auth: { user: { name: string; email: string; email_verified_at: string | null; avatar_url?: string | null }; is_admin: boolean };
        currentTenant?: { id: string; name: string; business_type?: string; business_type_id?: number; logo?: string | null } | null;
        businessTypes?: BusinessType[];
    }>();
    const { auth, currentTenant, businessTypes = [] } = props;

    const [selectedType, setSelectedType] = useState<string>(currentTenant?.business_type ?? '');
    const [avatarFile, setAvatarFile] = useState<File | null>(null);
    const [logoFile, setLogoFile] = useState<File | null>(null);
    const [removeAvatar, setRemoveAvatar] = useState(false);
    const [removeLogo, setRemoveLogo] = useState(false);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Profile settings" />

            <h1 className="sr-only">Profile Settings</h1>

            <SettingsLayout>
                <div className="space-y-6">
                    <Heading
                        variant="small"
                        title="Profile information"
                        description="Update your name, email, and photo"
                    />

                    <Form
                        {...ProfileController.update.form()}
                        options={{
                            preserveScroll: true,
                        }}
                        className="space-y-6"
                    >
                        {({ processing, recentlySuccessful, errors, setData }) => (
                            <>
                                <div className="grid gap-2">
                                    <Label htmlFor="name">Name</Label>

                                    <Input
                                        id="name"
                                        className="mt-1 block w-full"
                                        defaultValue={auth.user.name}
                                        name="name"
                                        required
                                        autoComplete="name"
                                        placeholder="Full name"
                                    />

                                    <InputError
                                        className="mt-2"
                                        message={errors.name}
                                    />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="email">Email address</Label>

                                    <Input
                                        id="email"
                                        type="email"
                                        className="mt-1 block w-full"
                                        defaultValue={auth.user.email}
                                        name="email"
                                        required
                                        autoComplete="username"
                                        placeholder="Email address"
                                    />

                                    <InputError
                                        className="mt-2"
                                        message={errors.email}
                                    />
                                </div>

                                <div className="grid gap-2">
                                    <Label>Photo</Label>
                                    <SingleImageUpload
                                        currentImage={removeAvatar ? null : auth.user.avatar_url}
                                        onFileChange={(file) => {
                                            setAvatarFile(file);
                                            setRemoveAvatar(false);
                                            if (file) {
                                                setData('avatar', file);
                                            }
                                        }}
                                        label="Upload photo"
                                    />
                                    {auth.user.avatar_url && !removeAvatar && (
                                        <button
                                            type="button"
                                            onClick={() => {
                                                setRemoveAvatar(true);
                                                setAvatarFile(null);
                                                setData('remove_avatar', '1');
                                            }}
                                            className="mt-1 text-left text-sm text-muted-foreground hover:text-destructive"
                                        >
                                            Remove photo
                                        </button>
                                    )}
                                    {removeAvatar && (
                                        <span className="mt-1 text-sm text-muted-foreground">
                                            Photo will be removed on save.{' '}
                                            <button
                                                type="button"
                                                onClick={() => {
                                                    setRemoveAvatar(false);
                                                    setData('remove_avatar', '');
                                                }}
                                                className="underline hover:text-foreground"
                                            >
                                                Undo
                                            </button>
                                        </span>
                                    )}
                                    <InputError message={errors.avatar} />
                                </div>

                                {mustVerifyEmail &&
                                    auth.user.email_verified_at === null && (
                                        <div>
                                            <p className="-mt-4 text-sm text-muted-foreground">
                                                Your email address is
                                                unverified.{' '}
                                                <Link
                                                    href={send()}
                                                    as="button"
                                                    className="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                                                >
                                                    Click here to resend the
                                                    verification email.
                                                </Link>
                                            </p>

                                            {status ===
                                                'verification-link-sent' && (
                                                <div className="mt-2 text-sm font-medium text-green-600">
                                                    A new verification link has
                                                    been sent to your email
                                                    address.
                                                </div>
                                            )}
                                        </div>
                                    )}

                                <div className="flex items-center gap-4">
                                    <Button
                                        disabled={processing}
                                        data-test="update-profile-button"
                                    >
                                        Save
                                    </Button>

                                    <Transition
                                        show={recentlySuccessful}
                                        enter="transition ease-in-out"
                                        enterFrom="opacity-0"
                                        leave="transition ease-in-out"
                                        leaveTo="opacity-0"
                                    >
                                        <p className="text-sm text-neutral-600">
                                            Saved
                                        </p>
                                    </Transition>
                                </div>
                            </>
                        )}
                    </Form>
                </div>

                <div className="space-y-6">
                    <Heading
                        variant="small"
                        title="Business information"
                        description="Update your business name, type, and logo"
                    />

                    <Form
                        {...updateBusiness.form()}
                        options={{
                            preserveScroll: true,
                        }}
                        className="space-y-6"
                    >
                        {({ processing, recentlySuccessful, errors, setData }) => (
                            <>
                                <div className="grid gap-2">
                                    <Label htmlFor="business_name">Business Name</Label>
                                    <Input
                                        id="business_name"
                                        className="mt-1 block w-full"
                                        defaultValue={currentTenant?.name ?? ''}
                                        name="name"
                                        required
                                        autoComplete="organization"
                                        placeholder="Business name"
                                    />
                                    <InputError
                                        className="mt-2"
                                        message={errors.name}
                                    />
                                </div>

                                {businessTypes.length > 0 && (
                                    <div className="grid gap-2">
                                        <Label>Business Type</Label>
                                        <BusinessTypeSelect
                                            businessTypes={businessTypes}
                                            value={selectedType}
                                            onValueChange={(val) => {
                                                setSelectedType(val);
                                                setData('business_type_slug', val);
                                            }}
                                        />
                                        <input type="hidden" name="business_type_slug" value={selectedType} />
                                        <InputError message={errors.business_type_slug} />
                                    </div>
                                )}

                                <div className="grid gap-2">
                                    <Label>Business Logo</Label>
                                    <SingleImageUpload
                                        currentImage={removeLogo ? null : currentTenant?.logo}
                                        onFileChange={(file) => {
                                            setLogoFile(file);
                                            setRemoveLogo(false);
                                            if (file) {
                                                setData('logo', file);
                                            }
                                        }}
                                        label="Upload logo"
                                    />
                                    {currentTenant?.logo && !removeLogo && (
                                        <button
                                            type="button"
                                            onClick={() => {
                                                setRemoveLogo(true);
                                                setLogoFile(null);
                                                setData('remove_logo', '1');
                                            }}
                                            className="mt-1 text-left text-sm text-muted-foreground hover:text-destructive"
                                        >
                                            Remove logo
                                        </button>
                                    )}
                                    {removeLogo && (
                                        <span className="mt-1 text-sm text-muted-foreground">
                                            Logo will be removed on save.{' '}
                                            <button
                                                type="button"
                                                onClick={() => {
                                                    setRemoveLogo(false);
                                                    setData('remove_logo', '');
                                                }}
                                                className="underline hover:text-foreground"
                                            >
                                                Undo
                                            </button>
                                        </span>
                                    )}
                                    <InputError message={errors.logo} />
                                </div>

                                <div className="flex items-center gap-4">
                                    <Button
                                        disabled={processing}
                                    >
                                        Save business
                                    </Button>

                                    <Transition
                                        show={recentlySuccessful}
                                        enter="transition ease-in-out"
                                        enterFrom="opacity-0"
                                        leave="transition ease-in-out"
                                        leaveTo="opacity-0"
                                    >
                                        <p className="text-sm text-neutral-600">
                                            Saved
                                        </p>
                                    </Transition>
                                </div>
                            </>
                        )}
                    </Form>
                </div>

                {!auth.is_admin && <DeleteUser />}
            </SettingsLayout>
        </AppLayout>
    );
}

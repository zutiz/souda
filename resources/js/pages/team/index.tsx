import { Form, Head, router } from '@inertiajs/react';
import { Mail, RefreshCw, Trash2, UserPlus, X } from 'lucide-react';
import { useState } from 'react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';

type Member = {
    id: number;
    user_id: number | null;
    email: string | null;
    name: string | null;
    seat_type: string;
    status: string;
    invitation_token: string | null;
    allocated_at: string | null;
    released_at: string | null;
};

type Props = {
    members: Member[];
};

const statusLabels: Record<string, string> = {
    active: 'Active',
    pending: 'Pending',
    released: 'Released',
};

const statusVariants: Record<string, string> = {
    active: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
    pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
    released: 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400',
};

const seatTypeLabels: Record<string, string> = {
    owner: 'Owner',
    admin: 'Admin',
    staff: 'Staff',
};

export default function Team({ members }: Props) {
    const [inviteOpen, setInviteOpen] = useState(false);
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Team', href: '/team' },
    ];

    function handleRemove(id: number) {
        router.delete(`/team/${id}`, { preserveScroll: true });
    }

    function handleResend(id: number) {
        router.post(`/team/${id}/resend`, {}, { preserveScroll: true });
    }

    const activeMembers = members.filter((m) => m.status !== 'released');
    const releasedMembers = members.filter((m) => m.status === 'released');

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Team" />
            <div className="mx-auto w-full max-w-2xl space-y-6 p-4 sm:p-6">
                <div className="flex items-center justify-between">
                    <Heading
                        title="Team"
                        description="Manage your team members and invitations."
                    />
                    <Dialog open={inviteOpen} onOpenChange={setInviteOpen}>
                        <DialogTrigger asChild>
                            <Button>
                                <UserPlus className="mr-2 size-4" />
                                Invite Member
                            </Button>
                        </DialogTrigger>
                        <DialogContent>
                            <DialogHeader>
                                <DialogTitle>Invite Team Member</DialogTitle>
                                <DialogDescription>
                                    Send an invitation to join your team.
                                </DialogDescription>
                            </DialogHeader>
                            <Form
                                action="/team/invite"
                                method="post"
                                resetOnSuccess
                                onSuccess={() => setInviteOpen(false)}
                                className="space-y-4"
                            >
                                {({ processing, errors }) => (
                                    <>
                                        <div className="grid gap-2">
                                            <Label htmlFor="email">
                                                Email address
                                            </Label>
                                            <Input
                                                id="email"
                                                name="email"
                                                type="email"
                                                placeholder="colleague@example.com"
                                                required
                                            />
                                            <InputError
                                                message={errors.email}
                                            />
                                        </div>
                                        <div className="grid gap-2">
                                            <Label htmlFor="seat_type">
                                                Role
                                            </Label>
                                            <select
                                                id="seat_type"
                                                name="seat_type"
                                                className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                            >
                                                <option value="admin">
                                                    Admin
                                                </option>
                                                <option value="staff">
                                                    Staff
                                                </option>
                                            </select>
                                            <InputError
                                                message={errors.seat_type}
                                            />
                                        </div>
                                        <DialogFooter>
                                            <DialogClose asChild>
                                                <Button
                                                    type="button"
                                                    variant="outline"
                                                >
                                                    Cancel
                                                </Button>
                                            </DialogClose>
                                            <Button
                                                type="submit"
                                                disabled={processing}
                                            >
                                                {processing && <Spinner />}
                                                Send Invitation
                                            </Button>
                                        </DialogFooter>
                                    </>
                                )}
                            </Form>
                        </DialogContent>
                    </Dialog>
                </div>

                {activeMembers.length === 0 ? (
                    <p className="py-8 text-center text-muted-foreground">
                        No team members yet. Invite someone to get started.
                    </p>
                ) : (
                    <ul className="space-y-2">
                        {activeMembers.map((member) => (
                            <li
                                key={member.id}
                                className="flex items-center gap-4 rounded-lg border bg-card p-4"
                            >
                                <div className="flex size-10 shrink-0 items-center justify-center rounded-full bg-primary/10">
                                    <span className="text-sm font-semibold text-primary">
                                        {(member.name ?? member.email ?? '?')
                                            .charAt(0)
                                            .toUpperCase()}
                                    </span>
                                </div>
                                <div className="min-w-0 flex-1">
                                    <p className="truncate font-medium">
                                        {member.name ?? 'Pending invitation'}
                                    </p>
                                    <p className="truncate text-sm text-muted-foreground">
                                        {member.email}
                                    </p>
                                </div>
                                <Badge
                                    className={statusVariants[member.status]}
                                >
                                    {statusLabels[member.status]}
                                </Badge>
                                <span className="text-sm text-muted-foreground">
                                    {seatTypeLabels[member.seat_type] ??
                                        member.seat_type}
                                </span>
                                {member.status === 'pending' && (
                                    <div className="flex shrink-0 gap-1">
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            onClick={() =>
                                                handleResend(member.id)
                                            }
                                            title="Resend invitation"
                                        >
                                            <RefreshCw className="size-4" />
                                        </Button>
                                        <Dialog>
                                            <DialogTrigger asChild>
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                >
                                                    <X className="size-4" />
                                                </Button>
                                            </DialogTrigger>
                                            <DialogContent>
                                                <DialogHeader>
                                                    <DialogTitle>
                                                        Cancel invitation
                                                    </DialogTitle>
                                                    <DialogDescription>
                                                        Are you sure you want
                                                        to cancel this
                                                        invitation? This action
                                                        cannot be undone.
                                                    </DialogDescription>
                                                </DialogHeader>
                                                <DialogFooter>
                                                    <DialogClose asChild>
                                                        <Button variant="outline">
                                                            Keep
                                                        </Button>
                                                    </DialogClose>
                                                    <Button
                                                        variant="destructive"
                                                        onClick={() =>
                                                            handleRemove(
                                                                member.id,
                                                            )
                                                        }
                                                    >
                                                        Cancel Invitation
                                                    </Button>
                                                </DialogFooter>
                                            </DialogContent>
                                        </Dialog>
                                    </div>
                                )}
                                {member.status === 'active' && (
                                    <Dialog>
                                        <DialogTrigger asChild>
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                            >
                                                <Trash2 className="size-4" />
                                            </Button>
                                        </DialogTrigger>
                                        <DialogContent>
                                            <DialogHeader>
                                                <DialogTitle>
                                                    Remove member
                                                </DialogTitle>
                                                <DialogDescription>
                                                    Are you sure you want to
                                                    remove this member? They
                                                    will lose access to the
                                                    team.
                                                </DialogDescription>
                                            </DialogHeader>
                                            <DialogFooter>
                                                <DialogClose asChild>
                                                    <Button variant="outline">
                                                        Cancel
                                                    </Button>
                                                </DialogClose>
                                                <Button
                                                    variant="destructive"
                                                    onClick={() =>
                                                        handleRemove(
                                                            member.id,
                                                        )
                                                    }
                                                >
                                                    Remove
                                                </Button>
                                            </DialogFooter>
                                        </DialogContent>
                                    </Dialog>
                                )}
                            </li>
                        ))}
                    </ul>
                )}

                {releasedMembers.length > 0 && (
                    <>
                        <Heading
                            title="Past Members"
                            description="Previously removed team members."
                        />
                        <ul className="space-y-2">
                            {releasedMembers.map((member) => (
                                <li
                                    key={member.id}
                                    className="flex items-center gap-4 rounded-lg border bg-muted/50 p-4 opacity-60"
                                >
                                    <div className="flex size-10 shrink-0 items-center justify-center rounded-full bg-muted">
                                        <span className="text-sm font-semibold text-muted-foreground">
                                            {(member.name ?? member.email ?? '?')
                                                .charAt(0)
                                                .toUpperCase()}
                                        </span>
                                    </div>
                                    <div className="min-w-0 flex-1">
                                        <p className="truncate text-muted-foreground">
                                            {member.name ?? member.email}
                                        </p>
                                    </div>
                                    <Badge
                                        className={
                                            statusVariants[member.status]
                                        }
                                    >
                                        {statusLabels[member.status]}
                                    </Badge>
                                </li>
                            ))}
                        </ul>
                    </>
                )}
            </div>
        </AppLayout>
    );
}

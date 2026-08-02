import { useState } from 'react';
import { AlertTriangle, Trash2, Download, Upload } from 'lucide-react';
import { Button } from '@/components/ui/button';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/components/ui/alert-dialog';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

// Confirmation dialog presets
interface ConfirmDialogProps {
    title: string;
    description?: string;
    confirmLabel?: string;
    cancelLabel?: string;
    variant?: 'default' | 'destructive';
    onConfirm: () => void | Promise<void>;
    trigger?: React.ReactNode;
    open?: boolean;
    onOpenChange?: (open: boolean) => void;
}

export function ConfirmDialog({
    title,
    description,
    confirmLabel = 'Confirm',
    cancelLabel = 'Cancel',
    variant = 'default',
    onConfirm,
    trigger,
    open,
    onOpenChange,
}: ConfirmDialogProps) {
    return (
        <AlertDialog open={open} onOpenChange={onOpenChange}>
            {trigger && <AlertDialogTrigger asChild>{trigger}</AlertDialogTrigger>}
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>{title}</AlertDialogTitle>
                    {description && (
                        <AlertDialogDescription>{description}</AlertDialogDescription>
                    )}
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel>{cancelLabel}</AlertDialogCancel>
                    <AlertDialogAction
                        onClick={onConfirm}
                        className={variant === 'destructive' ? 'bg-destructive hover:bg-destructive/90' : undefined}
                    >
                        {confirmLabel}
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    );
}

// Delete confirmation dialog
interface DeleteDialogProps {
    title?: string;
    description?: string;
    itemName?: string;
    onConfirm: () => void | Promise<void>;
    trigger?: React.ReactNode;
    open?: boolean;
    onOpenChange?: (open: boolean) => void;
}

export function DeleteDialog({
    title = 'Delete item',
    description = 'This action cannot be undone.',
    itemName,
    onConfirm,
    trigger,
    open,
    onOpenChange,
}: DeleteDialogProps) {
    return (
        <AlertDialog open={open} onOpenChange={onOpenChange}>
            {trigger && <AlertDialogTrigger asChild>{trigger}</AlertDialogTrigger>}
            <AlertDialogContent className="max-w-md">
                <AlertDialogHeader>
                    <div className="flex items-center gap-3">
                        <div className="flex size-10 items-center justify-center rounded-full bg-destructive/10">
                            <Trash2 className="size-5 text-destructive" />
                        </div>
                        <AlertDialogTitle>{title}</AlertDialogTitle>
                    </div>
                    <AlertDialogDescription className="pt-2">
                        {itemName ? (
                            <>
                                Are you sure you want to delete <strong>{itemName}</strong>?{' '}
                                {description}
                            </>
                        ) : (
                            description
                        )}
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel>Cancel</AlertDialogCancel>
                    <AlertDialogAction
                        onClick={onConfirm}
                        className="bg-destructive text-white hover:bg-destructive/90"
                    >
                        Delete
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    );
}

// Warning dialog
interface WarningDialogProps {
    title: string;
    description: string;
    confirmLabel?: string;
    onConfirm: () => void | Promise<void>;
    trigger?: React.ReactNode;
    open?: boolean;
    onOpenChange?: (open: boolean) => void;
}

export function WarningDialog({
    title,
    description,
    confirmLabel = 'Continue',
    onConfirm,
    trigger,
    open,
    onOpenChange,
}: WarningDialogProps) {
    return (
        <AlertDialog open={open} onOpenChange={onOpenChange}>
            {trigger && <AlertDialogTrigger asChild>{trigger}</AlertDialogTrigger>}
            <AlertDialogContent>
                <AlertDialogHeader>
                    <div className="flex items-center gap-3">
                        <div className="flex size-10 items-center justify-center rounded-full bg-yellow-100 dark:bg-yellow-900/30">
                            <AlertTriangle className="size-5 text-yellow-600 dark:text-yellow-400" />
                        </div>
                        <AlertDialogTitle>{title}</AlertDialogTitle>
                    </div>
                    <AlertDialogDescription className="pt-2">{description}</AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel>Cancel</AlertDialogCancel>
                    <AlertDialogAction onClick={onConfirm}>{confirmLabel}</AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    );
}

// Success dialog
interface SuccessDialogProps {
    title: string;
    description?: string;
    actionLabel?: string;
    onAction?: () => void;
    trigger?: React.ReactNode;
    open?: boolean;
    onOpenChange?: (open: boolean) => void;
}

export function SuccessDialog({
    title,
    description,
    actionLabel = 'Done',
    onAction,
    trigger,
    open,
    onOpenChange,
}: SuccessDialogProps) {
    return (
        <AlertDialog open={open} onOpenChange={onOpenChange}>
            {trigger && <AlertDialogTrigger asChild>{trigger}</AlertDialogTrigger>}
            <AlertDialogContent className="max-w-sm">
                <AlertDialogHeader>
                    <div className="flex items-center justify-center mb-2">
                        <div className="flex size-12 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30">
                            <svg className="size-6 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                    </div>
                    <AlertDialogTitle className="text-center">{title}</AlertDialogTitle>
                    {description && (
                        <AlertDialogDescription className="text-center">{description}</AlertDialogDescription>
                    )}
                </AlertDialogHeader>
                <AlertDialogFooter className="sm:justify-center">
                    <AlertDialogAction onClick={onAction}>{actionLabel}</AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    );
}

// Import/Export confirmation dialogs
interface ImportDialogProps {
    title?: string;
    description?: string;
    fileType?: string;
    onConfirm: () => void | Promise<void>;
    trigger?: React.ReactNode;
    open?: boolean;
    onOpenChange?: (open: boolean) => void;
}

export function ImportDialog({
    title = 'Import Data',
    description = 'This will import data from a CSV or Excel file. Existing records may be updated.',
    fileType = 'CSV/Excel',
    onConfirm,
    trigger,
    open,
    onOpenChange,
}: ImportDialogProps) {
    return (
        <AlertDialog open={open} onOpenChange={onOpenChange}>
            {trigger && <AlertDialogTrigger asChild>{trigger}</AlertDialogTrigger>}
            <AlertDialogContent>
                <AlertDialogHeader>
                    <div className="flex items-center gap-3">
                        <div className="flex size-10 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900/30">
                            <Upload className="size-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <AlertDialogTitle>{title}</AlertDialogTitle>
                    </div>
                    <AlertDialogDescription className="pt-2">{description}</AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel>Cancel</AlertDialogCancel>
                    <AlertDialogAction onClick={onConfirm}>
                        <Upload className="size-4 mr-2" />
                        Choose {fileType} File
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    );
}

interface ExportDialogProps {
    title?: string;
    description?: string;
    fileType?: string;
    onConfirm: () => void | Promise<void>;
    trigger?: React.ReactNode;
    open?: boolean;
    onOpenChange?: (open: boolean) => void;
}

export function ExportDialog({
    title = 'Export Data',
    description = 'Download your data as a CSV or Excel file.',
    fileType = 'CSV',
    onConfirm,
    trigger,
    open,
    onOpenChange,
}: ExportDialogProps) {
    return (
        <AlertDialog open={open} onOpenChange={onOpenChange}>
            {trigger && <AlertDialogTrigger asChild>{trigger}</AlertDialogTrigger>}
            <AlertDialogContent>
                <AlertDialogHeader>
                    <div className="flex items-center gap-3">
                        <div className="flex size-10 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30">
                            <Download className="size-5 text-green-600 dark:text-green-400" />
                        </div>
                        <AlertDialogTitle>{title}</AlertDialogTitle>
                    </div>
                    <AlertDialogDescription className="pt-2">{description}</AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel>Cancel</AlertDialogCancel>
                    <AlertDialogAction onClick={onConfirm}>
                        <Download className="size-4 mr-2" />
                        Export as {fileType}
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    );
}

// Bulk action confirmation
interface BulkActionDialogProps {
    count: number;
    action: string;
    description?: string;
    confirmLabel?: string;
    onConfirm: () => void | Promise<void>;
    trigger?: React.ReactNode;
    open?: boolean;
    onOpenChange?: (open: boolean) => void;
}

export function BulkActionDialog({
    count,
    action,
    description,
    confirmLabel,
    onConfirm,
    trigger,
    open,
    onOpenChange,
}: BulkActionDialogProps) {
    return (
        <AlertDialog open={open} onOpenChange={onOpenChange}>
            {trigger && <AlertDialogTrigger asChild>{trigger}</AlertDialogTrigger>}
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>
                        {confirmLabel ?? action} {count} item{count !== 1 ? 's' : ''}
                    </AlertDialogTitle>
                    {description && (
                        <AlertDialogDescription>{description}</AlertDialogDescription>
                    )}
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel>Cancel</AlertDialogCancel>
                    <AlertDialogAction onClick={onConfirm}>
                        {confirmLabel ?? action}
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    );
}
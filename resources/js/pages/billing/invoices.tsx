import { Head } from '@inertiajs/react';
import {
    ArrowDownToLine,
    Calendar,
    CreditCard,
    FileText,
    Printer,
    Receipt,
    Search,
    X,
} from 'lucide-react';
import { useMemo, useState } from 'react';
import { invoices } from '@/actions/App/Http/Controllers/BillingController';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';

type Invoice = {
    id: number;
    invoice_number: string;
    amount: number;
    currency: string;
    gateway: string;
    status: string;
    paid_at: string | null;
    created_at: string;
    customer_name: string;
    company_name: string;
    app_name: string;
    period_start: string | null;
    period_end: string | null;
    billing_cycle: string | null;
};

type Props = {
    invoices: Invoice[];
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Billing', href: invoices().url },
    { title: 'Invoices' },
];

function formatAmount(amount: number, currency: string): string {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currency.toUpperCase(),
        minimumFractionDigits: 2,
    }).format(amount / 100);
}

function formatDate(dateString: string | null): string {
    if (!dateString) return '—';
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
}

function statusBadge(status: string) {
    const variants: Record<string, { variant: 'default' | 'secondary' | 'destructive' | 'outline'; label: string }> = {
        completed: { variant: 'default', label: 'Paid' },
        paid: { variant: 'default', label: 'Paid' },
        pending: { variant: 'secondary', label: 'Pending' },
        processing: { variant: 'secondary', label: 'Processing' },
        failed: { variant: 'destructive', label: 'Failed' },
        refunded: { variant: 'outline', label: 'Refunded' },
        cancelled: { variant: 'outline', label: 'Cancelled' },
    };

    const config = variants[status] ?? { variant: 'outline', label: status };

    return (
        <Badge variant={config.variant}>
            {config.label}
        </Badge>
    );
}

function gatewayLabel(gateway: string): string {
    const labels: Record<string, string> = {
        stripe: 'Stripe',
        sslcommerz: 'SSLCommerz',
        bkash: 'bKash',
        nagad: 'Nagad',
        portwallet: 'PortWallet',
        manual: 'Manual',
    };
    return labels[gateway] ?? gateway;
}

const intervalLabels: Record<string, string> = {
    day: 'Daily',
    week: 'Weekly',
    month: 'Monthly',
    quarterly: 'Quarterly',
    year: 'Yearly',
};

function generateInvoiceHtml(invoice: Invoice, showPaidSeal: boolean): string {
    const isPaid = invoice.status === 'completed' || invoice.status === 'paid';

    const paidSealSvg = showPaidSeal && isPaid ? `
        <div class="paid-seal">
            <svg viewBox="0 0 120 120" xmlns="http://www.w3.org/2000/svg">
                <circle cx="60" cy="60" r="55" fill="none" stroke="#16a34a" stroke-width="4"/>
                <circle cx="60" cy="60" r="48" fill="none" stroke="#16a34a" stroke-width="2"/>
                <path d="M35 60 L52 77 L85 44" fill="none" stroke="#16a34a" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"/>
                <text x="60" y="100" text-anchor="middle" font-size="11" font-weight="700" fill="#16a34a" font-family="system-ui, sans-serif" letter-spacing="3">PAID</text>
            </svg>
        </div>
    ` : '';

    return `
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice ${invoice.invoice_number}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; color: #111827; line-height: 1.5; }
        .container { max-width: 800px; margin: 0 auto; padding: 40px 20px; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 40px; padding-bottom: 24px; border-bottom: 2px solid #e5e7eb; }
        .logo { font-size: 24px; font-weight: 700; color: #111827; }
        .invoice-title { font-size: 32px; font-weight: 700; color: #111827; text-align: right; }
        .invoice-number { font-size: 14px; color: #6b7280; text-align: right; margin-top: 4px; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 32px; margin-bottom: 24px; }
        .section-title { font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280; margin-bottom: 8px; }
        .section-content { font-size: 14px; color: #374151; }
        .section-content .company { font-size: 13px; color: #6b7280; margin-top: 2px; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 32px; }
        .table th { text-align: left; padding: 12px 16px; background: #f9fafb; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280; border-bottom: 1px solid #e5e7eb; }
        .table td { padding: 16px; border-bottom: 1px solid #e5e7eb; font-size: 14px; }
        .table .amount { text-align: right; font-weight: 600; }
        .totals { display: flex; align-items: center; justify-content: flex-end; gap: 24px; }
        .total-row { display: flex; justify-content: space-between; padding: 8px 0; font-size: 14px; }
        .total-row.grand { font-size: 18px; font-weight: 700; border-top: 2px solid #111827; padding-top: 12px; margin-top: 8px; }
        .paid-seal { width: 80px; height: 80px; flex-shrink: 0; opacity: 0.85; }
        .footer { margin-top: 48px; padding-top: 24px; border-top: 1px solid #e5e7eb; text-align: center; font-size: 12px; color: #6b7280; }
        @media print { body { print-color-adjust: exact; -webkit-print-color-adjust: exact; } }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <div class="logo">${invoice.app_name}</div>
            </div>
            <div>
                <div class="invoice-title">INVOICE</div>
                <div class="invoice-number">${invoice.invoice_number}</div>
            </div>
        </div>

        <div class="grid">
            <div>
                <div class="section-title">Bill To</div>
                <div class="section-content">
                    <div>${invoice.customer_name}</div>
                    <div class="company">${invoice.company_name}</div>
                </div>
            </div>
            <div style="text-align: right;">
                <div class="section-title">Details</div>
                <div class="section-content">
                    <div>Invoice Date: ${formatDate(invoice.created_at)}</div>
                    ${isPaid ? `<div>Payment Date: ${formatDate(invoice.paid_at)}</div>` : ''}
                    ${isPaid ? `<div>Payment Method: ${gatewayLabel(invoice.gateway)}</div>` : ''}
                    <div>Status: ${invoice.status}</div>
                </div>
            </div>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th style="text-align: right;">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        ${invoice.period_start && invoice.period_end
                            ? `${formatDate(invoice.period_start)} — ${formatDate(invoice.period_end)}`
                            : 'Subscription Payment'}
                        ${invoice.billing_cycle
                            ? `<div style="font-size: 12px; color: #6b7280; margin-top: 4px;">${intervalLabels[invoice.billing_cycle] ?? invoice.billing_cycle} Subscription</div>`
                            : ''}
                    </td>
                    <td class="amount">${formatAmount(invoice.amount, invoice.currency)}</td>
                </tr>
            </tbody>
        </table>

        <div class="totals">
            ${paidSealSvg}
            <div class="w-64">
                <div class="total-row grand">
                    <span>Total Paid</span>
                    <span>${formatAmount(invoice.amount, invoice.currency)}</span>
                </div>
            </div>
        </div>

        <div class="footer">
            <p>Thank you for your business. If you have any questions about this invoice, please contact support.</p>
        </div>
    </div>
</body>
</html>`;
}

function openInvoiceWindow(invoice: Invoice, autoPrint: boolean): void {
    const printWindow = window.open('', '_blank');
    if (!printWindow) return;

    const html = generateInvoiceHtml(invoice, true);
    printWindow.document.write(html);
    printWindow.document.close();

    if (autoPrint) {
        printWindow.onload = () => {
            printWindow.print();
        };
    }
}

function InvoicePreview({ invoice, onClose }: { invoice: Invoice; onClose: () => void }) {
    const isPaid = invoice.status === 'completed' || invoice.status === 'paid';

    return (
        <Dialog open onOpenChange={onClose}>
            <DialogContent className="max-w-3xl max-h-[90vh] overflow-y-auto">
                <DialogHeader>
                    <DialogTitle className="flex items-center gap-2">
                        <FileText className="size-5" />
                        {invoice.invoice_number}
                    </DialogTitle>
                    <DialogDescription>
                        Invoice preview
                    </DialogDescription>
                </DialogHeader>

                <div className="relative rounded-lg border bg-background p-6 space-y-6">
                    <div className="flex items-start justify-between">
                        <div>
                            <p className="text-lg font-bold">{invoice.app_name}</p>
                        </div>
                        <div className="text-right">
                            <p className="text-2xl font-bold">INVOICE</p>
                            <p className="text-sm text-muted-foreground">{invoice.invoice_number}</p>
                        </div>
                    </div>

                    <div className="grid grid-cols-2 gap-6">
                        <div>
                            <p className="text-xs font-medium uppercase tracking-wider text-muted-foreground mb-1">Bill To</p>
                            <p className="text-sm">{invoice.customer_name}</p>
                            <p className="text-sm text-muted-foreground">{invoice.company_name}</p>
                        </div>
                        <div className="text-right">
                            <p className="text-xs font-medium uppercase tracking-wider text-muted-foreground mb-1">Details</p>
                            <div className="text-sm space-y-0.5">
                                <p>Invoice Date: {formatDate(invoice.created_at)}</p>
                                {isPaid && <p>Payment Date: {formatDate(invoice.paid_at)}</p>}
                                {isPaid && <p>Method: {gatewayLabel(invoice.gateway)}</p>}
                                <p>Status: {invoice.status}</p>
                            </div>
                        </div>
                    </div>

                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Description</TableHead>
                                <TableHead className="text-right">Amount</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow>
                                <TableCell>
                                    <p className="font-medium">
                                        {invoice.period_start && invoice.period_end
                                            ? `${formatDate(invoice.period_start)} — ${formatDate(invoice.period_end)}`
                                            : 'Subscription Payment'}
                                    </p>
                                    {invoice.billing_cycle && (
                                        <p className="text-xs text-muted-foreground mt-0.5">
                                            {intervalLabels[invoice.billing_cycle] ?? invoice.billing_cycle} Subscription
                                        </p>
                                    )}
                                </TableCell>
                                <TableCell className="text-right font-semibold">
                                    {formatAmount(invoice.amount, invoice.currency)}
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>

                    <div className="flex items-center justify-end gap-6">
                        {isPaid && (
                            <div className="w-20 h-20 flex-shrink-0 opacity-85">
                                <svg viewBox="0 0 120 120" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="60" cy="60" r="55" fill="none" stroke="#16a34a" strokeWidth="4"/>
                                    <circle cx="60" cy="60" r="48" fill="none" stroke="#16a34a" strokeWidth="2"/>
                                    <path d="M35 60 L52 77 L85 44" fill="none" stroke="#16a34a" strokeWidth="5" strokeLinecap="round" strokeLinejoin="round"/>
                                    <text x="60" y="100" textAnchor="middle" fontSize="11" fontWeight="700" fill="#16a34a" fontFamily="system-ui, sans-serif" letterSpacing="3">PAID</text>
                                </svg>
                            </div>
                        )}
                        <div className="w-64">
                            <div className="flex justify-between border-t pt-3 text-lg font-bold">
                                <span>Total Paid</span>
                                <span>{formatAmount(invoice.amount, invoice.currency)}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div className="flex justify-end gap-2">
                    <Button variant="outline" onClick={onClose}>
                        Close
                    </Button>
                    <Button variant="outline" onClick={() => openInvoiceWindow(invoice, true)}>
                        <Printer className="mr-2 size-4" />
                        Print
                    </Button>
                    <Button onClick={() => openInvoiceWindow(invoice, true)}>
                        <ArrowDownToLine className="mr-2 size-4" />
                        Download PDF
                    </Button>
                </div>
            </DialogContent>
        </Dialog>
    );
}

export default function Invoices({ invoices: invoiceList }: Props) {
    const [search, setSearch] = useState('');
    const [selectedInvoice, setSelectedInvoice] = useState<Invoice | null>(null);

    const filteredInvoices = useMemo(() => {
        if (!search) return invoiceList;
        const query = search.toLowerCase();
        return invoiceList.filter(
            (inv) =>
                inv.invoice_number.toLowerCase().includes(query) ||
                inv.gateway.toLowerCase().includes(query) ||
                inv.status.toLowerCase().includes(query),
        );
    }, [invoiceList, search]);

    const totalAmount = useMemo(
        () => invoiceList.reduce((sum, inv) => sum + inv.amount, 0),
        [invoiceList],
    );

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Invoices" />
            <div className="mx-auto w-full max-w-5xl space-y-6 p-4 sm:p-6">
                <Heading
                    title="Invoices"
                    description="View and download your payment history."
                />

                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div className="relative w-full sm:w-72">
                        <Search className="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            placeholder="Search invoices..."
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            className="pl-9"
                        />
                        {search && (
                            <button
                                type="button"
                                onClick={() => setSearch('')}
                                className="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground"
                            >
                                <X className="size-4" />
                            </button>
                        )}
                    </div>

                    <div className="flex items-center gap-2 text-sm text-muted-foreground">
                        <Receipt className="size-4" />
                        <span>
                            {invoiceList.length} invoice{invoiceList.length !== 1 ? 's' : ''}
                        </span>
                        <span className="mx-1">•</span>
                        <span>Total: {formatAmount(totalAmount, 'BDT')}</span>
                    </div>
                </div>

                {invoiceList.length === 0 ? (
                    <div className="flex flex-col items-center justify-center rounded-lg border border-dashed p-12 text-center">
                        <FileText className="mb-4 size-12 text-muted-foreground/50" />
                        <h3 className="text-lg font-semibold">No invoices yet</h3>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Your payment history will appear here once you make a purchase.
                        </p>
                    </div>
                ) : filteredInvoices.length === 0 ? (
                    <div className="rounded-lg border p-8 text-center">
                        <p className="text-muted-foreground">
                            No invoices match your search.
                        </p>
                    </div>
                ) : (
                    <div className="rounded-lg border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Invoice</TableHead>
                                    <TableHead>Date</TableHead>
                                    <TableHead>Method</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead className="text-right">Amount</TableHead>
                                    <TableHead className="w-12" />
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {filteredInvoices.map((invoice) => (
                                    <TableRow key={invoice.id}>
                                        <TableCell className="font-medium">
                                            <div className="flex items-center gap-2">
                                                <FileText className="size-4 text-muted-foreground" />
                                                {invoice.invoice_number}
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex items-center gap-1.5 text-muted-foreground">
                                                <Calendar className="size-3.5" />
                                                {formatDate(invoice.paid_at ?? invoice.created_at)}
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex items-center gap-1.5">
                                                <CreditCard className="size-3.5 text-muted-foreground" />
                                                {gatewayLabel(invoice.gateway)}
                                            </div>
                                        </TableCell>
                                        <TableCell>{statusBadge(invoice.status)}</TableCell>
                                        <TableCell className="text-right font-semibold tabular-nums">
                                            {formatAmount(invoice.amount, invoice.currency)}
                                        </TableCell>
                                        <TableCell>
                                            <DropdownMenu>
                                                <DropdownMenuTrigger asChild>
                                                    <Button variant="ghost" size="icon" className="size-8">
                                                        <span className="sr-only">Actions</span>
                                                        <svg
                                                            xmlns="http://www.w3.org/2000/svg"
                                                            width="16"
                                                            height="16"
                                                            viewBox="0 0 24 24"
                                                            fill="none"
                                                            stroke="currentColor"
                                                            strokeWidth="2"
                                                            strokeLinecap="round"
                                                            strokeLinejoin="round"
                                                        >
                                                            <circle cx="12" cy="12" r="1" />
                                                            <circle cx="19" cy="12" r="1" />
                                                            <circle cx="5" cy="12" r="1" />
                                                        </svg>
                                                    </Button>
                                                </DropdownMenuTrigger>
                                                <DropdownMenuContent align="end">
                                                    <DropdownMenuItem onClick={() => setSelectedInvoice(invoice)}>
                                                        <FileText className="mr-2 size-4" />
                                                        Preview
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem onClick={() => openInvoiceWindow(invoice, true)}>
                                                        <ArrowDownToLine className="mr-2 size-4" />
                                                        Download PDF
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem onClick={() => openInvoiceWindow(invoice, true)}>
                                                        <Printer className="mr-2 size-4" />
                                                        Print
                                                    </DropdownMenuItem>
                                                </DropdownMenuContent>
                                            </DropdownMenu>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </div>
                )}

                {selectedInvoice && (
                    <InvoicePreview
                        invoice={selectedInvoice}
                        onClose={() => setSelectedInvoice(null)}
                    />
                )}
            </div>
        </AppLayout>
    );
}

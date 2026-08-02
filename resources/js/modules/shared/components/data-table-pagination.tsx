import { ChevronLeftIcon, ChevronRightIcon, ChevronsLeftIcon, ChevronsRightIcon } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { cn } from '@/lib/utils';

type DataTablePaginationProps = {
    pageIndex: number;
    pageSize: number;
    total: number;
    onPageChange: (page: number) => void;
    onPageSizeChange: (size: number) => void;
    pageCount?: number;
    canPreviousPage?: boolean;
    canNextPage?: boolean;
};

export function DataTablePagination({
    pageIndex,
    pageSize,
    total,
    onPageChange,
    onPageSizeChange,
    pageCount,
    canPreviousPage,
    canNextPage,
}: DataTablePaginationProps) {
    const totalPages = pageCount ?? Math.ceil(total / pageSize);
    const calculatedCanPrevious = canPreviousPage ?? pageIndex > 0;
    const calculatedCanNext = canNextPage ?? pageIndex < totalPages - 1;

    const startRow = total === 0 ? 0 : pageIndex * pageSize + 1;
    const endRow = Math.min((pageIndex + 1) * pageSize, total);

    return (
        <div className="flex flex-col items-center gap-4 rounded-lg border bg-muted/30 px-4 py-3 sm:flex-row sm:justify-between">
            <div className="flex items-center gap-3 text-sm">
                <span className="text-muted-foreground">
                    Showing <span className="font-medium text-foreground">{startRow}</span> to{' '}
                    <span className="font-medium text-foreground">{endRow}</span> of{' '}
                    <span className="font-medium text-foreground">{total.toLocaleString()}</span> results
                </span>
            </div>

            <div className="flex items-center gap-2">
                <div className="flex items-center gap-1.5">
                    <span className="text-sm text-muted-foreground">Rows per page</span>
                    <Select
                        value={String(pageSize)}
                        onValueChange={(value) => onPageSizeChange(Number(value))}
                    >
                        <SelectTrigger className="h-8 w-20">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            {[10, 25, 50, 100].map((size) => (
                                <SelectItem key={size} value={String(size)}>
                                    {size}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                </div>

                <div className="h-6 w-px bg-border mx-2" />

                <div className="flex items-center gap-1">
                    <Button
                        variant="outline"
                        size="icon"
                        className="hidden size-8 sm:flex"
                        onClick={() => onPageChange(0)}
                        disabled={!calculatedCanPrevious}
                        aria-label="Go to first page"
                    >
                        <ChevronsLeftIcon className="size-4" />
                    </Button>
                    <Button
                        variant="outline"
                        size="icon"
                        className="size-8"
                        onClick={() => onPageChange(pageIndex - 1)}
                        disabled={!calculatedCanPrevious}
                        aria-label="Go to previous page"
                    >
                        <ChevronLeftIcon className="size-4" />
                    </Button>
                    <div className="flex items-center gap-1.5 px-2">
                        <span className="text-sm">
                            <span className="font-medium">{pageIndex + 1}</span>
                            <span className="text-muted-foreground"> / {totalPages || 1}</span>
                        </span>
                    </div>
                    <Button
                        variant="outline"
                        size="icon"
                        className="size-8"
                        onClick={() => onPageChange(pageIndex + 1)}
                        disabled={!calculatedCanNext}
                        aria-label="Go to next page"
                    >
                        <ChevronRightIcon className="size-4" />
                    </Button>
                    <Button
                        variant="outline"
                        size="icon"
                        className="hidden size-8 sm:flex"
                        onClick={() => onPageChange(totalPages - 1)}
                        disabled={!calculatedCanNext}
                        aria-label="Go to last page"
                    >
                        <ChevronsRightIcon className="size-4" />
                    </Button>
                </div>
            </div>
        </div>
    );
}

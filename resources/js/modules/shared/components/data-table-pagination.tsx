import { ChevronLeftIcon, ChevronRightIcon, ChevronsLeftIcon, ChevronsRightIcon } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

type DataTablePaginationProps = {
    pageIndex: number;
    pageSize: number;
    total: number;
    onPageChange: (page: number) => void;
    onPageSizeChange: (size: number) => void;
};

export function DataTablePagination({ pageIndex, pageSize, total, onPageChange, onPageSizeChange }: DataTablePaginationProps) {
    const totalPages = Math.ceil(total / pageSize);
    const startRow = total === 0 ? 0 : pageIndex * pageSize + 1;
    const endRow = Math.min((pageIndex + 1) * pageSize, total);

    return (
        <div className="flex flex-col items-center gap-4 px-2 py-4 sm:flex-row sm:justify-between">
            <div className="text-muted-foreground flex items-center gap-2 text-sm">
                <span>
                    {startRow}-{endRow} of {total}
                </span>
                <Select
                    value={String(pageSize)}
                    onValueChange={(value) => onPageSizeChange(Number(value))}
                >
                    <SelectTrigger className="h-8 w-16">
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                        {[25, 50, 100].map((size) => (
                            <SelectItem key={size} value={String(size)}>
                                {size}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
                <span>per page</span>
            </div>
            <div className="flex items-center gap-1">
                <Button
                    variant="outline"
                    size="icon"
                    className="hidden size-8 lg:flex"
                    onClick={() => onPageChange(0)}
                    disabled={pageIndex === 0}
                >
                    <ChevronsLeftIcon className="size-4" />
                </Button>
                <Button
                    variant="outline"
                    size="icon"
                    className="size-8"
                    onClick={() => onPageChange(pageIndex - 1)}
                    disabled={pageIndex === 0}
                >
                    <ChevronLeftIcon className="size-4" />
                </Button>
                <div className="flex items-center gap-1 text-sm font-medium">
                    <span className="text-foreground">{pageIndex + 1}</span>
                    <span className="text-muted-foreground">/ {totalPages || 1}</span>
                </div>
                <Button
                    variant="outline"
                    size="icon"
                    className="size-8"
                    onClick={() => onPageChange(pageIndex + 1)}
                    disabled={pageIndex >= totalPages - 1}
                >
                    <ChevronRightIcon className="size-4" />
                </Button>
                <Button
                    variant="outline"
                    size="icon"
                    className="hidden size-8 lg:flex"
                    onClick={() => onPageChange(totalPages - 1)}
                    disabled={pageIndex >= totalPages - 1}
                >
                    <ChevronsRightIcon className="size-4" />
                </Button>
            </div>
        </div>
    );
}

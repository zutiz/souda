import { Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

type PaginationProps = {
    links: PaginationLink[];
    currentPage: number;
    lastPage: number;
    perPage: number;
    total: number;
};

export function Pagination({ links, lastPage, currentPage, perPage, total }: PaginationProps) {
    if (lastPage <= 1) return null;

    return (
        <div className="mt-4 space-y-2">
            <div className="flex items-center justify-center gap-1">
                {links.map((link, i) => (
                    <Button
                        key={i}
                        variant={link.active ? 'default' : 'outline'}
                        size="sm"
                        disabled={!link.url}
                        asChild={!!link.url && !link.active}
                    >
                        {link.url && !link.active ? (
                            <Link
                                href={link.url}
                                dangerouslySetInnerHTML={{ __html: link.label }}
                            />
                        ) : (
                            <span
                                dangerouslySetInnerHTML={{ __html: link.label }}
                            />
                        )}
                    </Button>
                ))}
            </div>
            <p className="text-center text-sm text-muted-foreground">
                Showing {perPage * (currentPage - 1) + 1}–
                {Math.min(perPage * currentPage, total)} of {total}
            </p>
        </div>
    );
}

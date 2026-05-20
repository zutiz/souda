export type PaginationState = {
    page: number;
    perPage: number;
};

export type PaginatedResponse<T> = {
    data: T[];
    total: number;
    currentPage: number;
    perPage: number;
    lastPage: number;
};

export type SortState = {
    field: string;
    direction: 'asc' | 'desc';
};

export type FilterState = Record<string, string | string[] | undefined>;

export type TableState = {
    pagination: PaginationState;
    sorting: SortState[];
    filters: FilterState;
    search?: string;
};

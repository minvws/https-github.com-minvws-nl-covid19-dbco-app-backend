export enum DefaultSortOptions {
    CREATED_AT = 'createdAt',
    STATUS = 'status',
}

export type PaginatedRequestOptions<T = DefaultSortOptions> = {
    page: number;
    perPage: number;
    lastPage?: number;
    order?: 'asc' | 'desc';
    sort?: T;
};

export type PaginatedResponse<T> = {
    currentPage: number;
    data: T[];
    from: number;
    lastPage: number;
    to: number;
    total: number;
};

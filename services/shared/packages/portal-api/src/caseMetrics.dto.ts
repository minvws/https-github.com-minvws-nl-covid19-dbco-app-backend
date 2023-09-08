export type CasesCreatedArchivedMetric = {
    date: DateStringISO8601;
    created: number;
    archived: number;
};

export type CasesCreatedArchivedResponse = {
    refreshedAt: DateStringISO8601 | null;
    eTag: string | null;
    data: CasesCreatedArchivedMetric[];
};

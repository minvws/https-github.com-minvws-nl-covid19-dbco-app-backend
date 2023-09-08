export interface AccessRequestOverviewResponse {
    caseUuid: string;
    date: string;
    name: string;
    user: string;
    show: number;
    exported: number;
    caseDeleted: number;
    caseDeleteStarted: number;
    caseDeleteRecovered: number;
    contactDeleted: number;
    contactDeleteStarted: number;
    contactDeleteRecovered: number;
}

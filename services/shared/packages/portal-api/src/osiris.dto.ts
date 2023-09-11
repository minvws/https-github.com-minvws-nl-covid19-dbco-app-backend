import type { OsirisHistoryStatusV1 } from '@dbco/enum';

export type OsirisLogItem = {
    caseIsReopened: boolean;
    osirisValidationResponse: OsirisValidationResponse | null;
    status: OsirisHistoryStatusV1;
    time: string;
    uuid: string;
};

export type OsirisValidationResponse = {
    errors?: string[];
    messages?: string[];
    warnings?: string[];
};

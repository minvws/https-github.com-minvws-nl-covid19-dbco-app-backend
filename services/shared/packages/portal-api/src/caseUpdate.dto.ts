export interface CaseUpdatesResponse {
    total: number;
    items: CaseUpdateResponseItem[];
}

export interface CaseUpdateResponseItem {
    uuid: string;
    receivedAt: string;
    source: string;
}

export interface CaseUpdateItem {
    uuid: string;
    receivedAt: string;
    source: string;
    fragments: CaseUpdateItemFragment[];
    contacts?: CaseUpdateItemContact[];
}

export interface CaseUpdateItemContact {
    fragments: CaseUpdateItemFragment[];
    label: string;
}

export interface CaseUpdateItemFragment {
    name: string;
    label: string;
    fields: CaseUpdateItemFragmentField[];
}

export interface CaseUpdateItemFragmentField {
    id: string;
    name: string;
    label: string;
    oldValue: string;
    newValue: string;
    oldDisplayValue: string;
    newDisplayValue: string;
}

export interface CaseUpdateRequest {
    fieldIds: string[];
}

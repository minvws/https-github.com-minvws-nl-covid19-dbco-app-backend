import type { InformedByV1, InformStatusV1 } from '@dbco/enum';

export type Task = {
    accessible?: boolean;
    caseUuid: string;
    category?: string;
    communication?: InformedByV1;
    contactDates?: string[];
    copiedAt?: string;
    createdAt?: string;
    dateOfLastExposure?: string;
    deletedAt?: string;
    derivedLabel?: string;
    dossierNumber?: string;
    email?: string;
    exportId?: string;
    exportedAt?: string;
    firstname?: string;
    group?: TaskGroup;
    informStatus: InformStatusV1;
    informedByIndexAt?: string;
    informedByStaffAt?: string;
    internalReference: string;
    isSource: boolean;
    label?: string;
    lastname?: string;
    nature?: string;
    progress?: string;
    pseudoBsnGuid?: string;
    questionnaireUuid?: string;
    source: string;
    status: TaskStatus;
    taskContext?: string;
    taskType: string;
    telephone?: string;
    updatedAt?: string;
    uuid: string;
};

export enum TaskStatus {
    Open = 'open',
    Closed = 'closed',
    Deleted = 'deleted',
}

export enum TaskGroup {
    Contact = 'contact',
    PositiveSource = 'positivesource',
    SymptomaticSource = 'symptomaticsource',
}

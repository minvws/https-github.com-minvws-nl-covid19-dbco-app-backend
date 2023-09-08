import type { BcoPhaseV1, BcoStatusV1, PriorityV1 } from '@dbco/enum';
import type { CaseListCommonDTO } from '@dbco/schema/caseList/caseListCommon';
import type { TestResult } from './case.dto';

export type CaseLabel = {
    uuid: string;
    label: string;
};

export type Counts = Record<PlannerView, number>;

export enum PlannerView {
    INTAKELIST = 'intakeList',
    UNASSIGNED = 'unassigned',
    ASSIGNED = 'assigned',
    OUTSOURCED = 'outsourced',
    QUEUED = 'queued',
    COMPLETED = 'completed',
    ARCHIVED = 'archived',
    UNKNOWN = 'unknown',
}

export type IntakeCase = {
    cat1Count: number | null;
    createdAt: string;
    dateOfBirth: string | null;
    dateOfSymptomOnset: string | null;
    dateOfTest: string | null;
    estimatedCat2Count: number | null;
    identifier: string;
    labels: CaseLabel[];
    priority: PriorityV1 | null;
    receivedAt: string | null;
    updatedAt: null;
    uuid: string;
};

export type CaseList = Pick<CaseListCommonDTO, 'uuid' | 'name' | 'isDefault' | 'isQueue'>;

export type CaseListWithStats = CaseList & {
    assignedCasesCount: number;
    unassignedCasesCount: number;
    completedCasesCount: number;
    archivedCasesCount: number;
};
// List item in planner overview page
export type PlannerCaseListItem = {
    uuid: string;
    caseId: string;
    contactsCount: number | null;
    dateOfBirth: string | null;
    dateOfTest: string | null;
    statusIndexContactTracing: string;
    statusExplanation: string;
    createdAt: string;
    updatedAt: string;
    organisation: {
        uuid: string;
        name: string;
        abbreviation: string | null;
        isCurrent: boolean;
    } | null;
    assignedOrganisation: {
        uuid: string;
        abbreviation: string | null;
        name: string;
        isCurrent: boolean;
    } | null;
    assignedCaseList: {
        uuid: string;
        isQueue: boolean | null;
        name: string;
    } | null;
    assignedUser: {
        uuid: string;
        isCurrent: boolean;
        name: string;
    } | null;
    isApproved: boolean | null;
    isAssignable: boolean;
    isClosable: boolean;
    isDeletable: boolean;
    isEditable: boolean;
    isReopenable: boolean;
    canChangeOrganisation: boolean;
    label: string | null;
    osirisNumber: number | null;
    plannerView: PlannerView;
    wasOutsourced: boolean;
    wasOutsourcedToOrganisation: { name: string } | null;
    caseLabels: CaseLabel[];
    priority: PriorityV1;
    bcoPhase?: BcoPhaseV1;
    bcoStatus?: BcoStatusV1 | null;
    hpzoneNumber?: string;
    testMonsterNumber?: string;
    testResults?: Array<TestResult['source']>;
    lastAssignedUserName: string | null;
};

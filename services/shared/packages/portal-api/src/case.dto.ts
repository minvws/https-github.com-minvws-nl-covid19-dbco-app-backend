import type {
    BcoStatusV1,
    ContactTracingStatusV1,
    PriorityV1,
    FixedCalendarPeriodV1,
    CalendarPeriodColorV1,
    CalendarPointColorV1,
} from '@dbco/enum';
import type { AssignmentOption } from './assignment';
import type { CaseLabel, PlannerView } from '@dbco/portal-api/caseList.dto';
import type { CallToActionHistoryItem } from '@dbco/portal-api/callToAction.dto';
import type { TestResultV1 as TestResultV1Model } from '@dbco/schema/testResult/testResultV1';
import type { Address } from './address';
import type { Organisation } from './organisation.dto';
import type { IconName } from '@dbco/ui-library';

export interface CalendarDateRange {
    id: string;
    type: string;
    startDate: Date;
    endDate: Date;
    color: CalendarPeriodColorV1 | CalendarPointColorV1;
    key?: FixedCalendarPeriodV1;
    label?: string;
    icon?: IconName;
}

export type CaseCreateUpdate = {
    assignedCaseListUuid?: string;
    contact: {
        phone?: string;
        email?: string;
    };
    general: { hpzoneNumber?: string };
    index: {
        firstname?: string;
        lastname?: string;
        dateOfBirth?: string;
        address?: Partial<Address>;
    };
    test: {
        dateOfTest?: string;
        monsterNumber?: string;
    };

    caseLabels?: string[];
    notes?: string;
    priority?: PriorityV1;
    pseudoBsnGuid?: string;
};

export type CaseUpdateMeta = {
    caseLabels?: string[];
    priority?: PriorityV1;
};

export type SearchDTO = {
    query: SearchQueryDTO;
    contacts: TaskSearchResultDTO[];
    cases: IndexSearchResultDTO[];
};

export type SearchQueryDTO = {
    caseUuid?: string | null | undefined;
    taskUuid?: string | null | undefined;
    email?: string | null | undefined;
    dateOfBirth?: Date | null | undefined;
    identifier?: string | null | undefined;
    lastname?: string | null | undefined;
    phone?: string | null | undefined;
};

export type PlannerSearchResultDto = {
    plannerView: PlannerView;
    statusIndexContactTracing: ContactTracingStatusV1;
    bcoStatus: BcoStatusV1;
    caseId: string;
    organisation: {
        abbreviation: string;
        isCurrent: boolean;
    } | null;
};

export type TaskSearchResultDTO = {
    uuid: string;
    contactDate: Date;
    category: string;
    index: {
        number: string;
        relationship: string;
    };
};

export type IndexSearchResultDTO = {
    uuid: string;
    number: string;
    dateOfSymptomOnset: Date;
};

export type IndexUpdateContactStatusQueryDTO = {
    uuid: string;
    statusIndexContactTracing: ContactTracingStatusV1;
    statusExplanation: string;
    casequalityFeedback: string | null;
};

export type IndexUpdateContactStatusDTO = {
    status_index_contact_tracing: ContactTracingStatusV1;
    status_explanation: string;
    casequality_feedback?: string | null;
};

export enum CaseTimelineType {
    Note = 'note',
    CallToAction = 'call-to-action',
    CaseAssignmentHistory = 'case-assignment-history',
    ExpertQuestion = 'expert-question',
}

export type CaseTimelineDTO = {
    time: string;
    timelineable_id: string;
    timelineable_type: CaseTimelineType;
    title: string;
    uuid: string;
    answer_time?: string | null;
    answer_user?: string | null;
    answer?: string | null;
    author_user?: string | null;
    call_to_action_deadline?: string | null;
    call_to_action_items?: Array<CallToActionHistoryItem> | null;
    call_to_action_uuid?: string | null;
    note?: string | null;
};

export type UserAssignmentOptionsDTO = { options: AssignmentOption[] };

export interface CaseLock {
    removed: boolean;
    user: {
        name: string;
        organisation: string;
    };
}

export interface CaseLockResponse {
    status: number;
    data: {
        user?: CaseLock['user'];
    };
}
export type TestResult = Pick<
    TestResultV1Model,
    'uuid' | 'typeOfTest' | 'source' | 'sampleLocation' | 'result' | 'customTypeOfTest' | 'laboratory'
> & {
    // missing fields in schema....
    sampleNumber?: string;
    testLocation?: string;

    // Dates
    dateOfTest: string;
    receivedAt?: string;
    dateOfResult?: string;
};
export type CreateManualTestResultFields = Pick<
    TestResultV1Model,
    'typeOfTest' | 'dateOfTest' | 'monsterNumber' | 'result' | 'customTypeOfTest' | 'laboratory'
>;

export type PlannerCase = {
    uuid: string;
    caseLabels: CaseLabel[];
    contact: {
        phone?: string;
        email?: string;
    };
    general: {
        hpzoneNumber?: string;
        notes?: string;
        organisation: Organisation;
        reference?: string;
    };
    index: {
        firstname?: string;
        lastname?: string;
        dateOfBirth?: string;
        address?: Partial<Address>;
        bsnCensored?: string;
        bsnLetters?: string;
    };
    test: {
        dateOfTest?: string;
        monsterNumber?: string;
    };
};

export type UpdatePriority = {
    cases: string[];
    priority: string;
};

export type ValidationLevel = 'fatal' | 'warning' | 'notice';

export type CaseValidationMessages = Record<ValidationLevel, string[]>;

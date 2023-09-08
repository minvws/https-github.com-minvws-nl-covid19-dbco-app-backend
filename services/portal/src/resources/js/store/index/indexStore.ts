import { sharedActions } from '../actions';
import { sharedMutations } from '../mutations';
import { StoreType } from '../storeType';
import { indexStoreActions } from './indexStoreAction';
import { indexStoreMutations } from './indexStoreMutation';
import type { CovidCaseUnionDTO } from '@dbco/schema/unions';
import type {
    AutomaticAddressVerificationStatusV1,
    CalendarViewV1,
    ContactTracingStatusV1,
    TaskGroupV1,
} from '@dbco/enum';
import type { AxiosError } from 'axios';
import type { CalendarDateRange, TestResult } from '@dbco/portal-api/case.dto';
import type { Context } from '@dbco/portal-api/context.dto';
import type { MessageSummary } from '@dbco/portal-api/message.dto';
import type { Organisation } from '@dbco/portal-api/organisation.dto';
import type { Task } from 'vitest';

export type IndexStoreState = {
    uuid: string;
    loaded: boolean;
    calendarData: CalendarDateRange[];
    calendarViews: Partial<{ [key in CalendarViewV1]: string[] }>;
    meta:
        | {
              automaticAddressVerificationStatus: AutomaticAddressVerificationStatusV1;
              schemaVersion: number;
              bcoStatus?: string;
              caseId?: string;
              completedAt?: string;
              contextContagiousCount?: number;
              episodeStartDate?: string;
              indexStatus?: string;
              indexSubmittedAt?: string;
              isLocked?: boolean;
              name?: string;
              organisation?: Organisation;
              osirisNumber?: number;
              pairingExpiresAt?: string;
              pseudoBsnGuid?: string;
              statusIndexContactTracing?: ContactTracingStatusV1;
              taskCount?: { [key in TaskGroupV1]: number };
              userCanEdit?: boolean;
          }
        | AnyObject;
    errors: AnyObject;
    // Shouldn't be partial, BE change needed
    fragments: Partial<CovidCaseUnionDTO>;
    messages: MessageSummary[];
    contexts: Context[];
    tasks: Partial<{ [key in TaskGroupV1]: Task[] }>;
    backendError?: AxiosError | null;
    testResults?: TestResult[];
};

const getDefaultState = (): IndexStoreState => ({
    backendError: null,
    uuid: '',
    calendarData: [],
    calendarViews: {},
    loaded: false,
    meta: {},
    errors: {},
    fragments: {},
    messages: [],
    contexts: [],
    tasks: {},
    testResults: [],
});

export default {
    namespaced: true,
    state: getDefaultState(),
    actions: {
        ...sharedActions(StoreType.INDEX),
        ...indexStoreActions,
    },
    mutations: { ...sharedMutations(getDefaultState), ...indexStoreMutations },
    getters: {
        calendarData: (state: IndexStoreState) => state.calendarData,
        calendarViews: (state: IndexStoreState) => state.calendarViews,
        meta: (state: IndexStoreState) => state.meta,
        errors: (state: IndexStoreState) => state.errors,
        forms: (state: IndexStoreState) => state,
        fragments: (state: IndexStoreState) => state.fragments,
        messages: (state: IndexStoreState) => state.messages,
        osirisNumber: (state: IndexStoreState) => state.meta?.osirisNumber,
        uuid: (state: IndexStoreState) => state.uuid,
        contexts: (state: IndexStoreState) => state.contexts,
        statusIndexContactTracing: (state: IndexStoreState) => state.meta?.statusIndexContactTracing,
        tasks: (state: IndexStoreState) => state.tasks,
        // This getter is being used in actions.updateFormValue (getters.type)
        type: () => 'cases',
        version: (state: IndexStoreState) => state.meta?.schemaVersion,
        testResults: (state: IndexStoreState) => state.testResults,
        indexDisplayName: (state: IndexStoreState) => state.meta?.name,
    },
};

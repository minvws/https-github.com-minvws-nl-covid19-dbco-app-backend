import { supervisionMutations } from './supervisionMutations/supervisionMutations';
import { supervisionActions } from './supervisionActions/supervisionActions';
import type { ExpertQuestionRequestOptions, ExpertQuestionResponse } from '@dbco/portal-api/supervision.dto';
import type { RootStoreState } from '@/store';
import type { BackendError } from '@dbco/portal-api/error';
import type { Poll } from '@/store/polling';
import type { SupervisionRoles } from '@dbco/portal-api/user';

export interface BcoSupervisionStoreState {
    questions: SupervisionQuestions;
}

export type SupervisionQuestions = Array<ExpertQuestionResponse>;

export interface SupervisionQuestionTable extends ExpertQuestionRequestOptions {
    infiniteId: number;
}

export interface SupervisorSupervisionStoreState {
    activeRole: SupervisionRoles | null;
    backendError: BackendError | null;
    pollSelected: Poll;
    pollSupervisionQuestions: Poll;
    selectedQuestion: ExpertQuestionResponse | null;
    supervisionQuestions: SupervisionQuestions;
    supervisionQuestionTable: SupervisionQuestionTable;
    updateMessage: string | null;
}

export interface SupervisionStoreState extends BcoSupervisionStoreState, SupervisorSupervisionStoreState {}

export const initialState = {
    activeRole: null,
    backendError: null,
    pollSelected: {
        polling: null,
        pollInterval: 5000,
    },
    pollSupervisionQuestions: {
        polling: null,
        pollInterval: 30000,
    },
    questions: [],
    selectedQuestion: null,
    supervisionQuestions: [],
    supervisionQuestionTable: {
        infiniteId: Date.now(),
        page: 1,
        perPage: 20,
    },
    updateMessage: null,
};

const getDefaultState = (): SupervisionStoreState => initialState;

export default {
    namespaced: true,
    state: getDefaultState(),
    actions: supervisionActions,
    mutations: supervisionMutations,
    getters: {
        expertQuestionPickedUpByUser: (
            state: SupervisionStoreState,
            getters: any,
            rootState: RootStoreState,
            rootGetters: any
        ) => {
            const assignedUserUuid = getters.assignedUserUuid;
            const userUuid = rootGetters['userInfo/userUuid'];
            return !!(assignedUserUuid && userUuid && assignedUserUuid === userUuid);
        },
        assignedUserUuid: (state: SupervisionStoreState) => state.selectedQuestion?.assignedUser?.uuid,
        answeredByUserUuid: (state: SupervisionStoreState) => state.selectedQuestion?.answer?.answeredBy?.uuid,
        expertQuestionAnsweredByUser: (
            state: SupervisionStoreState,
            getters: any,
            rootState: RootStoreState,
            rootGetters: any
        ) => {
            const answeredByUserUuid = getters.answeredByUserUuid;
            const userUuid = rootGetters['userInfo/userUuid'];
            return !!(answeredByUserUuid && userUuid && answeredByUserUuid === userUuid);
        },
    },
};

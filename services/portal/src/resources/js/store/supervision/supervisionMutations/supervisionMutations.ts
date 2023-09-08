import type { DefaultSortOptions } from '@dbco/portal-api/pagination';
import type { BackendError } from '@dbco/portal-api/error';
import type { ExpertQuestionRequestOptions, ExpertQuestionResponse } from '@dbco/portal-api/supervision.dto';
import type { AxiosError } from 'axios';
import type { SupervisionQuestions, SupervisionStoreState } from '../supervisionStore';
import type { SupervisionRoles } from '@dbco/portal-api/user';

export enum SupervisionMutations {
    ASK_QUESTION = 'ASK_QUESTION',
    DESELECT_QUESTION = 'DESELECT_QUESTION',
    INCREMENT_TABLE_PAGE = 'INCREMENT_TABLE_PAGE',
    RESET_QUESTION_TABLE = 'RESET_QUESTION_TABLE',
    SELECT_QUESTION = 'SELECT_QUESTION',
    SET_ACTIVE_ROLE = 'SET_ACTIVE_ROLE',
    SET_BACKEND_ERROR = 'SET_BACKEND_ERROR',
    SET_SUPERVISION_QUESTIONS = 'SET_SUPERVISION_QUESTIONS',
    SET_TABLE_SORT = 'SET_TABLE_SORT',
    SET_UPDATE_MESSAGE = 'SET_UPDATE_MESSAGE',
    STOP_POLLING_SELECTED = 'STOP_POLLING_SELECTED',
    STOP_POLLING_SUPERVISION_QUESTIONS = 'STOP_POLLING_SUPERVISION_QUESTIONS',
    UPDATE_QUESTION_IN_TABLE = 'UPDATE_QUESTION_IN_TABLE',
}

export const supervisionMutations = {
    [SupervisionMutations.ASK_QUESTION](state: SupervisionStoreState, question: ExpertQuestionResponse) {
        state.questions.push(question);
    },
    [SupervisionMutations.DESELECT_QUESTION](state: SupervisionStoreState) {
        state.selectedQuestion = null;
        this.STOP_POLLING_SELECTED;
    },
    [SupervisionMutations.INCREMENT_TABLE_PAGE]: (state: SupervisionStoreState) =>
        state.supervisionQuestionTable.page++,
    [SupervisionMutations.RESET_QUESTION_TABLE](state: SupervisionStoreState) {
        state.supervisionQuestionTable.page = 1;
        state.supervisionQuestions = [];

        // Reset vue-infinite-loading component
        state.supervisionQuestionTable.infiniteId = Date.now();
    },
    [SupervisionMutations.SELECT_QUESTION](state: SupervisionStoreState, questionRequest: ExpertQuestionResponse) {
        state.selectedQuestion = questionRequest;
    },
    [SupervisionMutations.SET_ACTIVE_ROLE](state: SupervisionStoreState, role: SupervisionRoles | null) {
        state.activeRole = role;
    },
    [SupervisionMutations.SET_BACKEND_ERROR](state: SupervisionStoreState, error: Partial<AxiosError> | null) {
        if (!error) return (state.backendError = error);
        const data = error.response?.data as any;
        const backendError: BackendError = {
            message: data?.message ?? data?.error,
            status: error.response?.status || 404,
        };

        state.backendError = backendError;
    },
    [SupervisionMutations.SET_SUPERVISION_QUESTIONS](state: SupervisionStoreState, questions: SupervisionQuestions) {
        state.supervisionQuestions.push(...questions);
    },
    [SupervisionMutations.SET_TABLE_SORT](
        state: SupervisionStoreState,
        payload: { order: ExpertQuestionRequestOptions['order']; sort: DefaultSortOptions }
    ) {
        state.supervisionQuestionTable.order = payload.order;
        state.supervisionQuestionTable.sort = payload.sort;
    },
    [SupervisionMutations.SET_UPDATE_MESSAGE](state: SupervisionStoreState, message: string | null) {
        state.updateMessage = message;
    },
    [SupervisionMutations.STOP_POLLING_SELECTED](state: SupervisionStoreState) {
        if (!state.pollSelected.polling) return;
        clearInterval(state.pollSelected.polling);
    },
    [SupervisionMutations.STOP_POLLING_SUPERVISION_QUESTIONS](state: SupervisionStoreState) {
        if (!state.pollSupervisionQuestions.polling) return;
        clearInterval(state.pollSupervisionQuestions.polling);
    },
    [SupervisionMutations.UPDATE_QUESTION_IN_TABLE](state: SupervisionStoreState, question: ExpertQuestionResponse) {
        const questionIndexInTable = state.supervisionQuestions.findIndex((q) => q.uuid === question.uuid);
        state.supervisionQuestions.splice(questionIndexInTable, 1, question);
    },
};

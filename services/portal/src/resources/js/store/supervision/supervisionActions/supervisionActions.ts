import type { Commit, Dispatch } from 'vuex';
import { supervisionApi } from '@dbco/portal-api';
import { SupervisionMutations } from '../supervisionMutations/supervisionMutations';
import type { ExpertQuestionResponse } from '@dbco/portal-api/supervision.dto';
import type { RootStoreState } from '@/store';
import type { SupervisionStoreState } from '../supervisionStore';
import i18n from '@/i18n/index';
import type { PaginatedResponse } from '@dbco/portal-api/pagination';
import { DefaultSortOptions } from '@dbco/portal-api/pagination';

export enum SupervisionActions {
    ANSWER_QUESTION = 'ANSWER_QUESTION',
    ASK_QUESTION = 'ASK_QUESTION',
    CHECK_IF_SUPERVISION_QUESTIONS_OUTDATED = 'CHECK_IF_SUPERVISION_QUESTIONS_OUTDATED',
    CHECK_QUESTION_AVAILABILITY = 'CHECK_QUESTION_AVAILABILITY',
    DROP_QUESTION = 'DROP_QUESTION',
    FETCH_QUESTIONS = 'FETCH_QUESTIONS',
    FIND_QUESTION_BY_CASE_ID = 'FIND_QUESTION_BY_CASE_ID',
    PICK_UP_QUESTION = 'PICK_UP_QUESTION',
    SELECT_QUESTION = 'SELECT_QUESTION',
    START_POLLING_SELECTED = 'START_POLLING_SELECTED',
    START_POLLING_SUPERVISION_QUESTIONS = 'START_POLLING_SUPERVISION_QUESTIONS',
}

const answerQuestion = async ({ commit, state }: { commit: Commit; state: SupervisionStoreState }, answer: string) => {
    if (!state.selectedQuestion) return;
    const question = await supervisionApi.answerQuestion(state.selectedQuestion?.uuid, answer);
    commit(SupervisionMutations.STOP_POLLING_SELECTED);
    commit(SupervisionMutations.SELECT_QUESTION, question);
    commit(SupervisionMutations.UPDATE_QUESTION_IN_TABLE, question);
};

const askQuestion = async (
    { commit }: { commit: Commit },
    payload: { uuid: string; question: ExpertQuestionResponse }
) => {
    const question = await supervisionApi.askQuestion(payload.uuid, payload.question);
    commit(SupervisionMutations.ASK_QUESTION, question);
    return question;
};

const checkIfSupervisionQuestionsOutdated = async ({
    commit,
    state,
}: {
    commit: Commit;
    state: SupervisionStoreState;
}) => {
    if (!state.activeRole || !state.pollSupervisionQuestions.pollStartedAt) return;
    const { data }: PaginatedResponse<ExpertQuestionResponse> = await supervisionApi.getQuestions({
        order: 'desc',
        page: 1,
        perPage: 1,
        sort: DefaultSortOptions.CREATED_AT,
        type: state.activeRole,
    });
    if (data.length) {
        const lastQuestionAskedAt = new Date(data[0].createdAt);
        const hasUpdate = lastQuestionAskedAt.valueOf() - state.pollSupervisionQuestions.pollStartedAt.valueOf() > 0;
        if (hasUpdate) {
            commit(SupervisionMutations.SET_UPDATE_MESSAGE, i18n.t('components.questionTable.update_message'));
            commit(SupervisionMutations.STOP_POLLING_SUPERVISION_QUESTIONS);
        }
    }
};

const checkQuestionAvailability = async ({ commit, state }: { commit: Commit; state: SupervisionStoreState }) => {
    if (!state.selectedQuestion?.uuid) return;
    try {
        await supervisionApi.getQuestion(state.selectedQuestion.uuid);
    } catch (error) {
        commit(SupervisionMutations.SET_BACKEND_ERROR, error);
        commit(SupervisionMutations.DESELECT_QUESTION);
    }
};

const dropQuestion = async ({ commit }: { commit: Commit }, uuid: string) => {
    try {
        const question = await supervisionApi.dropQuestion(uuid);
        commit(SupervisionMutations.SELECT_QUESTION, question);
        commit(SupervisionMutations.UPDATE_QUESTION_IN_TABLE, question);
    } catch (error) {
        commit(SupervisionMutations.SET_BACKEND_ERROR, error);
    }
};

const fetchQuestions = async ({
    commit,
    dispatch,
    state,
}: {
    commit: Commit;
    dispatch: Dispatch;
    state: SupervisionStoreState;
}) => {
    if (!state.activeRole) return;
    const { data, lastPage }: PaginatedResponse<ExpertQuestionResponse> = await supervisionApi.getQuestions({
        order: state.supervisionQuestionTable.order,
        page: state.supervisionQuestionTable.page,
        perPage: state.supervisionQuestionTable.perPage,
        sort: state.supervisionQuestionTable.sort,
        type: state.activeRole,
    });
    commit(SupervisionMutations.SET_SUPERVISION_QUESTIONS, data);
    await dispatch(SupervisionActions.START_POLLING_SUPERVISION_QUESTIONS);
    return lastPage;
};

const findQuestionByCaseId = async (
    { commit, dispatch, state }: { commit: Commit; dispatch: Dispatch; state: SupervisionStoreState },
    caseId: string
) => {
    if (!state.activeRole) return;
    try {
        const question: ExpertQuestionResponse = await supervisionApi.findQuestionByCaseId(caseId, state.activeRole);
        commit(SupervisionMutations.SELECT_QUESTION, question);
        void dispatch(SupervisionActions.START_POLLING_SELECTED);
    } catch (error) {
        commit(SupervisionMutations.SET_BACKEND_ERROR, error);
        commit(SupervisionMutations.DESELECT_QUESTION);
    }
};

const pickupQuestion = async ({ commit, rootState }: { commit: Commit; rootState: RootStoreState }, uuid: string) => {
    if (!rootState.userInfo.user?.uuid.length) return;
    try {
        const question: ExpertQuestionResponse = await supervisionApi.pickupQuestion(
            uuid,
            rootState.userInfo.user?.uuid
        );
        commit(SupervisionMutations.SELECT_QUESTION, question);
        commit(SupervisionMutations.UPDATE_QUESTION_IN_TABLE, question);
    } catch (error) {
        commit(SupervisionMutations.SET_BACKEND_ERROR, error);
        commit(SupervisionMutations.DESELECT_QUESTION);
    }
};

const selectQuestion = async ({ commit, dispatch }: { commit: Commit; dispatch: Dispatch }, uuid: string) => {
    try {
        const question: ExpertQuestionResponse = await supervisionApi.getQuestion(uuid);
        commit(SupervisionMutations.SELECT_QUESTION, question);
        void dispatch(SupervisionActions.START_POLLING_SELECTED);
    } catch (error) {
        commit(SupervisionMutations.SET_BACKEND_ERROR, error);
        commit(SupervisionMutations.DESELECT_QUESTION);
    }
};

const startPollingSelected = ({ dispatch, state }: { dispatch: Dispatch; state: SupervisionStoreState }) => {
    if (state.pollSelected.polling) clearInterval(state.pollSelected.polling);
    state.pollSelected.polling = setInterval(
        () => dispatch(SupervisionActions.CHECK_QUESTION_AVAILABILITY),
        state.pollSelected.pollInterval
    );
};

const startPollingSupervisionQuestions = ({
    dispatch,
    state,
}: {
    dispatch: Dispatch;
    state: SupervisionStoreState;
}) => {
    if (state.pollSupervisionQuestions.polling) clearInterval(state.pollSupervisionQuestions.polling);
    state.pollSupervisionQuestions.pollStartedAt = new Date();
    state.pollSupervisionQuestions.polling = setInterval(
        () => dispatch(SupervisionActions.CHECK_IF_SUPERVISION_QUESTIONS_OUTDATED),
        state.pollSupervisionQuestions.pollInterval
    );
};

export const supervisionActions = {
    [SupervisionActions.ANSWER_QUESTION]: answerQuestion,
    [SupervisionActions.ASK_QUESTION]: askQuestion,
    [SupervisionActions.CHECK_IF_SUPERVISION_QUESTIONS_OUTDATED]: checkIfSupervisionQuestionsOutdated,
    [SupervisionActions.CHECK_QUESTION_AVAILABILITY]: checkQuestionAvailability,
    [SupervisionActions.DROP_QUESTION]: dropQuestion,
    [SupervisionActions.FETCH_QUESTIONS]: fetchQuestions,
    [SupervisionActions.FIND_QUESTION_BY_CASE_ID]: findQuestionByCaseId,
    [SupervisionActions.PICK_UP_QUESTION]: pickupQuestion,
    [SupervisionActions.SELECT_QUESTION]: selectQuestion,
    [SupervisionActions.START_POLLING_SELECTED]: startPollingSelected,
    [SupervisionActions.START_POLLING_SUPERVISION_QUESTIONS]: startPollingSupervisionQuestions,
};

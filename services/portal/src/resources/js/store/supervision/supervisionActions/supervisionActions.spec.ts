import { createLocalVue } from '@vue/test-utils';
import Vuex from 'vuex';
import type { SupervisionQuestions, SupervisionStoreState } from '../supervisionStore';
import supervisionStore from '../supervisionStore';
import { SupervisionRoles } from '@dbco/portal-api/user';
import type { UserInfoState } from '@/store/userInfo/userInfoStore';
import userInfoStore from '@/store/userInfo/userInfoStore';
import type { RootStoreState } from '@/store';
import { supervisionApi } from '@dbco/portal-api';
import type { ExpertQuestionResponse } from '@dbco/portal-api/supervision.dto';
import { ExpertQuestionTypeV1 } from '@dbco/enum';
import { fakerjs } from '@/utils/test';

const question: ExpertQuestionResponse = {
    caseUuid: '',
    createdAt: '',
    assignedUser: null,
    phone: '',
    question: '',
    subject: '',
    type: ExpertQuestionTypeV1.VALUE_medical_supervision,
    updatedAt: '',
    user: {
        name: '',
        roles: [],
        uuid: '',
    },
    uuid: '1234',
    answer: null,
    caseOrganisationName: null,
};

const questions: SupervisionQuestions = [
    {
        answer: null,
        caseUuid: '',
        createdAt: '2022-04-06T12:35:13.295Z',
        assignedUser: null,
        phone: '',
        question: '',
        subject: '',
        type: ExpertQuestionTypeV1.VALUE_medical_supervision,
        updatedAt: '',
        uuid: '',
        user: {
            name: '',
            roles: [],
            uuid: '',
        },
        caseOrganisationName: null,
    },
    {
        answer: null,
        caseUuid: '',
        createdAt: '2022-04-05T12:35:13.295Z',
        assignedUser: null,
        phone: '',
        question: '',
        subject: '',
        type: ExpertQuestionTypeV1.VALUE_medical_supervision,
        updatedAt: '',
        uuid: '',
        user: {
            name: '',
            roles: [],
            uuid: '',
        },
        caseOrganisationName: null,
    },
];

describe('supervisionActions.ts', () => {
    afterEach(() => {
        vi.clearAllMocks();
    });
    const localVue = createLocalVue();
    localVue.use(Vuex);
    vi.useFakeTimers();

    const getStore = (
        supervisionStoreState: Partial<SupervisionStoreState>,
        userInfoState?: Partial<UserInfoState>
    ) => {
        const supervisionStoreModule = {
            ...supervisionStore,
            state: {
                ...supervisionStore.state,
                ...supervisionStoreState,
            },
            actions: supervisionStore.actions,
        };

        const userInfoStoreModule = {
            ...userInfoStore,
            state: {
                ...userInfoStore.state,
                ...userInfoState,
            },
        };

        return new Vuex.Store<RootStoreState>({
            modules: {
                supervision: supervisionStoreModule,
                userInfo: userInfoStoreModule,
            },
        });
    };

    it('should call api and set result in store when supervision/ANSWER_QUESTION is dispatched', async () => {
        const spyOnApi = vi.spyOn(supervisionApi, 'answerQuestion').mockImplementation(() => Promise.resolve(question));
        const supervisionStoreState: Partial<SupervisionStoreState> = {
            questions: [],
            selectedQuestion: {
                answer: null,
                caseUuid: '',
                createdAt: '',
                assignedUser: null,
                phone: '',
                question: '',
                subject: '',
                type: ExpertQuestionTypeV1.VALUE_medical_supervision,
                updatedAt: '',
                user: {
                    name: '',
                    roles: [],
                    uuid: '',
                },
                uuid: '1234',
                caseOrganisationName: null,
            },
        };
        const store = getStore(supervisionStoreState);
        const spyOnCommit = vi.spyOn(store, 'commit');
        await store.dispatch('supervision/ANSWER_QUESTION', 'Test antwoord');

        expect(spyOnApi).toHaveBeenCalledWith('1234', 'Test antwoord');
        expect(spyOnCommit).toHaveBeenNthCalledWith(1, 'supervision/STOP_POLLING_SELECTED', undefined, undefined);
        expect(spyOnCommit).toHaveBeenNthCalledWith(2, 'supervision/SELECT_QUESTION', question, undefined);
        expect(spyOnCommit).toHaveBeenNthCalledWith(3, 'supervision/UPDATE_QUESTION_IN_TABLE', question, undefined);
    });

    it('should do nothing when supervision/ANSWER_QUESTION is dispatched and selectedQuestion is not set', async () => {
        const spyOnApi = vi.spyOn(supervisionApi, 'answerQuestion').mockImplementation(() => Promise.resolve(question));
        const supervisionStoreState: Partial<SupervisionStoreState> = {
            questions: [],
            selectedQuestion: null,
        };
        const store = getStore(supervisionStoreState);
        await store.dispatch('supervision/ANSWER_QUESTION', 'Test antwoord');

        expect(spyOnApi).toHaveBeenCalledTimes(0);
    });

    it('should call api and set result in store when supervision/ASK_QUESTION is dispatched', async () => {
        const spyOnApi = vi.spyOn(supervisionApi, 'askQuestion').mockImplementation(() => Promise.resolve(question));
        const supervisionStoreState: Partial<SupervisionStoreState> = {
            questions: [],
        };
        const store = getStore(supervisionStoreState);
        const spyOnCommit = vi.spyOn(store, 'commit');
        await store.dispatch('supervision/ASK_QUESTION', {
            question,
            uuid: '1234',
        });

        expect(spyOnApi).toHaveBeenCalledWith('1234', question);
        expect(spyOnCommit).toHaveBeenCalledWith('supervision/ASK_QUESTION', question, undefined);
    });

    it('should do nothing if state.activeRole is not set and supervision/CHECK_IF_SUPERVISION_QUESTIONS_OUTDATED is dispatched', async () => {
        const spyOnApi = vi.spyOn(supervisionApi, 'getQuestions').mockImplementation(() =>
            Promise.resolve({
                currentPage: 1,
                data: [question],
                from: 0,
                lastPage: 1,
                to: 2,
                total: 1,
            })
        );
        const supervisionStoreState: Partial<SupervisionStoreState> = {
            questions: [],
        };
        const store = getStore(supervisionStoreState);
        await store.dispatch('supervision/CHECK_IF_SUPERVISION_QUESTIONS_OUTDATED');

        expect(spyOnApi).toHaveBeenCalledTimes(0);
    });

    it('should do nothing given supervision/CHECK_IF_SUPERVISION_QUESTIONS_OUTDATED is dispatched when there is no data', async () => {
        vi.spyOn(supervisionApi, 'getQuestions').mockImplementation(() =>
            Promise.resolve({
                currentPage: 1,
                data: [],
                from: 0,
                lastPage: 1,
                to: 2,
                total: 1,
            })
        );
        const supervisionStoreState: Partial<SupervisionStoreState> = {
            activeRole: SupervisionRoles.MEDICAL_SUPERVISION,
            questions: [],
        };
        const store = getStore(supervisionStoreState);
        const spyOnCommit = vi.spyOn(store, 'commit');

        await store.dispatch('supervision/CHECK_IF_SUPERVISION_QUESTIONS_OUTDATED');

        expect(spyOnCommit).toHaveBeenCalledTimes(0);
    });

    it('should not set update message when supervision/CHECK_IF_SUPERVISION_QUESTIONS_OUTDATED is dispatched and there are no new questions', async () => {
        const spyOnApi = vi.spyOn(supervisionApi, 'getQuestions').mockImplementation(() =>
            Promise.resolve({
                currentPage: 1,
                data: [question],
                from: 0,
                lastPage: 1,
                to: 2,
                total: 1,
            })
        );
        const supervisionStoreState: Partial<SupervisionStoreState> = {
            activeRole: SupervisionRoles.MEDICAL_SUPERVISION,
            pollSupervisionQuestions: {
                pollInterval: 10,
                polling: null,
                pollStartedAt: new Date(),
            },
            questions: [],
        };
        const store = getStore(supervisionStoreState);
        const spyOnCommit = vi.spyOn(store, 'commit');
        await store.dispatch('supervision/CHECK_IF_SUPERVISION_QUESTIONS_OUTDATED');
        expect(spyOnApi).toHaveBeenCalledWith({
            order: 'desc',
            page: 1,
            perPage: 1,
            sort: 'createdAt',
            type: 'medical-supervision',
        });
        expect(spyOnCommit).toHaveBeenCalledTimes(0);
    });

    it('should set update message when supervision/CHECK_IF_SUPERVISION_QUESTIONS_OUTDATED is dispatched and there are new questions', async () => {
        const creationDate = fakerjs.date.recent().toISOString();
        vi.spyOn(supervisionApi, 'getQuestions').mockImplementation(() =>
            Promise.resolve({
                currentPage: 1,
                data: [{ ...question, ...{ createdAt: creationDate } }],
                from: 0,
                lastPage: 1,
                to: 2,
                total: 1,
            })
        );
        const supervisionStoreState: Partial<SupervisionStoreState> = {
            activeRole: SupervisionRoles.MEDICAL_SUPERVISION,
            pollSupervisionQuestions: {
                pollInterval: 10,
                polling: null,
                pollStartedAt: fakerjs.date.past({ years: 1, refDate: creationDate }),
            },
            questions: [],
        };
        const store = getStore(supervisionStoreState);
        const spyOnCommit = vi.spyOn(store, 'commit');
        await store.dispatch('supervision/CHECK_IF_SUPERVISION_QUESTIONS_OUTDATED');

        expect(spyOnCommit).toHaveBeenNthCalledWith(
            1,
            'supervision/SET_UPDATE_MESSAGE',
            'Er zijn 1 of meerdere vragen toegevoegd',
            undefined
        );
        expect(spyOnCommit).toHaveBeenNthCalledWith(
            2,
            'supervision/STOP_POLLING_SUPERVISION_QUESTIONS',
            undefined,
            undefined
        );
    });

    it('should do nothing if state.selectedQuestion.uuid is not set and supervision/CHECK_QUESTION_AVAILABILITY is dispatched', async () => {
        const spyOnApi = vi.spyOn(supervisionApi, 'getQuestion').mockImplementation(() => Promise.resolve(question));
        const supervisionStoreState: Partial<SupervisionStoreState> = {
            questions: [],
        };
        const store = getStore(supervisionStoreState);
        await store.dispatch('supervision/CHECK_QUESTION_AVAILABILITY');

        expect(spyOnApi).toHaveBeenCalledTimes(0);
    });

    it('should call api when supervision/CHECK_QUESTION_AVAILABILITY is dispatched and do nothing if there is no backend error', async () => {
        const spyOnApi = vi.spyOn(supervisionApi, 'getQuestion').mockImplementation(() => Promise.resolve(question));
        const supervisionStoreState: Partial<SupervisionStoreState> = {
            questions: [],
            selectedQuestion: question,
        };
        const store = getStore(supervisionStoreState);
        await store.dispatch('supervision/CHECK_QUESTION_AVAILABILITY');

        expect(spyOnApi).toHaveBeenCalledWith(question.uuid);
    });

    it('should call api when supervision/CHECK_QUESTION_AVAILABILITY is dispatched and set backend error in store if present', async () => {
        vi.spyOn(supervisionApi, 'getQuestion').mockImplementation(() => Promise.reject('error'));
        const supervisionStoreState: Partial<SupervisionStoreState> = {
            questions: [],
            selectedQuestion: question,
        };
        const store = getStore(supervisionStoreState);
        const spyOnCommit = vi.spyOn(store, 'commit');
        await store.dispatch('supervision/CHECK_QUESTION_AVAILABILITY');

        expect(spyOnCommit).toHaveBeenNthCalledWith(1, 'supervision/SET_BACKEND_ERROR', 'error', undefined);
        expect(spyOnCommit).toHaveBeenNthCalledWith(2, 'supervision/DESELECT_QUESTION', undefined, undefined);
    });

    it('should call api and update store when supervision/DROP_QUESTION is dispatched', async () => {
        const spyOnApi = vi.spyOn(supervisionApi, 'dropQuestion').mockImplementation(() => Promise.resolve(question));
        const supervisionStoreState: Partial<SupervisionStoreState> = {
            questions: [],
        };
        const store = getStore(supervisionStoreState);
        const spyOnCommit = vi.spyOn(store, 'commit');
        await store.dispatch('supervision/DROP_QUESTION', '1234');

        expect(spyOnApi).toHaveBeenCalledWith('1234');
        expect(spyOnCommit).toHaveBeenNthCalledWith(1, 'supervision/SELECT_QUESTION', question, undefined);
        expect(spyOnCommit).toHaveBeenNthCalledWith(2, 'supervision/UPDATE_QUESTION_IN_TABLE', question, undefined);
    });

    it('should commit backend error to store when dropQuestion call is unsuccessful', async () => {
        const spyOnApi = vi.spyOn(supervisionApi, 'dropQuestion').mockImplementation(() => Promise.reject('error'));
        const supervisionStoreState: Partial<SupervisionStoreState> = {
            questions: [],
        };
        const store = getStore(supervisionStoreState);
        const spyOnCommit = vi.spyOn(store, 'commit');
        await store.dispatch('supervision/DROP_QUESTION', '1234');

        expect(spyOnApi).toHaveBeenCalledWith('1234');
        expect(spyOnCommit).toHaveBeenCalledWith('supervision/SET_BACKEND_ERROR', 'error', undefined);
    });

    it('should do nothing if state.activeRole is not set and supervision/FETCH_QUESTIONS is dispatched', async () => {
        const spyOnApi = vi.spyOn(supervisionApi, 'getQuestions').mockImplementation(() =>
            Promise.resolve({
                currentPage: 1,
                data: questions,
                from: 0,
                lastPage: 1,
                to: 2,
                total: 1,
            })
        );
        const supervisionStoreState: Partial<SupervisionStoreState> = {
            questions: [],
        };
        const store = getStore(supervisionStoreState);
        await store.dispatch('supervision/FETCH_QUESTIONS');

        expect(spyOnApi).toHaveBeenCalledTimes(0);
    });

    it('should call api and update store when supervision/FETCH_QUESTIONS is dispatched', async () => {
        const spyOnApi = vi.spyOn(supervisionApi, 'getQuestions').mockImplementation(() =>
            Promise.resolve({
                currentPage: 1,
                data: questions,
                from: 0,
                lastPage: 1,
                to: 2,
                total: 1,
            })
        );
        const supervisionStoreState: Partial<SupervisionStoreState> = {
            activeRole: SupervisionRoles.MEDICAL_SUPERVISION,
            questions: [],
            supervisionQuestions: [],
        };
        const store = getStore(supervisionStoreState);
        const spyOnCommit = vi.spyOn(store, 'commit');
        const spyOnDispatch = vi.spyOn(store, 'dispatch');
        await store.dispatch('supervision/FETCH_QUESTIONS');

        expect(spyOnApi).toHaveBeenCalledWith({ page: 1, perPage: 20, type: 'medical-supervision' });
        expect(spyOnCommit).toHaveBeenCalledWith('supervision/SET_SUPERVISION_QUESTIONS', questions, undefined);
        expect(spyOnDispatch).toHaveBeenNthCalledWith(2, 'supervision/START_POLLING_SUPERVISION_QUESTIONS', undefined);
    });

    it('should do nothing if state.activeRole is not set and supervision/FIND_QUESTION_BY_CASE_ID is dispatched', async () => {
        const spyOnApi = vi
            .spyOn(supervisionApi, 'findQuestionByCaseId')
            .mockImplementation(() => Promise.resolve(question));
        const supervisionStoreState: Partial<SupervisionStoreState> = {
            selectedQuestion: null,
        };
        const store = getStore(supervisionStoreState);
        await store.dispatch('supervision/FIND_QUESTION_BY_CASE_ID');

        expect(spyOnApi).toHaveBeenCalledTimes(0);
    });

    it('should call api and update store when supervision/FIND_QUESTION_BY_CASE_ID is dispatched', async () => {
        vi.spyOn(supervisionApi, 'findQuestionByCaseId').mockImplementation(() => Promise.resolve(question));
        const supervisionStoreState: Partial<SupervisionStoreState> = {
            activeRole: SupervisionRoles.MEDICAL_SUPERVISION,
        };
        const store = getStore(supervisionStoreState);
        const spyOnCommit = vi.spyOn(store, 'commit');
        const spyOnDispatch = vi.spyOn(store, 'dispatch');
        await store.dispatch('supervision/FIND_QUESTION_BY_CASE_ID');

        expect(spyOnCommit).toHaveBeenCalledWith('supervision/SELECT_QUESTION', question, undefined);
        expect(spyOnDispatch).toHaveBeenNthCalledWith(2, 'supervision/START_POLLING_SELECTED', undefined);
    });

    it('should set backendError in store when supervision/FIND_QUESTION_BY_CASE_ID is dispatched and api call fails', async () => {
        vi.spyOn(supervisionApi, 'findQuestionByCaseId').mockImplementation(() => Promise.reject('error'));
        const supervisionStoreState: Partial<SupervisionStoreState> = {
            activeRole: SupervisionRoles.MEDICAL_SUPERVISION,
        };
        const store = getStore(supervisionStoreState);
        const spyOnCommit = vi.spyOn(store, 'commit');
        await store.dispatch('supervision/FIND_QUESTION_BY_CASE_ID');

        expect(spyOnCommit).toHaveBeenNthCalledWith(1, 'supervision/SET_BACKEND_ERROR', 'error', undefined);
        expect(spyOnCommit).toHaveBeenNthCalledWith(2, 'supervision/DESELECT_QUESTION', undefined, undefined);
    });

    it('should do nothing if userInfo.user.uuid is not set and supervision/PICK_UP_QUESTION is dispatched', async () => {
        const spyOnApi = vi.spyOn(supervisionApi, 'pickupQuestion').mockImplementation(() => Promise.resolve(question));
        const supervisionStoreState: Partial<SupervisionStoreState> = {
            selectedQuestion: null,
        };
        const store = getStore(supervisionStoreState);
        await store.dispatch('supervision/PICK_UP_QUESTION');

        expect(spyOnApi).toHaveBeenCalledTimes(0);
    });

    it('should call api and update store when supervision/PICK_UP_QUESTION is dispatched', async () => {
        vi.spyOn(supervisionApi, 'pickupQuestion').mockImplementation(() => Promise.resolve(question));
        const supervisionStoreState: Partial<SupervisionStoreState> = {
            selectedQuestion: null,
        };
        const userInfoState: Partial<UserInfoState> = {
            user: {
                name: '',
                roles: [],
                uuid: '1234',
            },
        };
        const store = getStore(supervisionStoreState, userInfoState);
        const spyOnCommit = vi.spyOn(store, 'commit');
        await store.dispatch('supervision/PICK_UP_QUESTION');

        expect(spyOnCommit).toHaveBeenCalledWith('supervision/SELECT_QUESTION', question, undefined);
        expect(spyOnCommit).toHaveBeenNthCalledWith(2, 'supervision/UPDATE_QUESTION_IN_TABLE', question, undefined);
    });

    it('should set backendError in store when supervision/PICK_UP_QUESTION is dispatched and api call fails', async () => {
        vi.spyOn(supervisionApi, 'pickupQuestion').mockImplementation(() => Promise.reject('error'));
        const supervisionStoreState: Partial<SupervisionStoreState> = {
            selectedQuestion: null,
        };
        const userInfoState: Partial<UserInfoState> = {
            user: {
                name: '',
                roles: [],
                uuid: '1234',
            },
        };
        const store = getStore(supervisionStoreState, userInfoState);
        const spyOnCommit = vi.spyOn(store, 'commit');
        await store.dispatch('supervision/PICK_UP_QUESTION');

        expect(spyOnCommit).toHaveBeenNthCalledWith(1, 'supervision/SET_BACKEND_ERROR', 'error', undefined);
        expect(spyOnCommit).toHaveBeenNthCalledWith(2, 'supervision/DESELECT_QUESTION', undefined, undefined);
    });

    it('should call api and update store when supervision/SELECT_QUESTION is dispatched', async () => {
        vi.spyOn(supervisionApi, 'getQuestion').mockImplementation(() => Promise.resolve(question));
        const supervisionStoreState: Partial<SupervisionStoreState> = {
            selectedQuestion: null,
        };
        const store = getStore(supervisionStoreState);
        const spyOnCommit = vi.spyOn(store, 'commit');
        const spyOnDispatch = vi.spyOn(store, 'dispatch');
        await store.dispatch('supervision/SELECT_QUESTION');

        expect(spyOnCommit).toHaveBeenCalledWith('supervision/SELECT_QUESTION', question, undefined);
        expect(spyOnDispatch).toHaveBeenNthCalledWith(2, 'supervision/START_POLLING_SELECTED', undefined);
    });

    it('should set backendError in store when supervision/SELECT_QUESTION is dispatched and api call fails', async () => {
        vi.spyOn(supervisionApi, 'getQuestion').mockImplementation(() => Promise.reject('error'));
        const supervisionStoreState: Partial<SupervisionStoreState> = {
            selectedQuestion: null,
        };
        const store = getStore(supervisionStoreState);
        const spyOnCommit = vi.spyOn(store, 'commit');
        await store.dispatch('supervision/SELECT_QUESTION');

        expect(spyOnCommit).toHaveBeenNthCalledWith(1, 'supervision/SET_BACKEND_ERROR', 'error', undefined);
        expect(spyOnCommit).toHaveBeenNthCalledWith(2, 'supervision/DESELECT_QUESTION', undefined, undefined);
    });

    it('should dispatch CHECK_QUESTION_AVAILABILITY on pollInterval when supervision/START_POLLING_SELECTED is dispatched', async () => {
        const supervisionStoreState: Partial<SupervisionStoreState> = {
            pollSelected: {
                pollInterval: 499,
                polling: null,
                pollStartedAt: new Date(),
            },
        };
        const store = getStore(supervisionStoreState);
        const spyOnDispatch = vi.spyOn(store, 'dispatch');
        await store.dispatch('supervision/START_POLLING_SELECTED');

        vi.advanceTimersByTime(1000);
        expect(spyOnDispatch).toHaveBeenCalledTimes(3);
        expect(spyOnDispatch).toHaveBeenNthCalledWith(2, 'supervision/CHECK_QUESTION_AVAILABILITY', undefined);
    });

    it('should dispatch CHECK_IF_SUPERVISION_QUESTIONS_OUTDATED on pollInterval when supervision/START_POLLING_SUPERVISION_QUESTIONS is dispatched', async () => {
        const supervisionStoreState: Partial<SupervisionStoreState> = {
            pollSupervisionQuestions: {
                pollInterval: 499,
                polling: setInterval(() => {}, 10),
                pollStartedAt: new Date(),
            },
        };
        const store = getStore(supervisionStoreState);
        const spyOnDispatch = vi.spyOn(store, 'dispatch');
        await store.dispatch('supervision/START_POLLING_SUPERVISION_QUESTIONS');

        vi.advanceTimersByTime(1000);
        expect(spyOnDispatch).toHaveBeenCalledTimes(3);
        expect(spyOnDispatch).toHaveBeenNthCalledWith(
            2,
            'supervision/CHECK_IF_SUPERVISION_QUESTIONS_OUTDATED',
            undefined
        );
    });
});

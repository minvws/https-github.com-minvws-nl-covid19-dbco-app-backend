import { createLocalVue } from '@vue/test-utils';
import Vuex from 'vuex';
import type { SupervisionStoreState } from '../supervisionStore';
import supervisionStore from '../supervisionStore';
import { SupervisionRoles } from '@dbco/portal-api/user';
import type { RootStoreState } from '@/store';
import type { ExpertQuestionResponse } from '@dbco/portal-api/supervision.dto';
import { ExpertQuestionTypeV1 } from '@dbco/enum';
import { flushCallStack } from '@/utils/test';
import type { AxiosError } from 'axios';
import { DefaultSortOptions } from '@dbco/portal-api/pagination';
import type { BackendError } from '@dbco/portal-api/error';

const backendErrorDefault: BackendError = {
    message: 'Test backendError',
    status: 404,
};

const errorWithError: Partial<AxiosError> | null = {
    response: {
        config: {} as any, // eslint-disable-line @typescript-eslint/no-explicit-any
        data: {
            error: 'Test backendError',
        },
        headers: {},
        status: 404,
        statusText: '',
    },
};

const errorWithMessage: Partial<AxiosError> | null = {
    response: {
        config: {} as any, // eslint-disable-line @typescript-eslint/no-explicit-any
        data: {
            message: 'Test backendError',
        },
        headers: {},
        status: 404,
        statusText: '',
    },
};

const errorWithoutStatus: Partial<AxiosError> | null = {
    response: {
        config: {} as any, // eslint-disable-line @typescript-eslint/no-explicit-any
        data: {
            message: 'Test backendError',
        },
        headers: {},
        status: 0,
        statusText: '',
    },
};

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
    uuid: '',
    answer: null,
    caseOrganisationName: null,
};

describe('SupervisionMutations.ts', () => {
    const localVue = createLocalVue();
    localVue.use(Vuex);

    vi.stubGlobal('clearInterval', vi.fn());

    const getStore = (supervisionStoreState: Partial<SupervisionStoreState>) => {
        const supervisionStoreModule = {
            ...supervisionStore,
            state: {
                ...supervisionStore.state,
                ...supervisionStoreState,
            },
            mutations: supervisionStore.mutations,
        };

        return new Vuex.Store<RootStoreState>({
            modules: {
                supervision: supervisionStoreModule,
            },
        });
    };

    beforeEach(() => {
        vi.restoreAllMocks();
    });

    it('should add question to store when ASK_QUESTION is committed', async () => {
        const supervisionStoreState: Partial<SupervisionStoreState> = {
            questions: [],
        };
        const store = getStore(supervisionStoreState);
        await store.commit('supervision/ASK_QUESTION', question);

        expect(store.state.supervision.questions[0]).toBe(question);
    });

    it('should set selectedQuestion to null in store when DESELECT_QUESTION is committed', async () => {
        const supervisionStoreState: Partial<SupervisionStoreState> = {
            selectedQuestion: question,
        };
        const store = getStore(supervisionStoreState);
        await store.commit('supervision/DESELECT_QUESTION');

        expect(store.state.supervision.selectedQuestion).toBe(null);
    });

    it('should increment table page in store when INCREMENT_TABLE_PAGE is committed', async () => {
        const supervisionStoreState: Partial<SupervisionStoreState> = {
            supervisionQuestionTable: {
                infiniteId: Date.now(),
                page: 1,
                perPage: 20,
            },
        };
        const store = getStore(supervisionStoreState);
        await store.commit('supervision/INCREMENT_TABLE_PAGE');

        expect(store.state.supervision.supervisionQuestionTable.page).toBe(2);
    });

    it('should reset table related values in store when RESET_QUESTION_TABLE is committed', async () => {
        const supervisionStoreState: Partial<SupervisionStoreState> = {
            supervisionQuestions: [question],
            supervisionQuestionTable: {
                infiniteId: 1234,
                page: 2,
                perPage: 20,
            },
        };
        const store = getStore(supervisionStoreState);
        await store.commit('supervision/RESET_QUESTION_TABLE');

        expect(store.state.supervision.supervisionQuestionTable.infiniteId).not.toBe(1234);
        expect(store.state.supervision.supervisionQuestionTable.page).toBe(1);
        expect(store.state.supervision.supervisionQuestions).toStrictEqual([]);
    });

    it('should set selectedQuestion to given question when SELECT_QUESTION is committed', async () => {
        const supervisionStoreState: Partial<SupervisionStoreState> = {
            selectedQuestion: null,
        };
        const store = getStore(supervisionStoreState);
        await store.commit('supervision/SELECT_QUESTION', question);

        expect(store.state.supervision.selectedQuestion).toBe(question);
    });

    it('should set activeRole in store when SET_ACTIVE_ROLE is committed', async () => {
        const supervisionStoreState: Partial<SupervisionStoreState> = {
            activeRole: null,
        };
        const store = getStore(supervisionStoreState);
        await store.commit('supervision/SET_ACTIVE_ROLE', SupervisionRoles.CONVERSATION_COACH);

        expect(store.state.supervision.activeRole).toBe(SupervisionRoles.CONVERSATION_COACH);
    });

    it.each([
        ['with null value', null, null],
        ['with message', errorWithMessage, backendErrorDefault],
        ['with error', errorWithError, backendErrorDefault],
        ['without status', errorWithoutStatus, backendErrorDefault],
    ])(
        '%#: should set backendError "%s" in store when SET_BACKEND_ERROR is committed',
        async (testCase, givenError, expectedError) => {
            const supervisionStoreState: Partial<SupervisionStoreState> = {
                backendError: null,
            };
            const store = getStore(supervisionStoreState);
            await store.commit('supervision/SET_BACKEND_ERROR', givenError);

            expect(store.state.supervision.backendError).toStrictEqual(expectedError);
        }
    );

    it('should push given questions to supervisionQuestions when SET_SUPERVISION_QUESTIONS is committed', async () => {
        const supervisionStoreState: Partial<SupervisionStoreState> = {
            supervisionQuestions: [],
        };
        const store = getStore(supervisionStoreState);
        await store.commit('supervision/SET_SUPERVISION_QUESTIONS', [question]);

        expect(store.state.supervision.supervisionQuestions).toStrictEqual([question]);
    });

    it('should update table sort properties when SET_TABLE_SORT is committed', async () => {
        const supervisionStoreState: Partial<SupervisionStoreState> = {
            supervisionQuestionTable: {
                infiniteId: Date.now(),
                page: 1,
                perPage: 20,
            },
        };
        const store = getStore(supervisionStoreState);
        await store.commit('supervision/SET_TABLE_SORT', {
            order: 'asc',
            sort: DefaultSortOptions.CREATED_AT,
        });

        expect(store.state.supervision.supervisionQuestionTable.order).toBe('asc');
        expect(store.state.supervision.supervisionQuestionTable.sort).toBe(DefaultSortOptions.CREATED_AT);
    });

    it('should set updateMessage to given message when SET_UPDATE_MESSAGE is committed', async () => {
        const supervisionStoreState: Partial<SupervisionStoreState> = {
            updateMessage: undefined,
        };
        const store = getStore(supervisionStoreState);
        await store.commit('supervision/SET_UPDATE_MESSAGE', 'Test updateMessage');

        expect(store.state.supervision.updateMessage).toBe('Test updateMessage');
    });

    it('should clear pollSelected.polling when STOP_POLLING_SELECTED is committed', async () => {
        const supervisionStoreState: Partial<SupervisionStoreState> = {
            pollSelected: {
                polling: setInterval(() => {}, 10),
                pollInterval: 10,
            },
        };
        const store = getStore(supervisionStoreState);
        await store.commit('supervision/STOP_POLLING_SELECTED');
        await flushCallStack();

        expect(clearInterval).toHaveBeenCalled();
    });

    it('should do nothing when STOP_POLLING_SELECTED is committed and there is no poll to stop', async () => {
        const supervisionStoreState: Partial<SupervisionStoreState> = {
            pollSelected: {
                polling: null,
                pollInterval: 10,
            },
        };
        const store = getStore(supervisionStoreState);
        await store.commit('supervision/STOP_POLLING_SELECTED');
        await flushCallStack();

        expect(clearInterval).toHaveBeenCalledTimes(0);
    });

    it('should clear pollSupervisionQuestions.polling when STOP_POLLING_SUPERVISION_QUESTIONS is committed', async () => {
        const supervisionStoreState: Partial<SupervisionStoreState> = {
            pollSupervisionQuestions: {
                polling: setInterval(() => {}, 10),
                pollInterval: 10,
            },
        };
        const store = getStore(supervisionStoreState);
        await store.commit('supervision/STOP_POLLING_SUPERVISION_QUESTIONS');
        await flushCallStack();

        expect(clearInterval).toHaveBeenCalled();
    });

    it('should do nothing when STOP_POLLING_SUPERVISION_QUESTIONS is committed and there is no poll to stop', async () => {
        const supervisionStoreState: Partial<SupervisionStoreState> = {
            pollSupervisionQuestions: {
                polling: null,
                pollInterval: 10,
            },
        };
        const store = getStore(supervisionStoreState);
        await store.commit('supervision/STOP_POLLING_SUPERVISION_QUESTIONS');
        await flushCallStack();

        expect(clearInterval).toHaveBeenCalledTimes(0);
    });

    it('should update question in supervisionQuestions when UPDATE_QUESTION_IN_TABLE is committed', async () => {
        const supervisionStoreState: Partial<SupervisionStoreState> = {
            supervisionQuestions: [question, { ...question, ...{ uuid: '1234' } }],
        };
        const store = getStore(supervisionStoreState);
        await store.commit('supervision/UPDATE_QUESTION_IN_TABLE', {
            ...question,
            ...{ uuid: '1234', question: 'Test' },
        });
        await flushCallStack();

        expect(store.state.supervision.supervisionQuestions[1]).toStrictEqual({
            ...question,
            ...{ uuid: '1234', question: 'Test' },
        });
    });
});

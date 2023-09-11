import type { Wrapper } from '@vue/test-utils';
import { createLocalVue, shallowMount } from '@vue/test-utils';

import i18n from '@/i18n/index';
import Vuex, { Store } from 'vuex';
import type { SupervisionQuestions, SupervisionStoreState } from '@/store/supervision/supervisionStore';
import supervisionStore from '@/store/supervision/supervisionStore';

import userInfoStore from '@/store/userInfo/userInfoStore';
import { Role } from '@dbco/portal-api/user';

import type { StateChanger } from 'vue-infinite-loading';
import InfiniteLoading from 'vue-infinite-loading';
import QuestionTable from '@/components/supervision/QuestionTable/QuestionTable.vue';
import { ExpertQuestionTypeV1 } from '@dbco/enum';
import type { ExpertQuestionRequestOptions } from '@dbco/portal-api/supervision.dto';
import { DefaultSortOptions } from '@dbco/portal-api/pagination';

const mockedMutation = vi.fn();

const createStore = (
    questions?: SupervisionQuestions,
    userInfoStoreUser?: string,
    mockedFetchQuestions?: Promise<number>,
    mockedQuestionTable?: Partial<SupervisionStoreState['supervisionQuestionTable']>
) => {
    const supervisionQuestions: SupervisionQuestions = questions ?? [
        {
            caseUuid: '54a77721-bfe4-4e09-b4ac-e44b908ebcd3',
            createdAt: '2022-03-11T15:42:26.600Z',
            assignedUser: null,
            phone: '0612345678',
            question: 'Wat is een vraag?',
            subject: 'Onderwerp',
            type: ExpertQuestionTypeV1.VALUE_medical_supervision,
            updatedAt: '2022-03-11T15:42:26.600Z',
            user: {
                name: 'Bob BCOer',
                roles: [Role.user],
                uuid: '6e916779-a84f-4b4e-8a3d-b9e8fc446134',
            },
            uuid: '6a28c17c-1b54-48de-858e-d8f1aa8f0414',
            caseOrganisationName: 'GGD West-Brabant',
            answer: null,
        },
    ];

    const state: SupervisionStoreState = {
        activeRole: null,
        backendError: null,
        pollSelected: {
            pollInterval: 5000,
            polling: null,
        },
        pollSupervisionQuestions: {
            polling: null,
            pollInterval: 10000,
        },
        questions: [],
        selectedQuestion: null,
        supervisionQuestions,
        supervisionQuestionTable: {
            infiniteId: Date.now(),
            page: 1,
            perPage: 20,
        },
        updateMessage: null,
    };

    return new Store({
        modules: {
            supervision: {
                namespaced: true,
                state: {
                    ...state,
                    ...{
                        supervisionQuestionTable: { ...state.supervisionQuestionTable, ...mockedQuestionTable },
                    },
                },
                actions: {
                    FETCH_QUESTIONS: () => mockedFetchQuestions ?? Promise.resolve(2),
                    SELECT_QUESTION: () => mockedFetchQuestions ?? Promise.resolve(1),
                },
                mutations: {
                    INCREMENT_TABLE_PAGE: mockedMutation,
                    RESET_QUESTION_TABLE: mockedMutation,
                    SET_TABLE_SORT: mockedMutation,
                },
                getters: supervisionStore.getters,
            },
            userInfo: {
                namespaced: true,
                state: {
                    ...userInfoStore.state,
                    ...{
                        user: {
                            uuid: userInfoStoreUser,
                        },
                    },
                },
                getters: userInfoStore.getters,
            },
        },
    });
};

describe('QuestionTable.vue', () => {
    const localVue = createLocalVue();
    let wrapper: Wrapper<QuestionTable>;
    localVue.use(Vuex);

    const setWrapper = (
        questions?: SupervisionQuestions,
        userInfoStoreUser?: string,
        mockedFetchQuestions?: Promise<number>,
        mockedQuestionTable?: Partial<SupervisionStoreState['supervisionQuestionTable']>
    ) => {
        wrapper = shallowMount(QuestionTable, {
            localVue,
            i18n,
            store: createStore(questions, userInfoStoreUser, mockedFetchQuestions, mockedQuestionTable),
        });
    };

    it('should render a table', () => {
        setWrapper();

        expect(wrapper.find('table').exists()).toBeTruthy();
    });

    it('should render a translated status', () => {
        setWrapper();

        expect(wrapper.find('td:last-of-type').text()).toBe('Nog niet opgepakt');
    });

    it('should render a translated status when question is answered', () => {
        setWrapper([
            {
                caseUuid: '54a77721-bfe4-4e09-b4ac-e44b908ebcd3',
                createdAt: '2022-03-11T15:42:26.600Z',
                assignedUser: null,
                phone: '0612345678',
                question: 'Wat is een vraag?',
                subject: 'Onderwerp',
                type: ExpertQuestionTypeV1.VALUE_medical_supervision,
                updatedAt: '2022-03-11T15:42:26.600Z',
                user: {
                    name: 'Bob BCOer',
                    roles: [Role.user],
                    uuid: '6e916779-a84f-4b4e-8a3d-b9e8fc446134',
                },
                uuid: '6a28c17c-1b54-48de-858e-d8f1aa8f0414',
                caseOrganisationName: 'GGD West-Brabant',
                answer: {
                    value: 'Dit is een antwoord',
                    createdAt: '2022-03-23T15:55:00.000Z',
                    answeredBy: {
                        name: 'Sam Supervisor',
                        roles: [Role.medical_supervisor],
                        uuid: '25c099d7-48ef-43f5-a206-2f679d7af2a6',
                    },
                },
            },
        ]);

        expect(wrapper.find('td:last-of-type').text()).toBe('Beantwoord door jou');
    });

    it('should render a translated status when question is picked up', () => {
        setWrapper([
            {
                caseUuid: '54a77721-bfe4-4e09-b4ac-e44b908ebcd3',
                createdAt: '2022-03-11T15:42:26.600Z',
                assignedUser: {
                    name: 'Sam Supervisor',
                    roles: [Role.medical_supervisor],
                    uuid: '25c099d7-48ef-43f5-a206-2f679d7af2a6',
                },
                phone: '0612345678',
                question: 'Wat is een vraag?',
                subject: 'Onderwerp',
                type: ExpertQuestionTypeV1.VALUE_medical_supervision,
                updatedAt: '2022-03-11T15:42:26.600Z',
                user: {
                    name: 'Bob BCOer',
                    roles: [Role.user],
                    uuid: '6e916779-a84f-4b4e-8a3d-b9e8fc446134',
                },
                uuid: '6a28c17c-1b54-48de-858e-d8f1aa8f0414',
                caseOrganisationName: 'GGD West-Brabant',
                answer: null,
            },
        ]);

        expect(wrapper.find('td:last-of-type').text()).toBe('Opgepakt door jou');
    });

    it('should select a question when row its is clicked', async () => {
        setWrapper();
        const spyOnDispatch = vi.spyOn(wrapper.vm.$store, 'dispatch');

        await wrapper.find('tr.custom-link').trigger('click');

        expect(spyOnDispatch).toHaveBeenCalledWith(
            'supervision/SELECT_QUESTION',
            '6a28c17c-1b54-48de-858e-d8f1aa8f0414'
        );
    });

    it('should not select an already answered question when its row is clicked', async () => {
        setWrapper([
            {
                caseUuid: '54a77721-bfe4-4e09-b4ac-e44b908ebcd3',
                createdAt: '2022-03-11T15:42:26.600Z',
                assignedUser: null,
                phone: '0612345678',
                question: 'Wat is een vraag?',
                subject: 'Onderwerp',
                type: ExpertQuestionTypeV1.VALUE_medical_supervision,
                updatedAt: '2022-03-11T15:42:26.600Z',
                user: {
                    name: 'Bob BCOer',
                    roles: [Role.user],
                    uuid: '6e916779-a84f-4b4e-8a3d-b9e8fc446134',
                },
                uuid: '6a28c17c-1b54-48de-858e-d8f1aa8f0414',
                caseOrganisationName: 'GGD West-Brabant',
                answer: {
                    value: 'Dit is een antwoord',
                    createdAt: '2022-03-23T15:55:00.000Z',
                    answeredBy: {
                        name: 'Sam Supervisor',
                        roles: [Role.medical_supervisor],
                        uuid: '25c099d7-48ef-43f5-a206-2f679d7af2a6',
                    },
                },
            },
        ]);
        const spyOnDispatch = vi.spyOn(wrapper.vm.$store, 'dispatch');

        await wrapper.find('tr.custom-link').trigger('click');

        expect(spyOnDispatch).not.toHaveBeenCalled();
    });

    it('should still show infiniteLoader under questions if there are more pages to load', async () => {
        const stateChanger: Partial<StateChanger> = {
            loaded: vi.fn(),
            complete: vi.fn(),
        };
        setWrapper([
            {
                caseUuid: '54a77721-bfe4-4e09-b4ac-e44b908ebcd3',
                createdAt: '2022-03-11T15:42:26.600Z',
                assignedUser: {
                    name: 'Sam Supervisor',
                    roles: [Role.medical_supervisor],
                    uuid: '25c099d7-48ef-43f5-a206-2f679d7af2a6',
                },
                phone: '0612345678',
                question: 'Wat is een vraag?',
                subject: 'Onderwerp',
                type: ExpertQuestionTypeV1.VALUE_medical_supervision,
                updatedAt: '2022-03-11T15:42:26.600Z',
                user: {
                    name: 'Bob BCOer',
                    roles: [Role.user],
                    uuid: '6e916779-a84f-4b4e-8a3d-b9e8fc446134',
                },
                uuid: '6a28c17c-1b54-48de-858e-d8f1aa8f0414',
                caseOrganisationName: 'GGD West-Brabant',
                answer: null,
            },
        ]);

        wrapper.findComponent(InfiniteLoading).vm.$emit('infinite', stateChanger);
        await wrapper.vm.$nextTick();

        expect(stateChanger.loaded).toBeCalledTimes(1);
    });

    it('should not show infiniteLoader under questions if there are NO more pages to load', async () => {
        const stateChanger: Partial<StateChanger> = {
            loaded: vi.fn(),
            complete: vi.fn(),
        };
        setWrapper(
            [
                {
                    caseUuid: '54a77721-bfe4-4e09-b4ac-e44b908ebcd3',
                    createdAt: '2022-03-11T15:42:26.600Z',
                    assignedUser: {
                        name: 'Sam Supervisor',
                        roles: [Role.medical_supervisor],
                        uuid: '25c099d7-48ef-43f5-a206-2f679d7af2a6',
                    },
                    phone: '0612345678',
                    question: 'Wat is een vraag?',
                    subject: 'Onderwerp',
                    type: ExpertQuestionTypeV1.VALUE_medical_supervision,
                    updatedAt: '2022-03-11T15:42:26.600Z',
                    user: {
                        name: 'Bob BCOer',
                        roles: [Role.user],
                        uuid: '6e916779-a84f-4b4e-8a3d-b9e8fc446134',
                    },
                    uuid: '6a28c17c-1b54-48de-858e-d8f1aa8f0414',
                    caseOrganisationName: 'GGD West-Brabant',
                    answer: null,
                },
            ],
            undefined,
            Promise.resolve(1)
        );

        wrapper.findComponent(InfiniteLoading).vm.$emit('infinite', stateChanger);
        await wrapper.vm.$nextTick();

        expect(stateChanger.complete).toBeCalledTimes(1);
    });

    it('should render Tijdstip & Status table heads with aria-sort none on initial render', () => {
        setWrapper();

        expect(wrapper.find('thead th:nth-of-type(4)').attributes('aria-sort')).toBe('none');
        expect(wrapper.find('thead th:nth-of-type(5)').attributes('aria-sort')).toBe('none');
    });

    it.each([
        [
            { order: 'desc', sort: DefaultSortOptions.CREATED_AT },
            DefaultSortOptions.CREATED_AT,
            DefaultSortOptions.STATUS,
            'asc',
        ],
        [
            { order: 'asc', sort: DefaultSortOptions.STATUS },
            DefaultSortOptions.STATUS,
            DefaultSortOptions.CREATED_AT,
            'desc',
        ],
        [
            { order: 'desc', sort: DefaultSortOptions.CREATED_AT },
            DefaultSortOptions.CREATED_AT,
            DefaultSortOptions.CREATED_AT,
            'asc',
        ],
        [
            { order: 'asc', sort: DefaultSortOptions.CREATED_AT },
            DefaultSortOptions.CREATED_AT,
            DefaultSortOptions.CREATED_AT,
            'desc',
        ],
    ])(
        '%#: should call SET_TABLE_SORT with "%s" when onSort is called with "%s" and sort value in store is "%s" and order value in store is "%s"',
        async (expectedPayload, givenSort, sortInStore, orderInStore) => {
            setWrapper(undefined, undefined, undefined, {
                order: orderInStore as ExpertQuestionRequestOptions['order'],
                sort: sortInStore,
            });
            const spyOnCommit = vi.spyOn(wrapper.vm.$store, 'commit');

            const tableHeadToClick = wrapper.find(
                `thead th:nth-of-type(${givenSort === DefaultSortOptions.CREATED_AT ? 4 : 5})`
            );
            await tableHeadToClick.trigger('click');

            expect(spyOnCommit).toHaveBeenNthCalledWith(1, 'supervision/SET_TABLE_SORT', expectedPayload, undefined);
            expect(spyOnCommit).toHaveBeenNthCalledWith(2, 'supervision/RESET_QUESTION_TABLE', undefined, undefined);
        }
    );
});

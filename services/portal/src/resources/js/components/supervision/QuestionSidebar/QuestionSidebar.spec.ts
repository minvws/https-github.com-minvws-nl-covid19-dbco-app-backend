import type { Wrapper } from '@vue/test-utils';
import { createLocalVue, shallowMount } from '@vue/test-utils';
import BootstrapVue from 'bootstrap-vue';
import i18n from '@/i18n/index';
import Vuex, { Store } from 'vuex';

import type { ExpertQuestionResponse } from '@dbco/portal-api/supervision.dto';
import { ExpertQuestionTypeV1 } from '@dbco/enum';
import type { SupervisionStoreState } from '@/store/supervision/supervisionStore';
import supervisionStore from '@/store/supervision/supervisionStore';
import userInfoStore from '@/store/userInfo/userInfoStore';
import { Role } from '@dbco/portal-api/user';

import QuestionSidebar from '@/components/supervision/QuestionSidebar/QuestionSidebar.vue';

const mockedAction = vi.fn();

const createStore = (partialQuestion?: Partial<ExpertQuestionResponse>, userInfoStoreUser?: string) => {
    const selectedQuestion: ExpertQuestionResponse | null = partialQuestion
        ? {
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
              type: ExpertQuestionTypeV1.VALUE_conversation_coach,
              updatedAt: '2022-03-11T15:42:26.600Z',
              user: {
                  name: 'Bob BCOer',
                  roles: [Role.user],
                  uuid: '6e916779-a84f-4b4e-8a3d-b9e8fc446134',
              },
              uuid: '6a28c17c-1b54-48de-858e-d8f1aa8f0414',
              caseOrganisationName: 'GGD West-Brabant',
              answer: null,
              ...partialQuestion,
          }
        : null;

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
        selectedQuestion,
        supervisionQuestions: [],
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
                state,
                actions: {
                    PICK_UP_QUESTION: mockedAction,
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

describe('QuestionSidebar.vue', () => {
    const localVue = createLocalVue();
    let wrapper: Wrapper<QuestionSidebar>;

    localVue.use(BootstrapVue);
    localVue.use(Vuex);

    const setWrapper = (partialQuestion?: Partial<ExpertQuestionResponse>, userInfoStoreUser?: string) => {
        wrapper = shallowMount(QuestionSidebar, {
            localVue,
            i18n,
            store: createStore(partialQuestion, userInfoStoreUser),
        });
    };

    it('should render a sidebar with a translated title without any actions when no question is selected', () => {
        // ARRANGE
        setWrapper();

        // ASSERT
        expect(wrapper.find('choresidebar-stub').attributes('title')).toBe(
            i18n.t('components.questionSidebar.no_question_selected')
        );
        expect(wrapper.find('#deselect').exists()).toBeFalsy();
        expect(wrapper.find('#actions').exists()).toBeFalsy();
    });

    it('should render a translated title when a question to conversation coach is selected', () => {
        // ARRANGE
        setWrapper({ type: ExpertQuestionTypeV1.VALUE_conversation_coach });

        // ASSERT
        expect(wrapper.find('choresidebar-stub').attributes('title')).toBe(
            i18n.t('components.questionSidebar.question_for_conversation-coach')
        );
    });

    it('should render a translated title when a question to medical supervisor is selected', () => {
        // ARRANGE
        setWrapper({ type: ExpertQuestionTypeV1.VALUE_medical_supervision });

        // ASSERT
        expect(wrapper.find('choresidebar-stub').attributes('title')).toBe(
            i18n.t('components.questionSidebar.question_for_medical-supervision')
        );
    });

    it('should not render a close button when no question is selected', () => {
        // ARRANGE
        setWrapper();

        // ASSERT
        expect(wrapper.find('button').exists()).toBeFalsy();
    });

    it('should render a close button with translated content when a question is selected', () => {
        // ARRANGE
        setWrapper({});

        // ASSERT
        expect(wrapper.find('button').text()).toBe(i18n.t('components.questionSidebar.close_question'));
    });

    it('should not render an expert form when no question is selected', () => {
        // ARRANGE
        setWrapper();

        // ASSERT
        expect(wrapper.find('expertform-stub').exists()).toBe(false);
    });

    it('should render an expert form when a question is selected', () => {
        // ARRANGE
        setWrapper({});

        // ASSERT
        expect(wrapper.find('expertform-stub').exists()).toBe(true);
    });

    it('should render explanation when there is no selected question', () => {
        // ARRANGE
        setWrapper({ uuid: undefined });

        // ASSERT
        expect(wrapper.find('choresidebar-stub').attributes('hint')).toBe(
            i18n.t('components.questionSidebar.no_question_selected_hint')
        );
    });

    it('should render translated hint when no question is selected', () => {
        // ARRANGE
        setWrapper();

        // ASSERT
        expect(wrapper.find('choresidebar-stub').attributes('hint')).toBe(
            i18n.t('components.questionSidebar.no_question_selected_hint')
        );
    });

    it('should not render hint when question is answered', () => {
        // ARRANGE
        setWrapper(
            {
                assignedUser: null,
                answer: {
                    value: '',
                    createdAt: '',
                    answeredBy: {
                        name: 'Sam Supervisor',
                        roles: [Role.medical_supervisor],
                        uuid: '25c099d7-48ef-43f5-a206-2f679d7af2a6',
                    },
                },
            },
            '25c099d7-48ef-43f5-a206-2f679d7af2a6'
        );

        // ASSERT
        expect(wrapper.find('choresidebar-stub').attributes('hint')).toBe(undefined);
    });
});

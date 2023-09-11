import type { Wrapper } from '@vue/test-utils';
import { createLocalVue, shallowMount } from '@vue/test-utils';
import BootstrapVue from 'bootstrap-vue';
import i18n from '@/i18n/index';
import Vuex, { Store } from 'vuex';

import type { ExpertQuestionResponse } from '@dbco/portal-api/supervision.dto';
import { ExpertQuestionTypeV1 } from '@dbco/enum';
import { Role } from '@dbco/portal-api/user';
import type { SupervisionStoreState } from '@/store/supervision/supervisionStore';

import ExpertQuestion from '@/components/supervision/ExpertQuestion/ExpertQuestion.vue';

const createStore = (partialQuestion?: Partial<ExpertQuestionResponse>) => {
    const selectedQuestion: ExpertQuestionResponse | null = partialQuestion
        ? {
              caseUuid: '54a77721-bfe4-4e09-b4ac-e44b908ebcd3',
              createdAt: '2022-03-11T15:42:26.600Z',
              assignedUser: null,
              phone: '0612345678',
              question: 'Wat is een vraag?',
              subject: 'Onderwerp',
              type: ExpertQuestionTypeV1.VALUE_conversation_coach,
              updatedAt: '2022-03-11T15:42:26.600Z',
              user: {
                  name: 'Bob BCOer',
                  roles: [Role.user],
                  uuid: '7576407f-73e8-4a55-808c-9e515e2f9272',
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
            },
        },
    });
};

describe('ExpertQuestion.vue', () => {
    const localVue = createLocalVue();
    let wrapper: Wrapper<ExpertQuestion>;

    localVue.use(BootstrapVue);
    localVue.use(Vuex);

    const setWrapper = (partialQuestion?: Partial<ExpertQuestionResponse>) => {
        wrapper = shallowMount(ExpertQuestion, {
            localVue,
            i18n,
            store: createStore(partialQuestion),
        });
    };

    it('should not render if no expert question is selected', () => {
        // ARRANGE
        setWrapper();

        expect(wrapper.find('#expert-question').exists()).toBeFalsy();
    });

    it('should render a formatted date if a question is selected', () => {
        // ARRANGE
        setWrapper({});

        expect(wrapper.find('small').text()).toBe('11 maart 2022 15:42');
    });

    it('should render a translated text if a question with an undefined creation date is selected', () => {
        // ARRANGE
        setWrapper({ createdAt: undefined });

        expect(wrapper.find('small').text()).toBe('geen datum beschikbaar');
    });

    it('should render the answer and answer time when the question is answered', () => {
        // ARRANGE
        setWrapper({
            answer: {
                value: 'Dit is een antwoord.',
                createdAt: '2022-03-23T15:55:00.000Z',
                answeredBy: { name: '', roles: [], uuid: '' },
            },
        });

        expect(wrapper.find('#expert-question-answer p').text()).toBe('Dit is een antwoord.');
        expect(wrapper.find('#expert-question-answer small').text()).toBe('23 maart 2022 15:55');
    });

    it('should render a translated text if an answer with an undefined creation date is selected', () => {
        // ARRANGE
        setWrapper({
            answer: { value: 'Dit is een antwoord.', createdAt: '', answeredBy: { name: '', roles: [], uuid: '' } },
        });

        expect(wrapper.find('#expert-question-answer small').text()).toBe('geen datum beschikbaar');
    });

    it('should render a translated message when no user is defined', () => {
        // ARRANGE
        setWrapper({
            answer: {
                createdAt: '',
                value: '',
                answeredBy: null,
            },
            user: undefined,
            phone: undefined,
        });

        expect(wrapper.find('footer p').text()).toBe('geen gebruikersinformatie beschikbaar');
    });

    it('should render translated details when a user is defined', () => {
        // ARRANGE
        setWrapper({});

        expect(wrapper.find('footer p').text()).toBe('Bob BCOer, Gebruiker, 0612345678');
    });

    it('should render a translated role when an expert user is defined', () => {
        // ARRANGE
        setWrapper({
            answer: {
                value: 'Dit is een antwoord.',
                createdAt: '',
                answeredBy: {
                    name: 'Sam Supervisor',
                    roles: [Role.medical_supervisor, Role.conversation_coach],
                    uuid: '25c099d7-48ef-43f5-a206-2f679d7af2a6',
                },
            },
        });

        expect(wrapper.find('#expert-question-answer footer p').text()).toBe(
            'Sam Supervisor, Medisch Supervisor, Gesprekscoach'
        );
    });
});

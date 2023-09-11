import { supervisionApi } from '@dbco/portal-api';
import type { ExpertQuestionResponse } from '@dbco/portal-api/supervision.dto';
import { ExpertQuestionTypeV1, expertQuestionTypeV1Options } from '@dbco/enum';
import type { IndexStoreState } from '@/store/index/indexStore';
import indexStore from '@/store/index/indexStore';
import type { SupervisionStoreState } from '@/store/supervision/supervisionStore';
import supervisionStore from '@/store/supervision/supervisionStore';
import { Role } from '@dbco/portal-api/user';
import { flushCallStack, setupTest } from '@/utils/test';
import * as showToast from '@/utils/showToast';
import { shallowMount } from '@vue/test-utils';
import type { BvModal } from 'bootstrap-vue';
import Vuex from 'vuex';
import SupervisionModal from './SupervisionModal.vue';
import type { VueConstructor } from 'vue';
import type { AxiosError } from 'axios';

const mockQuestion: ExpertQuestionResponse = {
    caseUuid: 'd4fc3627-2cf1-4e8d-a3d1-fbfd7fa1ec45',
    type: ExpertQuestionTypeV1.VALUE_conversation_coach,
    phone: '0612345678',
    subject: 'test question',
    question: 'some test question text',
    createdAt: '',
    updatedAt: '',
    user: {
        name: 'Bob BCOer',
        roles: [Role.user],
        uuid: '7576407f-73e8-4a55-808c-9e515e2f9272',
    },
    uuid: '37088a62-bbcb-48eb-b667-0ce32f893492',
    assignedUser: {
        name: 'Sam Supervisor',
        roles: [Role.medical_supervisor],
        uuid: '25c099d7-48ef-43f5-a206-2f679d7af2a6',
    },
    caseOrganisationName: 'GGD West-Brabant',
    answer: null,
};

const createComponent = setupTest(
    (
        localVue: VueConstructor,
        data: object = {},
        indexStoreState?: Partial<IndexStoreState>,
        supervisionStoreState?: SupervisionStoreState
    ) => {
        const indexStoreModule = {
            ...indexStore,
            state: {
                ...indexStore.state,
                ...indexStoreState,
            },
        };

        const supervisionStoreModule = {
            ...supervisionStore,
            state: {
                ...supervisionStore.state,
                ...supervisionStoreState,
            },
        };

        return shallowMount<SupervisionModal>(SupervisionModal, {
            localVue,
            data: () => data,
            store: new Vuex.Store({
                modules: {
                    index: indexStoreModule,
                    supervision: supervisionStoreModule,
                },
            }),
            stubs: {
                BModal: true,
                FormulateForm: true,
                FormulateInput: true,
            },
        });
    }
);

describe('SupervisionModal.vue', () => {
    it('should call api and show success toast on confirm if filled in', async () => {
        const formValues = {
            type: 'conversation-coach',
            phone: '0612345678',
            subject: 'test question',
            question: 'some test question text',
        };
        const data = { formValues };

        const wrapper = createComponent(
            data,
            {
                uuid: 'd4fc3627-2cf1-4e8d-a3d1-fbfd7fa1ec45',
                loaded: true,
                meta: { schemaVersion: 1 },
                errors: {},
                fragments: {},
                messages: [],
                contexts: [],
                tasks: {},
            },
            {
                questions: [],
                backendError: null,
                pollSelected: {
                    pollInterval: 5000,
                    polling: null,
                },
                pollSupervisionQuestions: {
                    polling: null,
                    pollInterval: 10000,
                },
                selectedQuestion: null,
                supervisionQuestions: [],
                supervisionQuestionTable: {
                    infiniteId: Date.now(),
                    page: 1,
                    perPage: 20,
                },
                activeRole: null,
                updateMessage: null,
            }
        );

        (wrapper.vm.$refs.modal as unknown as BvModal).hide = vi.fn();
        const spyOnDispatch = vi.spyOn(wrapper.vm.$store, 'dispatch');
        vi.spyOn(supervisionApi, 'askQuestion').mockImplementationOnce(() => Promise.resolve(mockQuestion));
        const toastSpy = vi.spyOn(showToast, 'default');

        await wrapper.vm.askQuestion();
        await wrapper.vm.$nextTick();

        expect(spyOnDispatch).toHaveBeenCalledWith('supervision/ASK_QUESTION', {
            uuid: 'd4fc3627-2cf1-4e8d-a3d1-fbfd7fa1ec45',
            question: { ...formValues },
        });

        // Wait for store to process update
        await flushCallStack();

        expect(toastSpy).toHaveBeenCalledWith(
            `Vraag is verstuurd aan ${expertQuestionTypeV1Options[mockQuestion.type]}`,
            'ask-expert-question'
        );
    });

    it('should show error message on confirm if phone number is invalid', async () => {
        const formValues = {
            type: 'conversation-coach',
            phone: '123',
            subject: 'test question',
            question: 'some test question text',
        };
        const data = { formValues };

        const wrapper = createComponent(
            data,
            {
                uuid: 'd4fc3627-2cf1-4e8d-a3d1-fbfd7fa1ec45',
                loaded: true,
                meta: { schemaVersion: 1 },
                errors: {},
                fragments: {},
                messages: [],
                contexts: [],
                tasks: {},
            },
            {
                questions: [],
                backendError: null,
                pollSelected: {
                    pollInterval: 5000,
                    polling: null,
                },
                pollSupervisionQuestions: {
                    polling: null,
                    pollInterval: 10000,
                },
                selectedQuestion: null,
                supervisionQuestions: [],
                supervisionQuestionTable: {
                    infiniteId: Date.now(),
                    page: 1,
                    perPage: 20,
                },
                activeRole: null,
                updateMessage: null,
            }
        );

        const spyOnDispatch = vi.spyOn(wrapper.vm.$store, 'dispatch');
        const error: AxiosError = {
            config: {} as any, // eslint-disable-line @typescript-eslint/no-explicit-any
            isAxiosError: true,
            name: '',
            message: '',
            response: {
                config: {} as any, // eslint-disable-line @typescript-eslint/no-explicit-any
                headers: {},
                data: {
                    validationResult: {
                        fatal: {
                            failed: { phone: { Phone: ['AUTO', 'NL'] } },
                            errors: { phone: ['Telefoonnummer moet een geldig telefoonnummer zijn.'] },
                        },
                    },
                },
                status: 422,
                statusText: '',
            },
            toJSON: () => ({}),
        };
        vi.spyOn(supervisionApi, 'askQuestion').mockImplementationOnce(() => Promise.reject(error));

        await wrapper.vm.askQuestion();
        await wrapper.vm.$nextTick();

        expect(spyOnDispatch).toHaveBeenCalledWith('supervision/ASK_QUESTION', {
            uuid: 'd4fc3627-2cf1-4e8d-a3d1-fbfd7fa1ec45',
            question: { ...formValues },
        });

        // Wait for store to process update
        await flushCallStack();

        expect(wrapper.vm.errors).toStrictEqual({
            phone: '{"fatal":["Telefoonnummer moet een geldig telefoonnummer zijn."]}',
        });
    });

    it('should show toast with error when api call fails', async () => {
        const formValues = {
            type: 'conversation-coach',
            subject: 'test question',
            question: 'some test question text',
        };
        const data = { formValues };

        const wrapper = createComponent(
            data,
            {
                uuid: 'd4fc3627-2cf1-4e8d-a3d1-fbfd7fa1ec45',
                loaded: true,
                meta: { schemaVersion: 1 },
                errors: {},
                fragments: {},
                messages: [],
                contexts: [],
                tasks: {},
            },
            {
                questions: [],
                backendError: null,
                pollSelected: {
                    pollInterval: 5000,
                    polling: null,
                },
                pollSupervisionQuestions: {
                    polling: null,
                    pollInterval: 10000,
                },
                selectedQuestion: null,
                supervisionQuestions: [],
                supervisionQuestionTable: {
                    infiniteId: Date.now(),
                    page: 1,
                    perPage: 20,
                },
                activeRole: null,
                updateMessage: null,
            }
        );

        (wrapper.vm.$refs.modal as unknown as BvModal).hide = vi.fn();
        const spyOnDispatch = vi.spyOn(wrapper.vm.$store, 'dispatch');
        vi.spyOn(supervisionApi, 'askQuestion').mockImplementationOnce(() =>
            Promise.reject({ response: { data: {} } })
        );
        const toastSpy = vi.spyOn(showToast, 'default');

        await wrapper.vm.askQuestion();
        await wrapper.vm.$nextTick();

        expect(spyOnDispatch).toHaveBeenCalledWith('supervision/ASK_QUESTION', {
            uuid: 'd4fc3627-2cf1-4e8d-a3d1-fbfd7fa1ec45',
            question: { ...formValues },
        });

        // Wait for store to process update
        await flushCallStack();

        expect(toastSpy).toHaveBeenCalledWith('Er is iets fout gegaan', 'ask-expert-question', true);
    });

    it('should clear form values and errors when resetModal method is fired', async () => {
        const formValues = {
            type: 'conversation-coach',
            subject: 'test question',
            question: 'some test question text',
        };
        const errors = { error: 'some error' };

        const data = { formValues, errors };
        const wrapper = createComponent(data);

        expect(wrapper.vm.formValues).toStrictEqual(formValues);
        expect(wrapper.vm.errors).toStrictEqual(errors);

        await wrapper.vm.resetModal();

        expect(wrapper.vm.formValues).toStrictEqual({});
        expect(wrapper.vm.errors).toStrictEqual({});
    });

    it('should trigger vue formulate submit on modal submit', async () => {
        const wrapper = createComponent();

        const spyOnSubmit = vi.spyOn(wrapper.vm.$formulate, 'submit').mockImplementationOnce(() => vi.fn());

        await wrapper.vm.submitModal();

        expect(spyOnSubmit).toHaveBeenCalledWith('supervision-form');
    });
});

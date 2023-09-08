import { createLocalVue, shallowMount } from '@vue/test-utils';

import BootstrapVue, { BFormTextarea } from 'bootstrap-vue';
import VueI18n from 'vue-i18n';
import i18n from '@/i18n/index';
import Vuex, { Store } from 'vuex';

import ChoreActions from '@/components/chore/ChoreActions/ChoreActions.vue';
import ExpertForm from '@/components/supervision/ExpertForm/ExpertForm.vue';

import type { ExpertQuestionResponse } from '@dbco/portal-api/supervision.dto';
import type { SupervisionStoreState } from '@/store/supervision/supervisionStore';
import supervisionStore from '@/store/supervision/supervisionStore';

import userInfoStore from '@/store/userInfo/userInfoStore';
import { generateFakeExpertQuestionResponse } from '@/utils/__fakes__/expertQuestion';
import type { UntypedWrapper } from '@/utils/test';
import { fakerjs } from '@/utils/test';
import TypingHelpers from '@/plugins/typings';

const mockedAction = vi.fn();

const createStore = (question?: ExpertQuestionResponse, userInfoStoreUser?: string) => {
    const selectedQuestion: ExpertQuestionResponse = question ?? generateFakeExpertQuestionResponse();

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
                    ANSWER_QUESTION: mockedAction,
                    DROP_QUESTION: mockedAction,
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

describe('ExpertForm.vue', () => {
    const localVue = createLocalVue();
    let wrapper: UntypedWrapper<ExpertForm>;

    localVue.use(BootstrapVue);
    localVue.use(Vuex);
    localVue.use(VueI18n);
    localVue.use(TypingHelpers);

    const setWrapper = (question?: ExpertQuestionResponse, userInfoStoreUser?: string) => {
        wrapper = shallowMount(ExpertForm, {
            localVue,
            i18n,
            store: createStore(question, userInfoStoreUser),
            stubs: {
                'b-textarea': BFormTextarea,
            },
        });
    };

    it('Should not show textarea when question is not picked up', () => {
        // ARRANGE
        setWrapper();
        const textarea = wrapper.findComponent({ name: 'b-form-textarea' });

        // ASSERT
        expect(textarea.exists()).toBe(false);
    });

    it('Should show textarea when question is picked up', () => {
        // ARRANGE
        const fakeSelectedQuestion = generateFakeExpertQuestionResponse(false, true);
        setWrapper(fakeSelectedQuestion, fakeSelectedQuestion.assignedUser?.uuid);
        const textarea = wrapper.findComponent({ name: 'b-form-textarea' });

        // ASSERT
        expect(textarea.exists()).toBe(true);
    });

    it('Should show required message when user submits without answer', async () => {
        // ARRANGE
        const fakeSelectedQuestion = generateFakeExpertQuestionResponse(false, true);
        setWrapper(fakeSelectedQuestion, fakeSelectedQuestion.assignedUser?.uuid);

        await wrapper.findComponent(ChoreActions).vm.$emit('tertiaryAction');

        // ASSERT
        const message = i18n.t('components.answerSidebar.answer_required_message');
        expect(wrapper.html()).toContain(message);
    });

    it('Should dispatch the correct store action when user submits answer', async () => {
        // ARRANGE
        const fakeSelectedQuestion = generateFakeExpertQuestionResponse(false, true);
        setWrapper(fakeSelectedQuestion, fakeSelectedQuestion.assignedUser?.uuid);
        const spyOnDispatch = vi.spyOn(wrapper.vm.$store, 'dispatch');

        await wrapper.setData({ answer: fakerjs.lorem.paragraph() });

        await wrapper.findComponent(ChoreActions).vm.$emit('tertiaryAction');

        // ASSERT
        expect(spyOnDispatch).toHaveBeenCalledWith('supervision/ANSWER_QUESTION', wrapper.vm.answer);
    });

    it('should dispatch the correct store action when a pickup action occurs', async () => {
        // ARRANGE
        const fakeSelectedQuestion = generateFakeExpertQuestionResponse(false);
        setWrapper(fakeSelectedQuestion);
        const spyOnDispatch = vi.spyOn(wrapper.vm.$store, 'dispatch');

        await wrapper.findComponent(ChoreActions).vm.$emit('toggle');

        // ASSERT
        expect(spyOnDispatch).toHaveBeenCalledWith('supervision/PICK_UP_QUESTION', fakeSelectedQuestion.uuid);
    });

    it('should dispatch the correct store action when a drop action occurs', async () => {
        // ARRANGE
        const fakeSelectedQuestion = generateFakeExpertQuestionResponse(false, true);
        setWrapper(fakeSelectedQuestion, fakeSelectedQuestion.assignedUser?.uuid);
        const spyOnDispatch = vi.spyOn(wrapper.vm.$store, 'dispatch');

        await wrapper.findComponent(ChoreActions).vm.$emit('toggle');

        // ASSERT
        expect(spyOnDispatch).toHaveBeenCalledWith('supervision/DROP_QUESTION', fakeSelectedQuestion.uuid);
    });

    it('Should show translated button when question answered', async () => {
        // ARRANGE
        const fakeSelectedQuestion = generateFakeExpertQuestionResponse(true);
        setWrapper(fakeSelectedQuestion, fakeSelectedQuestion.answer?.answeredBy?.uuid);
        await wrapper.vm.$nextTick();
        const button = wrapper.findComponent({ name: 'b-button' });
        await wrapper.vm.$nextTick();

        // ASSERT
        expect(button.text()).toBe(i18n.t('components.answerSidebar.answer_answered_label'));
    });
});

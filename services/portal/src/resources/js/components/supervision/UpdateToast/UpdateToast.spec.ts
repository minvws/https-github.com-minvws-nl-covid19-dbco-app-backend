import type { Wrapper } from '@vue/test-utils';
import { createLocalVue, shallowMount } from '@vue/test-utils';
import BootstrapVue from 'bootstrap-vue';
import i18n from '@/i18n/index';
import Vuex, { Store } from 'vuex';

import type { SupervisionStoreState } from '@/store/supervision/supervisionStore';

import UpdateToast from '@/components/supervision/UpdateToast/UpdateToast.vue';
import { supervisionActions } from '@/store/supervision/supervisionActions/supervisionActions';
import { supervisionMutations } from '@/store/supervision/supervisionMutations/supervisionMutations';

interface State {
    supervision: SupervisionStoreState;
}

const createStore = () => {
    const state: SupervisionStoreState = {
        activeRole: null,
        backendError: null,
        pollSelected: {
            polling: null,
            pollInterval: 5000,
        },
        pollSupervisionQuestions: {
            polling: null,
            pollInterval: 10000,
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

    return new Store({
        modules: {
            supervision: {
                namespaced: true,
                state,
                actions: {
                    ...supervisionActions,
                    ['FETCH_QUESTIONS']: vi.fn(),
                    ['START_POLLING_SUPERVISION_QUESTIONS']: vi.fn(),
                },
                mutations: {
                    ...supervisionMutations,
                    ['SET_UPDATE_MESSAGE'](state: SupervisionStoreState, message: string) {
                        state.updateMessage = message;
                    },
                },
            },
        },
    }) as Store<State>;
};

describe('UpdateToast.vue', () => {
    const localVue = createLocalVue();
    let wrapper: Wrapper<UpdateToast>;

    localVue.use(BootstrapVue);
    localVue.use(Vuex);

    let store: Store<State>;
    const hide = vi.fn();
    const show = vi.fn();

    const setWrapper = () => {
        store = createStore();
        const BToastMock = {
            template: '<div><slot></slot></div>',
            methods: { hide, show },
        };

        wrapper = shallowMount(UpdateToast, {
            localVue,
            i18n,
            store,
            stubs: {
                BToast: BToastMock,
            },
        });
    };

    it('should not render a toast when created', () => {
        // ARRANGE
        setWrapper();

        // ASSERT
        expect(show).not.toHaveBeenCalled();
    });

    it('should render a toast when an update message is committed to store', async () => {
        // ARRANGE
        setWrapper();
        store.commit('supervision/SET_UPDATE_MESSAGE', 'Test update toast');
        await wrapper.vm.$nextTick();

        // ASSERT
        expect(show).toHaveBeenCalled();
    });

    it('should clear update message when toast is hidden', async () => {
        // ARRANGE
        setWrapper();
        store.commit('supervision/SET_UPDATE_MESSAGE', 'Test update toast');
        await wrapper.vm.$nextTick();
        wrapper.findComponent({ name: 'BToast' }).vm.$emit('hidden');
        await wrapper.vm.$nextTick();

        // ASSERT
        expect(store.state.supervision.updateMessage).toBeNull();
    });

    it('should reset table when refresh button is clicked', async () => {
        // ARRANGE
        setWrapper();
        const spyOnCommit = vi.spyOn(wrapper.vm.$store, 'commit');

        const button = wrapper.find('button.refresh');
        await button.trigger('click');

        // ASSERT
        expect(spyOnCommit).toHaveBeenCalledWith('supervision/RESET_QUESTION_TABLE', undefined, undefined);
    });

    it('should start polling again when close button is clicked', async () => {
        // ARRANGE
        setWrapper();
        const spyOnDispatch = vi.spyOn(wrapper.vm.$store, 'dispatch');

        const button = wrapper.find('button.close');
        await button.trigger('click');

        // ASSERT
        expect(spyOnDispatch).toHaveBeenCalledWith('supervision/START_POLLING_SUPERVISION_QUESTIONS', undefined);
    });
});

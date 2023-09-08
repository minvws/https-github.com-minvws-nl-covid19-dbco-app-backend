import { shallowMount } from '@vue/test-utils';
import i18n from '@/i18n/index';
import { Store } from 'vuex';

import type { SupervisionStoreState } from '@/store/supervision/supervisionStore';
import { supervisionMutations } from '@/store/supervision/supervisionMutations/supervisionMutations';

import SupervisionBackendErrorModal from '@/components/modals/SupervisionBackendErrorModal/SupervisionBackendErrorModal.vue';
import type { BackendError } from '@dbco/portal-api/error';
import { setupTest } from '@/utils/test';
import type { VueConstructor } from 'vue';

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
                mutations: {
                    ...supervisionMutations,
                    ['SET_BACKEND_ERROR'](state: SupervisionStoreState, error: BackendError) {
                        state.backendError = error;
                    },
                },
            },
        },
    }) as Store<State>;
};

let store: Store<State>;
const show = vi.fn();
const createComponent = setupTest((localVue: VueConstructor) => {
    store = createStore();
    const BModal = {
        template: '<div />',
        methods: { show },
    };

    return shallowMount(SupervisionBackendErrorModal, {
        localVue,
        i18n,
        store,
        stubs: { BModal },
    });
});

describe('SupervisionBackendErrorModal.vue', () => {
    it('should not render a modal when created', () => {
        // ARRANGE
        createComponent();

        // ASSERT
        expect(show).not.toHaveBeenCalled();
    });

    it('should render a modal when a backend error is committed to store', async () => {
        // ARRANGE
        const wrapper = createComponent();
        store.commit('supervision/SET_BACKEND_ERROR', { message: 'Not found', status: 404 });
        await wrapper.vm.$nextTick();

        // ASSERT
        expect(show).toHaveBeenCalled();
    });

    it('should clear backend error when modal is hidden', async () => {
        // ARRANGE
        const wrapper = createComponent();
        store.commit('supervision/SET_BACKEND_ERROR', { message: 'Not found', status: 404 });
        await wrapper.vm.$nextTick();
        wrapper.findComponent({ name: 'BModal' }).vm.$emit('hidden');
        await wrapper.vm.$nextTick();

        // ASSERT
        expect(store.state.supervision.backendError).toBeNull();
    });

    it('should reset table when modal is hidden', async () => {
        // ARRANGE
        const wrapper = createComponent();
        const spyOnCommit = vi.spyOn(wrapper.vm.$store, 'commit');

        store.commit('supervision/SET_BACKEND_ERROR', { message: 'Not found', status: 404 });
        await wrapper.vm.$nextTick();
        wrapper.findComponent({ name: 'BModal' }).vm.$emit('hidden');
        await wrapper.vm.$nextTick();

        // ASSERT
        expect(spyOnCommit).toHaveBeenCalledWith('supervision/RESET_QUESTION_TABLE');
    });
});

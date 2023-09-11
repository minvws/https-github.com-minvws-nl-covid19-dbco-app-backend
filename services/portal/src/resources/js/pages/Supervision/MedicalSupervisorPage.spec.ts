import type { Wrapper } from '@vue/test-utils';
import { createLocalVue, shallowMount } from '@vue/test-utils';

import BootstrapVue from 'bootstrap-vue';
import i18n from '@/i18n/index';
import Vuex, { Store } from 'vuex';
import type { SupervisionStoreState } from '@/store/supervision/supervisionStore';
import type { SupervisionRoles } from '@dbco/portal-api/user';

import MedicalSupervisorPage from '@/pages/Supervision/MedicalSupervisorPage.vue';
import { Heading } from '@dbco/ui-library';

const createStore = () => {
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
                    ['SET_ACTIVE_ROLE'](state: SupervisionStoreState, role: SupervisionRoles | null) {
                        state.activeRole = role;
                    },
                },
            },
        },
    });
};

describe('MedicalSupervisorPage.vue', () => {
    const localVue = createLocalVue();
    let wrapper: Wrapper<MedicalSupervisorPage>;

    localVue.use(BootstrapVue);
    localVue.use(Vuex);

    const setWrapper = () => {
        wrapper = shallowMount(MedicalSupervisorPage, {
            localVue,
            i18n,
            store: createStore(),
        });
    };

    it('should render a translated title', () => {
        // ARRANGE
        setWrapper();

        // ASSERT
        expect(wrapper.findComponent(Heading).text()).toBe('Hulpvragen medische supervisie');
    });
});

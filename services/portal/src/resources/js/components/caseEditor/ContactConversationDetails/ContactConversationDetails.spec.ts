import { shallowMount } from '@vue/test-utils';
import Vuex from 'vuex';

import ContactConversationDetails from './ContactConversationDetails.vue';
import taskStore from '@/store/task/taskStore';
import { setupTest } from '@/utils/test';
import type { VueConstructor } from 'vue';

const createComponent = setupTest((localVue: VueConstructor, state: object = {}) => {
    const store = {
        ...taskStore,
        state: {
            ...taskStore.state,
            ...state,
        },
    };

    return shallowMount(ContactConversationDetails, {
        localVue,
        store: new Vuex.Store({
            modules: {
                task: store,
            },
        }),
    });
});

describe('ContactConversationDetails.vue', () => {
    it('should show task name and number', () => {
        const state = {
            uuid: '769723e8-dab2-43e8-b1b5-7a872233898f',
            fragments: {
                general: {
                    firstname: 'John',
                    lastname: 'Doe',
                    phone: 'Nokia 3310',
                },
            },
        };

        const wrapper = createComponent(state);

        expect(wrapper.text()).toContain('John Doe');
        expect(wrapper.text()).toContain('Nokia 3310');
    });
});

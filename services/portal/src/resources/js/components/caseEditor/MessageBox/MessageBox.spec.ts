import indexStore from '@/store/index/indexStore';

import type { VueConstructor } from 'vue';
import Vuex from 'vuex';
import { caseUuid } from './FakeMessage';
import MessageBox from './MessageBox.vue';
import Messages from './Messages.vue';
import { fakerjs, setupTest } from '@/utils/test';
import type { Wrapper } from '@vue/test-utils';
import { shallowMount } from '@vue/test-utils';
import ViewMessageModal from '@/components/modals/ViewMessageModal/ViewMessageModal.vue';

const createComponent = setupTest((localVue: VueConstructor) =>
    shallowMount(MessageBox, {
        localVue,
        propsData: undefined,
        store: new Vuex.Store({
            modules: {
                index: {
                    ...indexStore,
                    state: {
                        ...indexStore.state,
                        uuid: caseUuid,
                    },
                },
            },
        }),
    })
);

describe('MessageBox.vue', () => {
    let wrapper: Wrapper<MessageBox>;
    let messageId: string;

    beforeEach(async () => {
        wrapper = createComponent();
        messageId = fakerjs.string.uuid();

        expect(wrapper.findComponent(ViewMessageModal).exists()).toBeFalsy();

        wrapper.findComponent(Messages).vm.$emit('select', messageId);
        await wrapper.vm.$nextTick();
    });

    it('should open dialog on message select', () => {
        expect(wrapper.findComponent(ViewMessageModal).exists()).toBeTruthy();
        expect(wrapper.findComponent(ViewMessageModal).props('messageUuid')).toEqual(messageId);
    });

    it('should hide dialog on closing', async () => {
        wrapper.findComponent(ViewMessageModal).vm.$emit('hide');
        await wrapper.vm.$nextTick();

        expect(wrapper.findComponent(ViewMessageModal).exists()).toBeFalsy();
    });
});

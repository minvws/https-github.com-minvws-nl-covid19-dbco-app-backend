import { shallowMount } from '@vue/test-utils';
import ContactsEditingModal from './ContactsEditingModal.vue';
import { setupTest } from '@/utils/test';
import type { VueConstructor } from 'vue';

const createComponent = setupTest((localVue: VueConstructor) => {
    return shallowMount(ContactsEditingModal, {
        localVue,
    });
});

describe('ContactsEditingModal.vue', () => {
    it('should show ContactEditingTable in modal', async () => {
        const wrapper = createComponent();
        wrapper.vm.$root.$emit('bv::show::modal', 'ContactsEditingModal');

        await wrapper.vm.$nextTick();
        const table = wrapper.findComponent({ name: 'ContactEditingTable' });

        expect(table.isVisible()).toBe(true);
    });
});

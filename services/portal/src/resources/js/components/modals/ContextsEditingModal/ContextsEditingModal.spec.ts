import { shallowMount } from '@vue/test-utils';
import ContextsEditingModal from './ContextsEditingModal.vue';
import { setupTest } from '@/utils/test';
import type { VueConstructor } from 'vue';

const createComponent = setupTest((localVue: VueConstructor) => {
    return shallowMount(ContextsEditingModal, {
        localVue,
    });
});

describe('ContextsEditingModal.vue', () => {
    it('should show ContextEditingTable in modal', async () => {
        const wrapper = createComponent();
        wrapper.vm.$root.$emit('bv::show::modal', 'ContextsEditingModal');

        await wrapper.vm.$nextTick();
        const table = wrapper.findComponent({ name: 'ContextEditingTable' });

        expect(table.isVisible()).toBe(true);
    });
});

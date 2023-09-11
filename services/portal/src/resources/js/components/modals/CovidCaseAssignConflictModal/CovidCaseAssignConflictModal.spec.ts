import type { BvModal } from 'bootstrap-vue';
import { shallowMount } from '@vue/test-utils';

import i18n from '@/i18n/index';

import CovidCaseAssignConflictModal from './CovidCaseAssignConflictModal.vue';
import { setupTest } from '@/utils/test';
import type { VueConstructor } from 'vue';

const createComponent = setupTest((localVue: VueConstructor) => {
    return shallowMount(CovidCaseAssignConflictModal, {
        localVue,
        i18n,
        stubs: {
            BModal: true,
        },
    });
});

describe('CovidCaseAssignConflictModal.vue', () => {
    it('should be added to the DOM', () => {
        const wrapper = createComponent();

        expect(wrapper.findComponent({ name: 'BModal' }).exists()).toBe(true);
    });

    it('should clear description and set assignmentConflicts to false when resetModal method is fired', async () => {
        const wrapper = createComponent();

        await (wrapper as any).vm.resetModal();

        expect(wrapper.vm.description).toBe('');
        expect(wrapper.vm.assignmentConflicts.length).toBe(0);
    });

    it('should render modal when show method is fired', async () => {
        const wrapper = createComponent();

        (wrapper.vm.$refs.modal as unknown as BvModal).show = vi.fn();

        await (wrapper as any).vm.show('', []);

        expect(wrapper.findComponent({ name: 'BModal' }).isVisible()).toBe(true);
    });
});

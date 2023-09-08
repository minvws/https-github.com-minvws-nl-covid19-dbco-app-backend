import type { VueConstructor } from 'vue';
import { shallowMount } from '@vue/test-utils';
import { setupTest } from '@/utils/test';
import PolicyGuidelineTable from './PolicyGuidelineTable.vue';
import { Tbody, Tr } from '@dbco/ui-library';
import { useRouter } from '@/router/router';
import { fakePolicyGuideline } from '@/utils/__fakes__/admin';

vi.mock('@/router/router');

const createComponent = setupTest((localVue: VueConstructor, props?: object) => {
    return shallowMount(PolicyGuidelineTable, {
        localVue,
        propsData: props,
    });
});

describe('PolicyGuidelineTable.vue', () => {
    it('should show table with correct number of guidelines', () => {
        const wrapper = createComponent({ guidelines: [fakePolicyGuideline(), fakePolicyGuideline()] });
        const tableBody = wrapper.findComponent(Tbody);
        expect(tableBody.findAllComponents(Tr).length).toBe(2);
    });

    it('should show a message when no items are found', () => {
        const wrapper = createComponent({ guidelines: [] });
        expect(wrapper.text()).toContain('Geen richtlijnen gevonden');
    });

    it('should push detail view path to router when table row is clicked', async () => {
        const wrapper = createComponent({ guidelines: [fakePolicyGuideline()] });
        const tableBodyRow = wrapper.findComponent(Tbody).findAllComponents(Tr).at(0);

        await tableBodyRow.vm.$emit('click');

        expect(useRouter().push).toHaveBeenCalledTimes(1);
    });
});

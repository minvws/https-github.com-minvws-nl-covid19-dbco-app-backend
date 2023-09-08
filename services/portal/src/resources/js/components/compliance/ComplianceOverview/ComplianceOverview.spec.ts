import { shallowMount } from '@vue/test-utils';
import ComplianceOverview from './ComplianceOverview.vue';
import ComplianceSearch from '../ComplianceSearch/ComplianceSearch.vue';
import { setupTest } from '@/utils/test';
import type { VueConstructor } from 'vue';

const createComponent = setupTest((localVue: VueConstructor) => {
    return shallowMount(ComplianceOverview, {
        localVue,
    });
});

describe('ComplianceOverview.vue', () => {
    it('should render overview with search', () => {
        const wrapper = createComponent();

        expect(wrapper.findComponent(ComplianceSearch).exists()).toBe(true);
    });
});

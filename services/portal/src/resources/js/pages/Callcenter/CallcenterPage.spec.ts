import type { VueConstructor } from 'vue';
import { shallowMount } from '@vue/test-utils';
import { setupTest } from '@/utils/test';
import CallcenterPage from './CallcenterPage.vue';
import { LayoutSidebar } from '@dbco/ui-library';

const createComponent = setupTest((localVue: VueConstructor, props?: object) => {
    return shallowMount(CallcenterPage, {
        localVue,
        propsData: props,
        stubs: { LayoutSidebar },
    });
});

describe('CallcenterPage.vue', () => {
    it('should show the callcenter search sidebar', () => {
        const wrapper = createComponent();
        expect(wrapper.find('callcentersearch-stub').exists()).toBe(true);
    });

    it('should show the callcenter results table', () => {
        const wrapper = createComponent();
        expect(wrapper.find('callcentersearchresults-stub').exists()).toBe(true);
    });
});

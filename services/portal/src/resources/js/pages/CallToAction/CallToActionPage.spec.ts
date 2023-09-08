import type { VueConstructor } from 'vue';
import { shallowMount } from '@vue/test-utils';
import { setupTest } from '@/utils/test';
import CallToActionPage from './CallToActionPage.vue';
import { Heading } from '@dbco/ui-library';

vi.mock('@/env');

const createComponent = setupTest((localVue: VueConstructor, props?: object) => {
    return shallowMount(CallToActionPage, {
        localVue,
        propsData: props,
    });
});

describe('CallToActionPage.vue', () => {
    it('should render the CallToAction page', () => {
        const wrapper = createComponent();
        expect(wrapper.exists()).toBe(true);
    });

    it('should show title-bar', () => {
        const wrapper = createComponent();

        expect(wrapper.findComponent(Heading).exists()).toBe(true);
        expect(wrapper.findComponent(Heading).text()).toBe('Taken');
    });
});

import type { VueConstructor } from 'vue';
import { shallowMount } from '@vue/test-utils';
import { setupTest } from '@/utils/test';
import DetailHeading from './DetailHeading.vue';

const createComponent = setupTest((localVue: VueConstructor, propsData?: object) => {
    return shallowMount(DetailHeading, { localVue, propsData });
});

describe('DetailHeading.vue', () => {
    it('should render a heading', () => {
        const wrapper = createComponent();
        expect(wrapper.exists()).toBe(true);
    });
});

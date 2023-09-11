import type { VueConstructor } from 'vue';
import { shallowMount } from '@vue/test-utils';
import { setupTest } from '@/utils/test';
import AdminModule from './AdminModule.vue';

const createComponent = setupTest((localVue: VueConstructor, props?: object) => {
    return shallowMount(AdminModule, { localVue, propsData: props, stubs: ['router-view'] });
});

describe('AdminModule.vue', () => {
    it('should render', () => {
        const wrapper = createComponent();
        expect(wrapper.exists()).toBe(true);
    });
});

import { shallowMount } from '@vue/test-utils';
import TabBar from './TabBar.vue';
import { setupTest } from '@/utils/test';
import type { VueConstructor } from 'vue';

const createComponent = setupTest((localVue: VueConstructor) => {
    return shallowMount(TabBar, {
        localVue,
        slots: {
            left: 'content-left',
            right: 'content-right',
        },
    });
});

describe('TabBar.vue', () => {
    it('should render TabBar', () => {
        const wrapper = createComponent();

        expect(wrapper.html()).toContain('content-left');
        expect(wrapper.html()).toContain('content-right');
    });
});

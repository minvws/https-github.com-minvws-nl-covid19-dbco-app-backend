import type { VueConstructor } from 'vue';
import { shallowMount } from '@vue/test-utils';
import { setupTest } from '@/utils/test';
import AdminTabs from './AdminTabs.vue';
import { TabList } from '@dbco/ui-library';

const createComponent = setupTest((localVue: VueConstructor, props?: object) => {
    return shallowMount(AdminTabs, { localVue, propsData: props });
});

describe('AdminTabs.vue', () => {
    it('should render', () => {
        const wrapper = createComponent();
        const tabList = wrapper.findComponent(TabList);

        expect(wrapper.exists()).toBe(true);
        expect(tabList.exists()).toBe(true);
    });
});

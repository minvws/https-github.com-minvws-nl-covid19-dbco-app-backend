import { mount } from '@vue/test-utils';
import { faker } from '@faker-js/faker';
import { createDefaultLocalVue } from '../../test/local-vue';
import LayoutSidebar from './LayoutSidebar.vue';

function createComponent(propsData?: { useFlexSpace?: boolean }, slots?: { default: string; sidebar: string }) {
    return mount(LayoutSidebar, {
        localVue: createDefaultLocalVue(),
        propsData,
        slots,
    });
}

describe('LayoutSidebar.vue', () => {
    it('should be able to render with a sidebar', () => {
        const sidebarContent = faker.lorem.word();
        const wrapper = createComponent(
            {},
            {
                default: '',
                sidebar: sidebarContent,
            }
        );

        expect(wrapper.classes()).toEqual(expect.arrayContaining(['tw-flex', 'tw-flex-row']));
        expect(wrapper.text()).toBe(sidebarContent);
    });

    it('should be able to render with a wrapper', () => {
        const sidebarContent = faker.lorem.word();
        const wrapper = createComponent(
            { useFlexSpace: true },
            {
                default: '',
                sidebar: sidebarContent,
            }
        );

        expect(wrapper.classes()).toEqual(expect.arrayContaining(['tw-relative', 'tw-flex-1']));
        expect(wrapper.text()).toBe(sidebarContent);
    });
});

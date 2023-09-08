import type { VueConstructor } from 'vue';
import { shallowMount } from '@vue/test-utils';
import { setupTest, fakerjs } from '@/utils/test';

import ChoreSidebar from './ChoreSidebar.vue';

const createComponent = setupTest((localVue: VueConstructor, givenProps?: object) => {
    return shallowMount<ChoreSidebar>(ChoreSidebar, {
        localVue,
        propsData: givenProps,
    });
});

describe('ChoreSidebar.vue', () => {
    const title = fakerjs.lorem.words();

    it('should render a sidebar with the title given through props as 3rd level heading', () => {
        // GIVEN a title through props
        // WHEN the sidebar renders
        const wrapper = createComponent({ title });

        // THEN the sidebar should have a third level heading with the given title
        expect(wrapper.find('h3').text()).toBe(title);
    });

    it('should render a sidebar with the hint given through props as a paragraph', () => {
        // GIVEN a hint through props
        const hint = fakerjs.lorem.sentence();

        // WHEN the sidebar renders
        const wrapper = createComponent({ title, hint });

        // THEN the sidebar should have a paragraph with the given hint
        expect(wrapper.find('p').text()).toBe(hint);
    });

    it('should render a sidebar without a paragraph when there is no hint', () => {
        // GIVEN there is no hint
        // WHEN the sidebar renders
        const wrapper = createComponent({ title });

        // THEN the sidebar should not have a paragraph
        expect(wrapper.find('p').exists()).toBe(false);
    });
});

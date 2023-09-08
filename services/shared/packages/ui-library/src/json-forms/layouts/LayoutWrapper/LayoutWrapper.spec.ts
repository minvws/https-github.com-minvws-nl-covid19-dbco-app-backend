import { faker } from '@faker-js/faker';
import { mount } from '@vue/test-utils';
import { createDefaultLocalVue } from '../../../test/local-vue';
import type { JsonFormsLayout } from '../../types';
import { default as LayoutWrapper } from './LayoutWrapper.vue';

type Props = {
    layout: Pick<JsonFormsLayout, 'visible'>;
};

function createComponent(propsData: Props, content?: string) {
    return mount(LayoutWrapper, {
        localVue: createDefaultLocalVue(),
        propsData: {
            ...propsData,
            control: propsData.layout as JsonFormsLayout,
        },
        slots: {
            default: content || faker.lorem.sentence(),
        },
    });
}

describe('ControlWrapper.vue', () => {
    it('should be visible when the layout is visible', () => {
        const content = faker.lorem.sentence();
        const wrapper = createComponent({ layout: { visible: true } }, content);
        expect(wrapper.isVisible()).toBe(true);
        expect(wrapper.text()).toBe(content);
    });

    it('should NOT be visible when the layout is NOT visible', () => {
        const wrapper = createComponent({ layout: { visible: false } });
        expect(wrapper.isVisible()).toBe(false);
    });
});

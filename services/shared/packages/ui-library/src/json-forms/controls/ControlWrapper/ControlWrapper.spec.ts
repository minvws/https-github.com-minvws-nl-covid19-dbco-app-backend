import { faker } from '@faker-js/faker';
import { mount } from '@vue/test-utils';
import { createDefaultLocalVue } from '../../../test/local-vue';
import type { ControlBindings } from '../../types';
import { default as ControlWrapper } from './ControlWrapper.vue';

type Props = {
    control: Pick<ControlBindings, 'visible'>;
};

function createComponent(propsData: Props, content?: string) {
    return mount(ControlWrapper, {
        localVue: createDefaultLocalVue(),
        propsData: {
            ...propsData,
            control: propsData.control as ControlBindings,
        },
        slots: {
            default: content || faker.lorem.sentence(),
        },
    });
}

describe('ControlWrapper.vue', () => {
    it('should be visible when the control is visible', () => {
        const content = faker.lorem.sentence();
        const wrapper = createComponent({ control: { visible: true } }, content);
        expect(wrapper.isVisible()).toBe(true);
        expect(wrapper.text()).toBe(content);
    });

    it('should NOT be visible when the control is NOT visible', () => {
        const wrapper = createComponent({ control: { visible: false } });
        expect(wrapper.isVisible()).toBe(false);
    });
});

import { mount } from '@vue/test-utils';
import { createDefaultLocalVue } from '../../test/local-vue';
import Radio from './Radio.vue';
import { faker } from '@faker-js/faker';

type Props = {
    checked?: boolean;
};

function createComponent(propsData: Props = {}, content?: string) {
    return mount(Radio, {
        localVue: createDefaultLocalVue(),
        propsData: { value: faker.lorem.word(), ...propsData },
        slots: {
            default: content || '',
        },
    });
}

describe('Radio.vue', () => {
    it('should render unchecked by default', () => {
        const wrapper = createComponent();
        const checkbox = wrapper.find<HTMLInputElement>('input[type="radio"]');
        expect(checkbox.element.checked).toBeFalsy();
    });

    it('should render checked when prop checked is true', () => {
        const wrapper = createComponent({ checked: true });
        const checkbox = wrapper.find<HTMLInputElement>('input[type="radio"]');
        expect(checkbox.element.checked).toBeTruthy();
    });

    it('should render content if given', () => {
        const content = faker.lorem.sentence();
        const wrapper = createComponent({}, content);
        expect(wrapper.find('label').text()).toBe(content);
    });
});

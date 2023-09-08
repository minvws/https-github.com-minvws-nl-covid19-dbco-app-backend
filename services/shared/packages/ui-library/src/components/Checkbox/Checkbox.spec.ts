import { mount } from '@vue/test-utils';
import { createDefaultLocalVue } from '../../test/local-vue';
import Checkbox from './Checkbox.vue';
import { faker } from '@faker-js/faker';

type Props = {
    checked?: boolean;
};

function createComponent(propsData: Props = {}, content?: string) {
    return mount(Checkbox, {
        localVue: createDefaultLocalVue(),
        propsData,
        slots: {
            default: content || '',
        },
    });
}

describe('Checkbox.vue', () => {
    it('should render unchecked by default', () => {
        const wrapper = createComponent();
        const checkbox = wrapper.find<HTMLInputElement>('input[type="checkbox"]');
        expect(checkbox.element.checked).toBeFalsy();
    });

    it('should render checked when prop checked is true', () => {
        const wrapper = createComponent({ checked: true });
        const checkbox = wrapper.find<HTMLInputElement>('input[type="checkbox"]');
        expect(checkbox.element.checked).toBeTruthy();
    });

    it('should render content if given', () => {
        const content = faker.lorem.sentence();
        const wrapper = createComponent({}, content);
        expect(wrapper.find('label').text()).toBe(content);
    });
});

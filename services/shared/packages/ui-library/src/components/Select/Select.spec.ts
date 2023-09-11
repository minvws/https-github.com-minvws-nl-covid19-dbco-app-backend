import { mount } from '@vue/test-utils';
import { createDefaultLocalVue } from '../../test/local-vue';
import Select from './Select.vue';
import { faker } from '@faker-js/faker';

type Props = {
    placeholder?: string;
};

function createComponent(propsData: Props, content: string) {
    return mount(Select, {
        localVue: createDefaultLocalVue(),
        propsData,
        slots: {
            default: content,
        },
    });
}

describe('Select.vue', () => {
    it('should render with options', async () => {
        const wrapper = createComponent(
            {},
            `
        <option value="option1">Option 1</option>
        <option value='option2'>Option 2</option>
        <option value='option3'>Option 3</option>
        <option value='option4'>Option 4</option>
        <option value='option5'>Option 5</option>
        `
        );
        expect(wrapper.findAll('option')).toHaveLength(5);
    });

    it('should render with a placeholder', async () => {
        const placeholder = faker.lorem.sentence();
        const wrapper = createComponent(
            { placeholder },
            `
        <option value="option1">Option 1</option>
        <option value='option2'>Option 2</option>
        <option value='option3'>Option 3</option>
        `
        );
        expect(wrapper.find('option').text()).toBe(placeholder);
    });

    it('should use a different style when the placeholder is selected', async () => {
        const placeholder = faker.lorem.sentence();
        const wrapper = createComponent(
            { placeholder },
            `
        <option value="option1">Option 1</option>
        <option value='option2'>Option 2</option>
        <option value='option3'>Option 3</option>
        `
        );

        const select = wrapper.find('select');
        expect(select.classes()).toContain('tw-text-gray-600');

        await select.findAll('option').at(1).setSelected();

        expect(select.classes()).not.toContain('tw-text-gray-600');
    });
});

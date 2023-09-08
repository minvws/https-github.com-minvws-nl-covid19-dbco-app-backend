import { mount } from '@vue/test-utils';
import { createDefaultLocalVue } from '../../test/local-vue';
import { defineComponent, ref } from 'vue';
import RadioButton from './RadioButton.vue';
import { Radio, RadioGroup } from '.';
import type { RadioVariant } from './radio-props';
import { faker } from '@faker-js/faker';

const RadioGroupTest = defineComponent({
    props: { variant: String, value: String, name: String },
    components: { RadioGroup, Radio },
    setup(props) {
        const value = ref(props.value);
        const variant = ref(props.variant);
        return { variant, value };
    },
    template: `
    <RadioGroup :variant="variant" :value="value" :name="name">
        <Radio value="true">Option 1</Radio>
        <Radio value="false">Option 2</Radio>
    </RadioGroup>`,
});

type RadioGroupProps = {
    name?: string;
    value?: string;
    variant?: RadioVariant;
    onChange?: Function; // eslint-disable-line @typescript-eslint/ban-types
};

function createComponent({ onChange, ...propsData }: RadioGroupProps = {}) {
    return mount(RadioGroupTest as any, {
        localVue: createDefaultLocalVue(),
        propsData,
        listeners: onChange ? { change: onChange } : {},
    });
}

function getSelectedRadio(wrapper: ReturnType<typeof createComponent>) {
    return wrapper.find('input[type="radio"]:checked');
}

describe('RadioGroup', () => {
    it('RadioGroup should render with the no checked Radio by default', () => {
        const wrapper = createComponent();
        expect(getSelectedRadio(wrapper).exists()).toBeFalsy();
    });

    it('RadioGroup should check the radio with a matching value', () => {
        const wrapper = createComponent({ value: 'false' });
        expect(getSelectedRadio(wrapper).attributes('value')).toBe('false');
    });

    it('RadioGroup should pass on the variant to the Radio elements', () => {
        const wrapper = createComponent({ variant: 'button' });
        const radioButtons = wrapper.findAllComponents(RadioButton);
        expect(radioButtons.length).toBe(2);
    });

    it('RadioGroup should pass on the name to the Radio elements', () => {
        const radioGroupName = faker.lorem.word();
        const wrapper = createComponent({ name: radioGroupName });
        const radios = wrapper.findAll('input');
        const radioNames = radios.wrappers.map((radio) => radio.attributes('name'));
        expect(radioNames).toEqual([radioGroupName, radioGroupName]);
    });
});

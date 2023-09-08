import { faker } from '@faker-js/faker';
import type { JsonFormsControlTestConfig } from '../../test';
import { createJsonFormsControlTest } from '../../test';
import RadioOneOfEnumControl from './RadioOneOfEnumControl.vue';

type TestConfig = {
    label?: string;
} & Pick<JsonFormsControlTestConfig, 'onChange'>;

const stringEnumOptions = faker.helpers.uniqueArray(faker.lorem.word, 3);

function createComponent({ label, onChange }: TestConfig = {}) {
    return createJsonFormsControlTest({
        control: RadioOneOfEnumControl,
        data: {},
        schema: {
            type: 'object',
            properties: {
                value: { type: 'string', oneOf: stringEnumOptions.map((x) => ({ const: x })) },
            },
        },
        uiSchema: {
            type: 'Control',
            scope: '#/properties/value',
            label: label || faker.lorem.sentence(),
            options: {
                format: 'radio-button',
            },
        },
        onChange,
    });
}

describe('RadioEnumControl.vue', () => {
    it('should render a label and inputs for all the values', () => {
        const label = faker.lorem.sentence();
        const formWrapper = createComponent({ label });

        const radios = formWrapper.findAll('input[type="radio"]');
        const values = radios.wrappers.map((x) => x.attributes('value'));
        expect(values).toEqual(stringEnumOptions);

        const labelElement = formWrapper.find<HTMLLabelElement>('label');
        expect(labelElement.text()).toBe(label);
    });

    it('should handle changes', async () => {
        const onChange = vi.fn();
        const formWrapper = createComponent({ onChange });

        const radios = formWrapper.findAll('input[type="radio"]');
        await radios.at(1).setChecked();

        expect(onChange).toHaveBeenCalledWith({ data: { value: stringEnumOptions[1] }, errors: [] });
    });
});

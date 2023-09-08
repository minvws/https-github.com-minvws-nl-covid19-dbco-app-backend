import { faker } from '@faker-js/faker';
import { createJsonFormsControlTest } from '../../test';
import BooleanControl from './BooleanControl.vue';

type TestConfig = {
    label?: string;
};

function createComponent({ label }: TestConfig) {
    return createJsonFormsControlTest({
        control: BooleanControl,
        data: { value: false },
        schema: {
            type: 'object',
            properties: {
                value: { type: 'boolean' },
            },
        },
        uiSchema: {
            type: 'Control',
            scope: '#/properties/value',
            label: label || faker.lorem.sentence(),
        },
    });
}

describe('BooleanControl.vue', () => {
    it('should render a label and input with a shared id', () => {
        const label = faker.lorem.sentence();
        const formWrapper = createComponent({ label });

        const labelElement = formWrapper.find<HTMLLabelElement>('label');
        const input = formWrapper.find<HTMLInputElement>('input[type="checkbox"]');

        expect(labelElement.text()).toBe(label);
        expect(input.attributes('id')).toBe(labelElement.attributes('for'));
    });
});

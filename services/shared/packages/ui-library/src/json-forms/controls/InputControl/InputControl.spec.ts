import { faker } from '@faker-js/faker';
import type { JsonFormsBaseTestConfig } from '../../test';
import { createJsonFormsControlTest } from '../../test';
import InputControl from './InputControl.vue';

type TestConfig = {
    label?: string;
    cells?: JsonFormsBaseTestConfig['cells'];
};

function createComponent({ label, cells }: TestConfig) {
    return createJsonFormsControlTest({
        control: InputControl,
        data: { value: '' },
        schema: {
            type: 'object',
            properties: {
                value: { type: 'string' },
            },
        },
        uiSchema: {
            type: 'Control',
            scope: '#/properties/value',
            label: label || faker.lorem.sentence(),
        },
        cells,
    });
}

describe('InputControl.vue', () => {
    it('should render a label and input with a shared id', () => {
        const label = faker.lorem.sentence();
        const formWrapper = createComponent({ label });

        const labelElement = formWrapper.find<HTMLLabelElement>('label');
        const input = formWrapper.find<HTMLInputElement>('input[type="text"]');

        expect(labelElement.text()).toBe(label);
        expect(input.attributes('id')).toBe(labelElement.attributes('for'));
    });

    it('should log a warning if no applicable cell renderer was found', () => {
        const spy = vi.spyOn(console, 'warn').mockImplementation(() => {});
        createComponent({ cells: [] });
        expect(spy.mock.calls[0][0]).toBe(`No applicable cell found.`);
        spy.mockRestore();
    });
});

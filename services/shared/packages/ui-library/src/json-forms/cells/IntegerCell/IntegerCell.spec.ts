import { faker } from '@faker-js/faker';
import type { JsonFormsCellTestConfig } from '../../test';
import { createJsonFormsCellTest } from '../../test';
import type { UiSchemaOptions } from '../../types';
import IntegerCell from './IntegerCell.vue';

type TestConfig = {
    value?: number;
    uiOptions?: UiSchemaOptions['string'];
} & Pick<JsonFormsCellTestConfig, 'onChange'>;

function createComponent({ value, onChange, uiOptions }: TestConfig = {}) {
    const wrapper = createJsonFormsCellTest({
        cell: IntegerCell,
        data: { value },
        schema: { type: 'object', properties: { value: { type: 'integer' } } },
        uiSchema: {
            type: 'Control',
            scope: '#/properties/value',
            options: uiOptions,
        },
        onChange,
    });

    return wrapper.find<HTMLInputElement>('input[type="number"]');
}

describe('IntegerCell.vue', () => {
    it('should render and handle changes', async () => {
        const onChange = vi.fn();
        const value = faker.number.int();
        const input = createComponent({ value, onChange });

        expect(input.element.value).toBe(`${value}`);

        const newValue = faker.number.int();
        input.element.value = `${newValue}`;
        await input.trigger('change');

        expect(onChange).toHaveBeenCalledWith({ data: { value: newValue }, errors: [] });
    });

    it('value should be undefined when the input contains an empty string', async () => {
        const onChange = vi.fn();
        const input = createComponent({ value: faker.number.int(), onChange });

        input.element.value = '';
        await input.trigger('change');

        expect(onChange).toHaveBeenCalledWith({ data: {}, errors: [] });
    });

    it('should NOT allow decimal values', async () => {
        const onChange = vi.fn();
        const input = createComponent({ onChange });

        const newValue = faker.number.float({ min: 0, max: 100, precision: 2 });

        input.element.value = newValue.toString();
        await input.trigger('change');

        expect(onChange).toHaveBeenCalledWith({ data: { value: Math.trunc(newValue) }, errors: [] });
    });

    it('should render with focus', () => {
        const input = createComponent({ uiOptions: { focus: true } });
        expect(input.attributes('autofocus')).toBe('true');
    });
});

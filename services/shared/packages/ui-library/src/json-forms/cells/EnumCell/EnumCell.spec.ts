import { faker } from '@faker-js/faker';
import type { JsonFormsCellTestConfig } from '../../test';
import { createJsonFormsCellTest } from '../../test';
import type { UiSchemaOptions } from '../../types';
import EnumCell from './EnumCell.vue';

type TestConfig = {
    value?: string | number;
    uiOptions?: UiSchemaOptions['enum'];
} & Pick<JsonFormsCellTestConfig, 'onChange'>;

const stringEnumOptions = faker.helpers.uniqueArray(faker.lorem.word, 3);

function createComponent({ value, onChange, uiOptions }: TestConfig = {}) {
    const wrapper = createJsonFormsCellTest({
        cell: EnumCell,
        data: { value },
        schema: { type: 'object', properties: { value: { type: 'string', enum: stringEnumOptions } } },
        uiSchema: {
            type: 'Control',
            scope: '#/properties/value',
            options: uiOptions || {},
        },
        onChange,
    });

    return wrapper.find<HTMLSelectElement>('select');
}

describe('EnumCell.vue', () => {
    it('should render and handle changes', async () => {
        const onChange = vi.fn();
        const value = stringEnumOptions[0];
        const input = createComponent({ value, onChange });
        expect(input.element.value).toBe(value);

        const newValue = stringEnumOptions[1];
        input.element.value = newValue;
        await input.trigger('blur');

        expect(onChange).toHaveBeenCalledWith({ data: { value: newValue }, errors: [] });
    });

    it('value should be undefined when the input contains an empty string', async () => {
        const onChange = vi.fn();
        const input = createComponent({ value: stringEnumOptions[0], onChange });
        input.element.value = '';
        await input.trigger('blur');

        expect(onChange).toHaveBeenCalledWith({ data: {}, errors: [] });
    });

    it('should render with focus', () => {
        const input = createComponent({ uiOptions: { focus: true } });
        expect(input.attributes('autofocus')).toBe('true');
    });
});

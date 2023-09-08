import { faker } from '@faker-js/faker';
import type { JsonFormsCellTestConfig } from '../../test';
import { createJsonFormsCellTest } from '../../test';
import type { UiSchemaOptions } from '../../types';
import TextCell from './TextCell.vue';

type TestConfig = {
    value?: string;
    maxLength?: number;
    uiOptions?: UiSchemaOptions['string'];
} & Pick<JsonFormsCellTestConfig, 'onChange'>;

function createComponent({ value, maxLength, onChange, uiOptions }: TestConfig = {}) {
    const wrapper = createJsonFormsCellTest({
        cell: TextCell,
        data: { value: value || '' },
        schema: { type: 'object', properties: { value: { type: 'string', maxLength } } },
        uiSchema: {
            type: 'Control',
            scope: '#/properties/value',
            options: uiOptions,
        },
        onChange,
    });

    return wrapper.find<HTMLInputElement>('input[type="text"]');
}

describe('TextCell.vue', () => {
    it('should render and handle changes', async () => {
        const onChange = vi.fn();
        const value = faker.lorem.sentence();
        const input = createComponent({ value, onChange });
        expect(input.element.value).toBe(value);

        const newValue = faker.lorem.sentence();
        input.element.value = newValue;
        await input.trigger('change');

        expect(onChange).toHaveBeenCalledWith({ data: { value: newValue }, errors: [] });
    });

    it('value should be undefined when the input contains an empty string', async () => {
        const onChange = vi.fn();
        const input = createComponent({ value: faker.lorem.sentence(), onChange });
        input.element.value = '';
        await input.trigger('change');

        expect(onChange).toHaveBeenCalledWith({ data: {}, errors: [] });
    });

    it('should render with a placeholder', () => {
        const placeholder = faker.lorem.sentence();
        const input = createComponent({ uiOptions: { placeholder } });
        expect(input.attributes('placeholder')).toBe(placeholder);
    });

    it('should render with focus', () => {
        const input = createComponent({ uiOptions: { focus: true } });
        expect(input.attributes('autofocus')).toBe('true');
    });

    it('can be used with maxLength', () => {
        const maxLength = faker.number.int();
        const input = createComponent({ maxLength });
        expect(input.attributes('maxlength')).toBe(`${maxLength}`);
    });
});

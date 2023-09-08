import { faker } from '@faker-js/faker';
import type { JsonFormsCellTestConfig } from '../../test';
import { createJsonFormsCellTest } from '../../test';
import type { UiSchemaOptions } from '../../types';
import TextareaCell from './TextareaCell.vue';

type TestConfig = {
    value?: string;
    maxLength?: number;
    uiOptions?: UiSchemaOptions['string'];
} & Pick<JsonFormsCellTestConfig, 'onChange'>;

function createComponent({ value, maxLength, onChange, uiOptions = {} }: TestConfig = {}) {
    const wrapper = createJsonFormsCellTest({
        cell: TextareaCell,
        data: { value: value || '' },
        schema: { type: 'object', properties: { value: { type: 'string', maxLength } } },
        uiSchema: {
            type: 'Control',
            scope: '#/properties/value',
            options: { ...uiOptions, multi: true },
        },
        onChange,
    });

    return wrapper.find<HTMLTextAreaElement>('textarea');
}

describe('TextareaCell.vue', () => {
    it('should render and handle changes', async () => {
        const onChange = vi.fn();
        const value = faker.lorem.sentence();
        const textarea = createComponent({ value, onChange });

        expect(textarea.element.value).toBe(value);
        const newValue = faker.lorem.sentence();

        textarea.element.value = newValue;
        await textarea.trigger('change');

        expect(onChange).toHaveBeenCalledWith({ data: { value: newValue }, errors: [] });
    });

    it('value should be undefined when the textare contains an empty string', async () => {
        const onChange = vi.fn();
        const textarea = createComponent({ value: faker.lorem.sentence(), onChange });

        textarea.element.value = '';
        await textarea.trigger('change');

        expect(onChange).toHaveBeenCalledWith({ data: {}, errors: [] });
    });

    it('should render with a placeholder', () => {
        const placeholder = faker.lorem.sentence();
        const textarea = createComponent({ uiOptions: { placeholder } });
        expect(textarea.attributes('placeholder')).toBe(placeholder);
    });

    it('should render with focus', () => {
        const textarea = createComponent({ uiOptions: { focus: true } });
        expect(textarea.attributes('autofocus')).toBe('true');
    });

    it('can be used with maxLength', () => {
        const maxLength = faker.number.int();
        const textarea = createComponent({ maxLength });
        expect(textarea.attributes('maxlength')).toBe(`${maxLength}`);
    });
});

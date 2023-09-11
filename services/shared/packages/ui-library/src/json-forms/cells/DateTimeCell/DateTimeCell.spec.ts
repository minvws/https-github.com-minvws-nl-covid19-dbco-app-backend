import { faker } from '@faker-js/faker';
import type { JsonFormsCellTestConfig } from '../../test';
import { createJsonFormsCellTest } from '../../test';
import type { UiSchemaOptions } from '../../types';
import DateTimeCell from './DateTimeCell.vue';

type TestConfig = {
    value?: string;
    uiOptions?: UiSchemaOptions['date'];
} & Pick<JsonFormsCellTestConfig, 'onChange'>;

const randomIso8601Date = () => faker.date.past().toISOString();

function createComponent({ value, onChange, uiOptions }: TestConfig = {}) {
    const wrapper = createJsonFormsCellTest({
        cell: DateTimeCell,
        data: { value: value || '' },
        schema: { type: 'object', properties: { value: { type: 'string', format: 'date-time' } } },
        uiSchema: {
            type: 'Control',
            scope: '#/properties/value',
            options: uiOptions,
        },
        onChange,
    });

    return wrapper.find<HTMLInputElement>('input[type="datetime-local"]');
}

const dropSecondsAndTimezone = (value: string) => value.substring(0, 16);
const dropMillisecondsAndTimezone = (value: string) => value.substring(0, 19);

describe('DateTimeCell.vue', () => {
    it('should render and handle changes', async () => {
        const onChange = vi.fn();
        const value = randomIso8601Date();
        const input = createComponent({ value, onChange });
        expect(input.element.value).toBe(dropSecondsAndTimezone(value));

        const newValue = randomIso8601Date();
        input.element.value = dropMillisecondsAndTimezone(newValue);
        await input.trigger('blur');

        const newValueDateTime = `${dropSecondsAndTimezone(newValue)}:00`;

        expect(onChange).toHaveBeenCalledWith({ data: { value: newValueDateTime }, errors: [] });
    });

    it('value should be undefined when the input contains an empty string', async () => {
        const onChange = vi.fn();
        const input = createComponent({ value: randomIso8601Date(), onChange });
        input.element.value = '';
        await input.trigger('blur');

        expect(onChange).toHaveBeenCalledWith({ data: {}, errors: [] });
    });

    it('should render with focus', () => {
        const input = createComponent({ uiOptions: { focus: true } });
        expect(input.attributes('autofocus')).toBe('true');
    });
});

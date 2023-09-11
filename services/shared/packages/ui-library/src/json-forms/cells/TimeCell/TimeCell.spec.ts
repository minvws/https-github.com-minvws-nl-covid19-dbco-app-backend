import { faker } from '@faker-js/faker';
import type { JsonFormsCellTestConfig } from '../../test';
import { createJsonFormsCellTest } from '../../test';
import type { UiSchemaOptions } from '../../types';
import TimeCell from './TimeCell.vue';

type TestConfig = {
    value?: string;
    uiOptions?: UiSchemaOptions['date'];
} & Pick<JsonFormsCellTestConfig, 'onChange'>;

const randomIso8601Time = () =>
    // eslint-disable-next-line @typescript-eslint/no-non-null-assertion
    faker.date
        .past()
        .toISOString()
        .match(/T(\d\d:\d\d)/)![1];

function createComponent({ value, onChange, uiOptions }: TestConfig = {}) {
    const wrapper = createJsonFormsCellTest({
        cell: TimeCell,
        data: { value: value || '' },
        schema: { type: 'object', properties: { value: { type: 'string', format: 'time' } } },
        uiSchema: {
            type: 'Control',
            scope: '#/properties/value',
            options: uiOptions,
        },
        onChange,
    });

    return wrapper.find<HTMLInputElement>('input[type="time"]');
}

describe('TimeCell.vue', () => {
    it('should render and handle changes', async () => {
        const onChange = vi.fn();
        const value = randomIso8601Time();
        const input = createComponent({ value, onChange });
        expect(input.element.value).toBe(value);

        const newValue = randomIso8601Time();
        input.element.value = newValue;
        await input.trigger('blur');

        const newValueTime = `${newValue}:00`;

        expect(onChange).toHaveBeenCalledWith({ data: { value: newValueTime }, errors: [] });
    });

    it('should should not change the amount of seconds if it was defined', async () => {
        const onChange = vi.fn();
        const input = createComponent({ value: randomIso8601Time(), onChange });

        const newValue = `${randomIso8601Time()}:34`;
        input.element.value = newValue;
        await input.trigger('blur');

        expect(onChange).toHaveBeenCalledWith({ data: { value: newValue }, errors: [] });
    });

    it('value should be undefined when the input contains an empty string', async () => {
        const onChange = vi.fn();
        const input = createComponent({ value: randomIso8601Time(), onChange });
        input.element.value = '';
        await input.trigger('blur');

        expect(onChange).toHaveBeenCalledWith({ data: {}, errors: [] });
    });

    it('should render with focus', () => {
        const input = createComponent({ uiOptions: { focus: true } });
        expect(input.attributes('autofocus')).toBe('true');
    });
});

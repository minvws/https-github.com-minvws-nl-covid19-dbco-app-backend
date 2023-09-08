import type { JsonFormsCellTestConfig } from '../../test';
import { createJsonFormsCellTest } from '../../test';
import type { UiSchemaOptions } from '../../types';
import BooleanCell from './BooleanCell.vue';

type TestConfig = {
    value?: boolean;
    uiOptions?: UiSchemaOptions['boolean'];
} & Pick<JsonFormsCellTestConfig, 'onChange'>;

function createComponent({ value, onChange, uiOptions }: TestConfig = {}) {
    const wrapper = createJsonFormsCellTest({
        cell: BooleanCell,
        data: { value: value || false },
        schema: { type: 'object', properties: { value: { type: 'boolean' } } },
        uiSchema: {
            type: 'Control',
            scope: '#/properties/value',
            options: { ...(uiOptions || {}) },
        },
        onChange,
    });

    return wrapper.find<HTMLInputElement>('input[type="checkbox"]');
}

describe('BooleanCell.vue', () => {
    it('should render and handle changes', async () => {
        const onChange = vi.fn();
        const value = false;
        const checkbox = createComponent({ value, onChange });

        expect(checkbox.exists()).toBe(true);
        expect(checkbox.element.checked).toBe(value);

        await checkbox.setChecked(true);
        await checkbox.trigger('change');

        expect(onChange).toHaveBeenCalledWith({ data: { value: true }, errors: [] });
    });

    it('should render with focus', () => {
        const checkbox = createComponent({ uiOptions: { focus: true } });
        expect(checkbox.attributes('autofocus')).toBe('true');
    });
});

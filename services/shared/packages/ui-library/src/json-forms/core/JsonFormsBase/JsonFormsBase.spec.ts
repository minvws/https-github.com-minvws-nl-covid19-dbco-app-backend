import { faker } from '@faker-js/faker';
import { createJsonFormsBaseTest } from '../../test';
import type { FormError, JsonSchema } from '../../types';

describe('JsonFormsBase.vue', () => {
    it('should handle invalid additionalErrors', async () => {
        const spy = vi.spyOn(console, 'error').mockImplementation(() => {});
        const errorMessage = faker.lorem.sentence();
        const additionalErrors = [
            { instancePath: '/value', message: errorMessage },
            { instancePath: undefined },
            { foo: 'bar' },
            'foo',
            2,
        ];

        const formWrapper = createJsonFormsBaseTest({
            data: {
                value: '',
            },
            schema: {
                type: 'object',
                properties: { value: { type: 'string' } },
            },
            uiSchema: {
                type: 'Control',
                scope: '#/properties/value',
            },
            additionalErrors: additionalErrors as FormError[],
        });

        const errorId = formWrapper.find('input').attributes('aria-errormessage');
        const errors = formWrapper.find(`#${errorId}`);

        expect(spy).toHaveBeenNthCalledWith(4, 'Invalid additional error object: ', expect.anything());
        expect(errors.text()).toBe(errorMessage);

        spy.mockRestore();
    });

    it("should handle invalid schema's", async () => {
        const formWrapper = createJsonFormsBaseTest({
            data: {
                value: '',
            },
            schema: {
                type: 'object',
                properties: { value: { type: 'foo' } },
            } as unknown as JsonSchema,
            uiSchema: {
                type: 'Control',
                scope: '#/properties/value',
            },
        });

        expect(formWrapper.text()).toEqual(expect.stringContaining('Json schema is invalid!'));
    });

    it.only('should only emit changes when data is different from source', async () => {
        const onChange = vi.fn();
        const sourceValue = faker.lorem.word(10);
        const formWrapper = createJsonFormsBaseTest({
            data: {
                value: sourceValue,
            },
            schema: {
                type: 'object',
                properties: { value: { type: 'string' } },
            },
            uiSchema: {
                type: 'Control',
                scope: '#/properties/value',
            },
            onChange,
        });

        const input = formWrapper.find<HTMLInputElement>('input');
        const newValue = faker.lorem.word(5);
        input.element.value = newValue;
        await input.trigger('change');

        expect(onChange).toHaveBeenNthCalledWith(1, { data: { value: newValue }, errors: [] });
        onChange.mockClear();

        input.element.value = sourceValue;
        await input.trigger('change');

        expect(onChange).not.toHaveBeenCalled();
    });
});

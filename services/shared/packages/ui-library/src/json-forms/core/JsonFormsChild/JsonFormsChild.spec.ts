import { createMockFormActionHandler } from '../../../test/mocks';
import { inject } from 'vue';
import type { Mock } from 'vitest';
import type { Props } from './props';
import type { emits } from './emits';
import { createDefaultLocalVue, decorateWrapper, flushCallStack } from '../../../test';
import { mount } from '@vue/test-utils';
import JsonFormsChild from './JsonFormsChild.vue';
import { key as formActionHandlerKey } from '../JsonForms/provide/form-action-handler';
import type { UiSchema } from '../../types';
import { faker } from '@faker-js/faker';
import { defaultsDeep } from 'lodash';

const mockFormActionHandler = createMockFormActionHandler();

export function createJsonFormsChildTest({
    onChange,
    onFormLink,
    ...propsData
}: Props & {
    onChange?: (typeof emits)['change'];
    onFormLink?: (typeof emits)['formLink'];
}) {
    return decorateWrapper(
        // eslint-disable-next-line @typescript-eslint/no-explicit-any
        mount(JsonFormsChild as any /* fixes Volar type issue */, {
            localVue: createDefaultLocalVue(),
            propsData,
            listeners: {
                change: onChange || vi.fn(),
                formLink: onFormLink || vi.fn(),
            },
            provide: {
                [formActionHandlerKey]: null,
            },
        })
    );
}

describe('JsonFormsChild.vue', () => {
    beforeEach(() => {
        mockFormActionHandler.mockClear();
    });

    it('should error if only `$links` meta or `actionHandler` is provided', async () => {
        const spy = vi.spyOn(console, 'error').mockImplementation(() => {});
        createJsonFormsChildTest({
            data: { $links: {} },
            schema: {},
            uiSchema: {} as UiSchema,
        });

        (inject as Mock).mockImplementationOnce(() => mockFormActionHandler);

        createJsonFormsChildTest({
            data: {},
            schema: {},
            uiSchema: {} as UiSchema,
        });

        expect(spy).toHaveBeenNthCalledWith(
            2,
            'The `formActionHandler` works in combination with the `$links` meta data. You should not only provide one of them. Most likely you forgot to set the `actionHandler` on the `JsonForms` component.'
        );
        spy.mockRestore();
    });

    it('should bubble up change events if no action handler is to be used', async () => {
        const onChange = vi.fn();
        const formWrapper = createJsonFormsChildTest({
            data: {},
            schema: {
                type: 'object',
                properties: {
                    value: { type: 'string' },
                },
            },
            uiSchema: {
                type: 'Control',
                scope: '#/properties/value',
            },
            onChange,
        });

        const input = formWrapper.find<HTMLInputElement>('input');
        const newValue = faker.lorem.word();
        input.element.value = newValue;
        await input.trigger('change');

        expect(onChange).toHaveBeenCalledWith({ data: { value: newValue }, errors: [] });
    });

    it('should use the action handler to update data changes when using the `$links` meta data', async () => {
        const onChange = vi.fn();
        (inject as Mock).mockImplementationOnce(() => mockFormActionHandler);
        const formData = {
            data: {
                $links: {
                    update: { method: 'PUT', href: '/update' },
                },
            },
            schema: {
                type: 'object',
                properties: {
                    value: { type: 'string' },
                },
            },
            uiSchema: {
                type: 'Control',
                scope: '#/properties/value',
            },
        } as const;

        const formWrapper = createJsonFormsChildTest({
            ...formData,
            onChange,
        });

        const input = formWrapper.find<HTMLInputElement>('input');
        const newValue = faker.lorem.word();
        input.element.value = newValue;
        await input.trigger('change');
        await flushCallStack();

        const expectedData = defaultsDeep({ value: newValue }, formData.data);
        expect(mockFormActionHandler.update).toHaveBeenCalledWith(
            {
                method: 'PUT',
                href: '/update',
            },
            expectedData
        );
        expect(onChange).toHaveBeenCalledWith({ data: expectedData, errors: [] });
    });

    it.only('should throw if update handler does not return any data', async () => {
        (inject as Mock).mockImplementationOnce(() => mockFormActionHandler);
        mockFormActionHandler.update.mockImplementationOnce(() => undefined as any);

        const formData = {
            data: {
                $links: {
                    update: { href: '/update' },
                },
            },
            schema: {},
            uiSchema: {} as UiSchema,
        } as const;

        let updateError;

        // the regular `expect(() => {}).toThrow()` does not seem to work here
        try {
            const formWrapper = createJsonFormsChildTest(formData);
            await (formWrapper.vm as any).handleFormChange({ data: {} });
        } catch (error) {
            updateError = error;
        }

        expect(updateError).toEqual(
            expect.objectContaining({ message: 'No data received from the form action update handler!' })
        );
    });
});

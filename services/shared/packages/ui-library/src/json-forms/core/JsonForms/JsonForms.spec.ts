import { faker } from '@faker-js/faker';
import { createJsonFormsTest } from '../../test';
import { createMockFormActionHandler } from '../../../test/mocks';
import { flushCallStack } from '../../../test';
import { cloneDeep, set } from 'lodash';
import type { Wrapper } from '@vue/test-utils';
import { provide } from 'vue';
import { key } from './provide/form-action-handler';
import type { Mock } from 'vitest';
import type { UiSchema } from '../../types';

const mockFormActionHandler = createMockFormActionHandler();

describe('JsonForms.vue', () => {
    beforeEach(() => {
        (provide as Mock).mockClear();
    });

    it('should provide the action handler', async () => {
        createJsonFormsTest({
            data: { $links: {} },
            schema: {},
            uiSchema: {} as UiSchema,
            actionHandler: mockFormActionHandler,
        });
        expect(provide).toHaveBeenCalledWith(key, mockFormActionHandler);
    });

    it('should handle changes and errors', async () => {
        const onChange = vi.fn();
        const formData = {
            data: {},
            schema: {
                type: 'object',
                properties: {
                    value: { type: 'string', minLength: 10 },
                },
            },
            uiSchema: {
                type: 'Control',
                scope: '#/properties/value',
            },
        } as const;

        const formWrapper = createJsonFormsTest({
            ...formData,
            onChange,
        });

        const input = formWrapper.find<HTMLInputElement>('input');
        const newValue = faker.lorem.word(11);
        input.element.value = newValue;
        await input.trigger('change');

        expect(onChange).toHaveBeenCalledWith({ data: { value: newValue }, errors: [] });

        input.element.value = faker.lorem.word(9);
        await input.trigger('change');

        const [{ errors }] = onChange.mock.lastCall;
        expect(errors.length).toBe(1);
        expect(errors[0]).toEqual(expect.objectContaining({ instancePath: '/value', keyword: 'minLength' }));
    });

    it('should handle changes and errors from child forms', async () => {
        const onChange = vi.fn();
        const formData = {
            data: {
                value: {
                    childValue: faker.lorem.word(10),
                    $links: {
                        update: { href: '/' },
                    },
                },
                deep: {
                    child: {
                        deepChildValue: faker.lorem.word(10),
                        $links: {
                            update: { href: '/' },
                        },
                    },
                },
                $links: {
                    update: { href: '/' },
                },
            },
            schema: {
                type: 'object',
                properties: {
                    value: {
                        type: 'object',
                        properties: {
                            childValue: { type: 'string', minLength: 10 },
                        },
                    },
                    deep: {
                        type: 'object',
                        properties: {
                            child: {
                                type: 'object',
                                properties: {
                                    deepChildValue: { type: 'string', minLength: 10 },
                                },
                            },
                        },
                    },
                },
            },
            uiSchema: {
                type: 'VerticalLayout',
                elements: [
                    {
                        type: 'Control',
                        scope: '#/properties/value',
                        customRenderer: 'ChildForm',
                        options: {
                            detail: {
                                type: 'Control',
                                scope: '#/properties/childValue',
                            },
                        },
                    },
                    {
                        type: 'Control',
                        scope: '#/properties/deep/properties/child',
                        customRenderer: 'ChildForm',
                        options: {
                            detail: {
                                type: 'Control',
                                scope: '#/properties/deepChildValue',
                            },
                        },
                    },
                ],
            },
        } as const;

        const formWrapper = createJsonFormsTest({
            ...formData,
            onChange,
            actionHandler: mockFormActionHandler,
        });

        await flushCallStack(); // Child forms are rendered asynchronously

        const [childInput, deepChildInput] = formWrapper.findAll('input').wrappers as Wrapper<Vue, HTMLInputElement>[];
        const [newChildValue, newDeepChildValue] = [faker.lorem.word(11), faker.lorem.word(11)];

        childInput.element.value = newChildValue;
        await childInput.trigger('change');
        await flushCallStack(); // Wait for event to bubble up from the child form

        let latestFormData = set(cloneDeep(formData.data), 'value.childValue', newChildValue);
        expect(onChange).toHaveBeenLastCalledWith({ data: latestFormData, errors: [] });

        deepChildInput.element.value = newDeepChildValue;
        await deepChildInput.trigger('change');
        await flushCallStack(); // Wait for event to bubble up from the child form

        latestFormData = set(cloneDeep(latestFormData), 'deep.child.deepChildValue', newDeepChildValue);
        expect(onChange).toHaveBeenLastCalledWith({ data: latestFormData, errors: [] });

        childInput.element.value = faker.lorem.word(9);
        await childInput.trigger('change');
        await flushCallStack(); // Wait for event to bubble up from the child form

        let [{ errors }] = onChange.mock.lastCall;
        expect(errors.length).toBe(1);
        expect(errors[0]).toEqual(expect.objectContaining({ instancePath: '/value/childValue', keyword: 'minLength' }));

        deepChildInput.element.value = faker.lorem.word(9);
        await deepChildInput.trigger('change');
        await flushCallStack(); // Wait for event to bubble up from the child form

        [{ errors }] = onChange.mock.lastCall;
        expect(errors.length).toBe(1);
        expect(errors[0]).toEqual(
            expect.objectContaining({ instancePath: '/deep/child/deepChildValue', keyword: 'minLength' })
        );
    });

    it('should persist data changes from its parent', async () => {
        const formData = {
            data: {},
            schema: {
                type: 'object',
                properties: {
                    value: { type: 'string' },
                    deep: {
                        type: 'object',
                        properties: {
                            deepValue: { type: 'string' },
                        },
                    },
                },
            },
            uiSchema: {
                type: 'VerticalLayout',
                elements: [
                    {
                        type: 'Control',
                        scope: '#/properties/value',
                    },
                    {
                        type: 'Control',
                        scope: '#/properties/deep/properties/deepValue',
                    },
                ],
            },
        } as const;

        const formWrapper = createJsonFormsTest(formData);

        const [input, deepInput] = formWrapper.findAll('input').wrappers as Wrapper<Vue, HTMLInputElement>[];
        const [newValue, newDeepValue] = faker.lorem.sentences(2, '<br>').split('<br>');

        expect(input.element.value).toBe('');
        expect(deepInput.element.value).toBe('');

        await formWrapper.setProps({
            data: { value: newValue },
        });

        expect(input.element.value).toBe(newValue);
        expect(deepInput.element.value).toBe('');

        await formWrapper.setProps({
            data: {
                value: newValue,
                deep: { deepValue: newDeepValue },
            },
        });

        expect(input.element.value).toBe(newValue);
        expect(deepInput.element.value).toBe(newDeepValue);
    });
});

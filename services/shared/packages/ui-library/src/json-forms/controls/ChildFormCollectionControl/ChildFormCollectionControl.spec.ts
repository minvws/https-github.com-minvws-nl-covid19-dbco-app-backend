import { faker } from '@faker-js/faker';
import type { Mock } from 'vitest';
import { inject, ref } from 'vue';
import { flushCallStack } from '../../../test/flush-callstack';
import { createJsonFormsControlTest } from '../../test';
import type { ChildFormCollectionJsonSchema } from '../../types';
import ChildFormCollectionControl from './ChildFormCollectionControl.vue';
import AddChildForm from './AddChildForm.vue';
import JsonFormsChild from '../../core/JsonFormsChild/JsonFormsChild.vue';
import { createMockedEventBus, createMockFormActionHandler } from '../../../test/mocks';

type TestConfig = {
    canCreate?: boolean;
};

const mockEventBus = createMockedEventBus();
const mockFormActionHandler = createMockFormActionHandler();

vi.mock('../../composition', async () => {
    const composition = await vi.importActual<typeof import('../../composition')>('../../composition');
    return { ...composition, useErrors: () => ref([]) };
});

async function createComponent({ canCreate }: TestConfig = {}) {
    const formWrapper = createJsonFormsControlTest({
        control: ChildFormCollectionControl,
        useFilteredControls: false,
        data: {
            value: {
                items: [
                    {
                        childValue: '',
                        $links: {
                            update: {
                                href: '/update-child',
                            },
                        },
                    },
                ],
                $links: {
                    update: {
                        href: '/update',
                    },
                    create: canCreate
                        ? {
                              href: '/create',
                          }
                        : undefined,
                },
            },
        },
        schema: {
            type: 'object',
            properties: {
                value: {
                    type: 'object',
                    properties: {
                        items: {
                            type: 'array',
                            items: {
                                type: 'object',
                                properties: {
                                    childValue: { type: 'string' },
                                },
                            },
                        },
                    },
                } as ChildFormCollectionJsonSchema,
            },
        },
        uiSchema: {
            type: 'Control',
            scope: '#/properties/value',
            customRenderer: 'ChildFormCollection',
            options: {
                detail: {
                    type: 'Control',
                    scope: '#/properties/childValue',
                },
            },
        },
    });

    await flushCallStack(); // child form component is loaded asynchronously

    return formWrapper;
}

describe('ChildFormCollectionControl.vue', () => {
    beforeAll(() => {
        (inject as Mock).mockImplementation((key: symbol | string) => {
            switch (key.toString()) {
                case 'Symbol(form-action-handler)':
                    return mockFormActionHandler;
                case 'Symbol(event-bus)':
                    return mockEventBus;
            }
            return undefined;
        });
    });

    beforeEach(() => {
        mockEventBus.mockClear();
        mockFormActionHandler.mockClear();
    });

    it('should handle changes via the injected event bus', async () => {
        const formWrapper = await createComponent();
        const input = formWrapper.find<HTMLInputElement>('input[type="text"]');

        const newValue = faker.lorem.sentence();
        input.element.value = newValue;
        await input.trigger('change');
        await flushCallStack();

        expect(mockEventBus.$emit).toHaveBeenCalledWith('childFormChange', {
            path: 'value',
            data: {
                $links: {
                    create: undefined,
                    update: {
                        href: '/update',
                    },
                },
                items: [
                    {
                        childValue: newValue,
                        $links: {
                            update: {
                                href: '/update-child',
                            },
                        },
                    },
                ],
            },
            errors: [],
        });
    });

    it('should allow you to create new items using the create endpoint', async () => {
        mockFormActionHandler.create.mockImplementation((config, data) => {
            return Promise.resolve({
                ...data,
                $links: {
                    update: {
                        href: '/update-new-child',
                    },
                },
            });
        });

        const formWrapper = await createComponent({ canCreate: true });

        const addChildForm = formWrapper.findComponent(AddChildForm);
        const addChildFormInput = addChildForm.find<HTMLInputElement>('input[type="text"]');
        const addChildFormSubmit = addChildForm.find<HTMLInputElement>('button[type="submit"]');

        const newChildValue = faker.lorem.sentence();
        addChildFormInput.element.value = newChildValue;
        await addChildFormInput.trigger('change');
        await flushCallStack();

        await addChildFormSubmit.trigger('click');

        expect(mockFormActionHandler.create).toHaveBeenCalledWith(
            {
                href: '/create',
            },
            {
                childValue: newChildValue,
            }
        );

        await flushCallStack();

        expect(mockEventBus.$emit).toHaveBeenCalledWith('childFormChange', {
            path: 'value',
            data: {
                $links: {
                    create: {
                        href: '/create',
                    },
                    update: {
                        href: '/update',
                    },
                },
                items: [
                    {
                        childValue: '',
                        $links: {
                            update: {
                                href: '/update-child',
                            },
                        },
                    },
                    {
                        childValue: newChildValue,
                        $links: {
                            update: {
                                href: '/update-new-child',
                            },
                        },
                    },
                ],
            },
            errors: [],
        });
    });

    it('form link events are bubbled up', async () => {
        const formWrapper = await createComponent();
        const childForm = formWrapper.findComponent(JsonFormsChild);
        const formLinkEvent = {
            href: faker.lorem.word(),
        };

        await childForm.vm.$emit('formLink', formLinkEvent);

        expect(mockEventBus.$emit).toHaveBeenCalledWith('formLink', formLinkEvent);
    });
});

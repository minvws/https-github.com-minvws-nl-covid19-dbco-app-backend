import { createJsonFormsControlTest } from '../../test';
import ChildFormControl from './ChildFormControl.vue';
import { inject, ref } from 'vue';
import type { Mock } from 'vitest';
import { faker } from '@faker-js/faker';
import { flushCallStack } from '../../../test';
import { createMockedEventBus, createMockFormActionHandler } from '../../../test/mocks';

const mockEventBus = createMockedEventBus();
const mockFormActionHandler = createMockFormActionHandler();

vi.mock('../../composition', async () => {
    const composition = await vi.importActual<typeof import('../../composition')>('../../composition');
    return { ...composition, useErrors: () => ref([]) };
});

function createComponent() {
    return createJsonFormsControlTest({
        control: ChildFormControl,
        useFilteredControls: false,
        data: {
            value: {
                childValue: '',
                $links: {
                    update: {
                        href: '/',
                    },
                },
            },
        },
        schema: {
            type: 'object',
            properties: {
                value: {
                    type: 'object',
                    properties: {
                        childValue: { type: 'string' },
                    },
                },
            },
        },
        uiSchema: {
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
    });
}

describe('ChildFormControl.vue', () => {
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

    it('should handle change via the injected event bus', async () => {
        const formWrapper = createComponent();
        await flushCallStack(); // child form component is loaded asynchronously
        const input = formWrapper.find<HTMLInputElement>('input[type="text"]');

        const newValue = faker.lorem.sentence();
        input.element.value = newValue;
        await input.trigger('change');
        await flushCallStack();

        expect(mockEventBus.$emit).toHaveBeenCalledWith('childFormChange', {
            path: 'value',
            data: {
                $links: {
                    update: {
                        href: '/',
                    },
                },
                childValue: newValue,
            },
            errors: [],
        });
    });
});

import { faker } from '@faker-js/faker';
import { createJsonFormsControlTest } from '../../test';
import FormLinkControl from './FormLinkControl.vue';
import type { Mock } from 'vitest';
import { inject } from 'vue';
import { createMockedEventBus } from '../../../test/mocks';

type TestConfig = {
    label?: string;
    scope?: string;
};

function createComponent({ label, scope }: TestConfig = {}) {
    return createJsonFormsControlTest({
        control: FormLinkControl,
        data: {
            $forms: {
                foo: 'bar',
            },
        },
        schema: {},
        uiSchema: {
            type: 'Control',
            customRenderer: 'FormLink',
            scope: scope || '#/properties/$forms/properties/foo',
            label: label || faker.lorem.sentence(),
        },
    });
}

const mockEventBus = createMockedEventBus();

describe('FormLinkControl.vue', () => {
    beforeAll(() => {
        (inject as Mock).mockImplementation((key: symbol | string) => {
            switch (key.toString()) {
                case 'Symbol(eventBus)':
                    return mockEventBus;
            }
            return undefined;
        });
    });

    beforeEach(() => {
        mockEventBus.mockClear();
    });

    it('should render a button with a given label', () => {
        const label = faker.lorem.sentence();
        const formWrapper = createComponent({ label });
        const button = formWrapper.find<HTMLElement>('button');
        expect(button.text()).toBe(label);
    });

    it.only('should be disabled if the scope is invalid', () => {
        const formWrapper = createComponent({ scope: '#/invalid' });
        const button = formWrapper.find<HTMLElement>('button');
        expect(button.attributes('disabled')).toBe('disabled');
    });

    it('should emit a form link event when clicked', async () => {
        const formWrapper = createComponent();
        const button = formWrapper.find<HTMLElement>('button');
        await button.trigger('click');

        expect(mockEventBus.$emit).toHaveBeenCalledWith('formLink', {
            href: 'bar',
        });
    });
});

import { faker } from '@faker-js/faker';
import { createJsonFormsControlTest } from '../../test';
import type { UiSchemaOptions } from '../../types';
import { decorateWrappers } from '../../../test';
import ArrayControl from './ArrayControl.vue';

type TestConfig = {
    label?: string;
    value?: string[];
    uiOptions?: UiSchemaOptions['array'];
};

function createComponent({ label, value, uiOptions }: TestConfig) {
    return createJsonFormsControlTest({
        control: ArrayControl,
        useFilteredControls: false,
        data: { value: value || [] },
        schema: {
            type: 'object',
            properties: {
                value: { type: 'array', items: { type: 'string' } },
            },
        },
        uiSchema: {
            type: 'Control',
            scope: '#/properties/value',
            label: label || faker.lorem.sentence(),
            options: uiOptions,
        },
    });
}

describe('ArrayControl.vue', () => {
    it('should render a label and inputs for the value', () => {
        const label = faker.lorem.sentence();
        const value = [faker.lorem.sentence()];
        const formWrapper = createComponent({ label, value });

        const arrayControlElement = formWrapper.find('fieldset');
        const labelElement = arrayControlElement.find<HTMLLabelElement>('legend');
        const inputElement = arrayControlElement.find<HTMLInputElement>('input');

        expect(labelElement.text()).toBe(label);
        expect(inputElement.element.value).toBe(value[0]);
    });

    it('should render a message if there is no data', () => {
        const noDataLabel = faker.lorem.sentence();
        const formWrapper = createComponent({ value: [], uiOptions: { noDataLabel } });

        const arrayControlElement = formWrapper.find<HTMLLabelElement>('fieldset');
        expect(arrayControlElement.text()).toContain(noDataLabel);
    });

    it('should add a new item on add item click', async () => {
        const addLabel = faker.lorem.sentence();
        const formWrapper = createComponent({ value: [], uiOptions: { addLabel } });
        const arrayControlElement = formWrapper.find('fieldset');
        let inputElements = arrayControlElement.findAll('input');
        expect(inputElements.length).toBe(0);

        const addItemButton = formWrapper.findByText('button', addLabel);
        await addItemButton.trigger('click');

        inputElements = arrayControlElement.findAll('input');
        expect(inputElements.length).toBe(1);
    });

    it('should only render a trash button for items by default', async () => {
        const formWrapper = createComponent({ value: [faker.lorem.sentence()], uiOptions: {} });
        const arrayControlElement = formWrapper.find('fieldset');

        let inputElements = arrayControlElement.findAll('input');
        expect(inputElements.length).toBe(1);

        const itemMoveUpButton = formWrapper.findByAriaLabel('move up');
        const itemMoveDownButton = formWrapper.findByAriaLabel('move down');
        const itemDeleteButton = formWrapper.findByAriaLabel('delete');

        expect(itemMoveUpButton.exists()).toBe(false);
        expect(itemMoveDownButton.exists()).toBe(false);
        expect(itemDeleteButton.exists()).toBe(true);

        await itemDeleteButton.trigger('click');

        inputElements = arrayControlElement.findAll('input');
        expect(inputElements.length).toBe(0);
    });

    it('should also render sorting buttons when enabled via the ui options', async () => {
        const formWrapper = createComponent({ value: [faker.lorem.sentence()], uiOptions: { showSortButtons: true } });

        const itemMoveUpButton = formWrapper.findByAriaLabel('move up');
        const itemMoveDownButton = formWrapper.findByAriaLabel('move down');
        const itemDeleteButton = formWrapper.findByAriaLabel('delete');

        expect(itemMoveUpButton.exists()).toBe(true);
        expect(itemMoveDownButton.exists()).toBe(true);
        expect(itemDeleteButton.exists()).toBe(true);
    });

    it('should move items up and down', async () => {
        const value1 = faker.lorem.sentence();
        const value2 = faker.lorem.sentence();
        const value3 = faker.lorem.sentence();
        const formWrapper = createComponent({ value: [value1, value2, value3], uiOptions: { showSortButtons: true } });

        let items = formWrapper.findAllByTestId('array-control-item');
        let itemsWrappers = decorateWrappers(items.wrappers);
        let itemValues = itemsWrappers
            .map((item) => item.find<HTMLInputElement>('input'))
            .map((input) => input.element.value);

        expect(itemValues).toEqual([value1, value2, value3]);

        expect(itemsWrappers[0].findByAriaLabel('move up').attributes('disabled')).toBeDefined();
        expect(itemsWrappers[2].findByAriaLabel('move down').attributes('disabled')).toBeDefined();

        await itemsWrappers[2].findByAriaLabel('move up').trigger('click');
        await itemsWrappers[0].findByAriaLabel('move down').trigger('click');

        items = formWrapper.findAllByTestId('array-control-item');
        itemsWrappers = decorateWrappers(items.wrappers);
        itemValues = itemsWrappers
            .map((item) => item.find<HTMLInputElement>('input'))
            .map((input) => input.element.value);

        expect(itemValues).toEqual([value3, value1, value2]);
    });
});

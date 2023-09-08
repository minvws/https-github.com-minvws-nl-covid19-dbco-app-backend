import { mount } from '@vue/test-utils';

import FormRepeatableGroup from './FormRepeatableGroup.vue';
import { faker } from '@faker-js/faker';
import { setupTest } from '@/utils/test';
import type { VueConstructor } from 'vue';

// Tell Jest to mock all timeout functions
vi.useFakeTimers();

const createComponent = setupTest((localVue: VueConstructor, props?: object) => {
    return mount(FormRepeatableGroup, {
        localVue,
        propsData: props,
    });
});

describe('FormRepeatableGroup.vue', () => {
    let propsGenerated: Partial<{
        caseUuid: string;
        disabled: boolean;
        context: object;
        schema: object;
        childrenSchema: [];
    }>;
    beforeEach(() => {
        propsGenerated = {
            caseUuid: faker.string.uuid(),
            context: {
                minimum: 1,
                model: [
                    {
                        value: 'val',
                    },
                    {
                        value: 'val',
                    },
                ],
                classes: {
                    groupRepeatableRemove: '',
                },
                name: 'thisisaname',
            },
            schema: {},
            childrenSchema: [],
            disabled: true,
        };
    });

    it('the add more and remove button should be disabled of the disabled property is set to true', () => {
        const wrapper = createComponent(propsGenerated);
        const addButton = wrapper.find('[data-testid=add-button');
        const removeButton = wrapper.find('[data-testid=remove-button]');

        expect(addButton.attributes().disabled).toBe('disabled');
        expect(removeButton.attributes().disabled).toBe('disabled');
    });

    it('the add more and remove button should not be disabled of the disabled property is set to false', () => {
        propsGenerated.disabled = false;

        const wrapper = createComponent(propsGenerated);
        const addButton = wrapper.find('[data-testid=add-button');
        const removeButton = wrapper.find('[data-testid=remove-button]');

        expect(addButton.attributes().disabled).toBe(undefined);
        expect(removeButton.attributes().disabled).toBe(undefined);
    });

    it('should hide the remove button when number of items is equal or below context.minimum', () => {
        propsGenerated.context = {
            minimum: 1,
            model: [
                {
                    value: 'val',
                },
            ],
            classes: {
                groupRepeatableRemove: '',
            },
            name: 'thisisaname',
        };
        const wrapper = createComponent(propsGenerated);

        const removeButton = wrapper.find('[data-testid=remove-button]');
        expect(removeButton.attributes('data-disabled')).toBe('true');
    });

    it('should show the remove button when number of items is higher than context.minimum', () => {
        propsGenerated.context = {
            minimum: 1,
            model: [
                {
                    value: 'val',
                },
                {
                    value: 'val',
                },
            ],
            classes: {
                groupRepeatableRemove: '',
            },
            name: 'thisisaname',
        };
        propsGenerated.disabled = false;

        const wrapper = createComponent(propsGenerated);

        const removeButton = wrapper.find('[data-testid=remove-button]');
        expect(removeButton.attributes('data-disabled')).toBe(undefined);
    });

    it('should call submit fn with added empty value when add-button is clicked', async () => {
        propsGenerated.context = {
            minimum: 1,
            model: [
                {
                    value: 'val',
                },
                {
                    value: 'val',
                },
            ],
            classes: {
                groupRepeatableRemove: '',
            },
            name: 'thisisaname',
        };
        propsGenerated.disabled = false;

        const spyUpdateModel = vi.spyOn((FormRepeatableGroup as any).methods, 'updateModel');

        const wrapper = createComponent(propsGenerated);

        const addButton = wrapper.find('[data-testid="add-button"]');

        await addButton.trigger('click');

        // This field has a ~DEBOUNCE~ of 300ms, so fast-forward time
        vi.runAllTimers();

        expect(spyUpdateModel).toBeCalled();
        expect(wrapper.vm.context.model.length).toEqual(3);
    });

    it('should remove value from model remove-button is clicked', async () => {
        propsGenerated.context = {
            minimum: 1,
            model: [
                {
                    value: 'val',
                },
                {
                    value: 'val',
                },
            ],
            classes: {
                groupRepeatableRemove: '',
            },
            name: 'thisisaname',
        };
        propsGenerated.disabled = false;

        const spyUpdateModel = vi.spyOn((FormRepeatableGroup as any).methods, 'updateModel');

        const wrapper = createComponent(propsGenerated);

        const removeButton = wrapper.find('[data-testid="remove-button"]');
        await removeButton.trigger('click');

        // This field has a ~DEBOUNCE~ of 300ms, so fast-forward time
        vi.runAllTimers();

        expect(spyUpdateModel).toBeCalled();
        expect(wrapper.vm.context.model.length).toEqual(1);
    });

    it('should add CSS class "repeatable-group--one" when attribute limit = 1', () => {
        propsGenerated.context = {
            minimum: 1,
            limit: 1,
            model: [
                {
                    value: 'val',
                },
                {
                    value: 'val',
                },
            ],
            classes: {
                groupRepeatableRemove: '',
            },
            name: 'thisisaname',
        };
        propsGenerated.disabled = false;

        const wrapper = createComponent(propsGenerated);

        const inputRepeatableGroupEl = wrapper.find('.repeatable-group');
        expect(inputRepeatableGroupEl.classes()).toContain('repeatable-group--one');
    });

    it('should not add CSS class "repeatable-group--one" when attribute limit is not set', () => {
        propsGenerated.context = {
            minimum: 1,
            model: [
                {
                    value: 'val',
                },
                {
                    value: 'val',
                },
            ],
            classes: {
                groupRepeatableRemove: '',
            },
            name: 'thisisaname',
        };
        propsGenerated.disabled = false;

        const wrapper = createComponent(propsGenerated);

        const inputRepeatableGroupEl = wrapper.find('.repeatable-group');
        expect(inputRepeatableGroupEl.classes()).not.toContain('repeatable-group--one');
    });

    it('should not add CSS class "repeatable-group--one" when attribute limit = 100', () => {
        propsGenerated.context = {
            minimum: 1,
            model: [
                {
                    value: 'val',
                },
                {
                    value: 'val',
                },
            ],
            classes: {
                groupRepeatableRemove: '',
            },
            name: 'thisisaname',
        };
        propsGenerated.disabled = false;

        const wrapper = createComponent(propsGenerated);

        const inputRepeatableGroupEl = wrapper.find('.repeatable-group');
        expect(inputRepeatableGroupEl.classes()).not.toContain('repeatable-group--one');
    });
});

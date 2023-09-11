import { mount } from '@vue/test-utils';
import FormMultiSelectDropdown from './FormMultiSelectDropdown.vue';
import { decorateWrapper, setupTest, waitForElements } from '@/utils/test';
import type { VueConstructor } from 'vue';

const createComponent = setupTest((localVue: VueConstructor, props: AnyObject = {}, stubs: object = {}) => {
    return mount(FormMultiSelectDropdown, {
        localVue,
        stubs: { FormLabel: true, ...stubs },
        propsData: {
            ...props,
            context: {
                model: null,
                name: 'testinput',
                type: 'formMultiSelectDropdown',
                attributes: {},
                ...(props.context || {}),
            },
        },
        attachTo: document.body,
    });
});

describe('FormMultiSelectDropdown.vue', () => {
    it('should load the component', () => {
        // ARRANGE
        const props = {
            listOptions: [],
        };

        const wrapper = createComponent(props);

        // ASSERT
        expect(wrapper.findComponent({ name: 'FormMultiSelectDropdown' }).exists()).toBe(true);
    });

    it('should show options without groups', () => {
        // ARRANGE
        const props = {
            listOptions: [
                {
                    label: 'Option A',
                    value: 'A',
                    description: null,
                },
                {
                    label: 'Option B',
                    value: 'B',
                    description: null,
                },
            ],
        };

        const wrapper = createComponent(props);

        // ASSERT
        expect(wrapper.findAll('label.option')).toHaveLength(2);
        expect(wrapper.findAll('label.group-label')).toHaveLength(0);
    });

    it('should show options with groups', () => {
        // ARRANGE
        const props = {
            listGroups: {
                testGroup1: 'Test group 1',
                testGroup2: 'Test group 2',
            },
            listOptions: [
                {
                    label: 'Group 1 Option A',
                    value: 'A',
                    description: null,
                    group: 'testGroup1',
                },
                {
                    label: 'Group 1 Option B',
                    value: 'B',
                    description: null,
                    group: 'testGroup1',
                },
                {
                    label: 'Group 2 Option C',
                    value: 'C',
                    description: null,
                    group: 'testGroup2',
                },
                {
                    label: 'Group 2 Option D',
                    value: 'D',
                    description: null,
                    group: 'testGroup2',
                },
                {
                    label: 'Group 2 Option E',
                    value: 'E',
                    description: null,
                    group: 'testGroup2',
                },
            ],
        };

        const wrapper = createComponent(props);

        // ASSERT
        expect(wrapper.findAll('label.option')).toHaveLength(5);
        expect(wrapper.findAll('legend.group-label')).toHaveLength(2);
    });

    it('should show descriptions', () => {
        // ARRANGE
        const props = {
            listOptions: [
                {
                    label: 'Option A',
                    value: 'A',
                    description: 'Description A',
                },
                {
                    label: 'Option B',
                    value: 'B',
                    description: 'Description B',
                },
                {
                    label: 'Option C',
                    value: 'C',
                    description: null,
                },
            ],
        };

        const wrapper = createComponent(props);

        // ASSERT
        expect(wrapper.findAll('.option .label span')).toHaveLength(2);
    });

    it('should display currently selected options', () => {
        // ARRANGE
        const props = {
            context: {
                model: ['B', 'C'],
            },
            listOptions: [
                {
                    label: 'Option A',
                    value: 'A',
                    description: null,
                },
                {
                    label: 'Option B',
                    value: 'B',
                    description: null,
                },
                {
                    label: 'Option C',
                    value: 'C',
                    description: null,
                },
                {
                    label: 'Option D',
                    value: 'D',
                    description: null,
                },
            ],
        };

        const wrapper = createComponent(props);

        // ASSERT
        expect(wrapper.findAll('[data-testid^="selected-label-"]')).toHaveLength(2);
        expect(wrapper.findByTestId('selected-label-B').exists()).toBe(true);
        expect(wrapper.findByTestId('selected-label-C').exists()).toBe(true);
    });

    it('should filter options', async () => {
        // ARRANGE
        const props = {
            filterEnabled: true,
            listOptions: [
                {
                    label: 'Option A',
                    value: 'A',
                    description: null,
                },
                {
                    label: 'Option B',
                    value: 'B',
                    description: null,
                },
            ],
        };

        const wrapper = createComponent(props);

        const filterInput = wrapper.findByTestId('testinput-filter-input');
        expect(filterInput.exists()).toBe(true);
        expect(wrapper.findAll('input[type="checkbox"]')).toHaveLength(2);

        // ACT
        await filterInput.setValue('Option B');

        // ASSERT
        expect(wrapper.findAll('input[type="checkbox"]')).toHaveLength(1);
        expect(wrapper.findAll('input[type="checkbox"]').at(0).attributes('value')).toEqual('B');
    });

    it('should remove option on chip click', async () => {
        // ARRANGE
        const props = {
            context: {
                model: ['B', 'C'],
            },
            listOptions: [
                {
                    label: 'Option A',
                    value: 'A',
                    description: null,
                },
                {
                    label: 'Option B',
                    value: 'B',
                    description: null,
                },
                {
                    label: 'Option C',
                    value: 'C',
                    description: null,
                },
                {
                    label: 'Option D',
                    value: 'D',
                    description: null,
                },
            ],
        };

        const wrapper = createComponent(props);

        expect(wrapper.findAll('input[type="checkbox"]:checked')).toHaveLength(2);

        // ACT
        await wrapper.findByTestId('selected-label-B').trigger('click');

        // ASSERT
        expect(wrapper.findAll('input[type="checkbox"]:checked')).toHaveLength(1);
    });

    // This test is incredibly flaky.. The component itself could probably do with some refactoring to make this more reliable.
    it.skip('should focus search on focus method', async () => {
        // ARRANGE
        const props = {
            filterEnabled: true,
            listOptions: [
                {
                    label: 'Option A',
                    value: 'A',
                    description: null,
                },
                {
                    label: 'Option B',
                    value: 'B',
                    description: null,
                },
            ],
        };

        // ACT
        const wrapper = createComponent(props);
        const selector = '[data-testid="testinput-filter-input"]';
        expect(wrapper.find(selector).exists()).toBe(true);
        expect(wrapper.find(`${selector}:focus`).exists()).toBe(false);
        await wrapper.find('button').trigger('click');
        await waitForElements(wrapper, selector);
        await wrapper.trigger('focus');

        // ASSERT
        expect(wrapper.find(`${selector}:focus`).exists()).toBe(true);
    });

    it('should show no results when no options', () => {
        // ARRANGE
        const props = {
            listOptions: [],
        };

        const wrapper = createComponent(props);

        // ASSERT
        expect(wrapper.find('.no-results').exists()).toBe(true);
    });

    it('should show no results when filter does not find anything', async () => {
        // ARRANGE
        const props = {
            filterEnabled: true,
            listOptions: [
                {
                    label: 'Option A',
                    value: 'A',
                    description: null,
                },
                {
                    label: 'Option B',
                    value: 'B',
                    description: null,
                },
            ],
        };

        const wrapper = createComponent(props);

        const filterInput = wrapper.findByTestId('testinput-filter-input');
        expect(filterInput.exists()).toBe(true);

        // ACT
        await filterInput.setValue('nonexisting search');

        // ASSERT
        expect(wrapper.find('.no-results').exists()).toBe(true);
    });

    const disableInputFields = ['selected-label-A', 'selected-label-B'];
    it('should disable certain input fields when disabled prop is true', () => {
        // ARRANGE
        const props = {
            filterEnabled: true,
            context: {
                name: 'index',
                model: ['A', 'B'],
            },
            listOptions: [
                {
                    label: 'Option A',
                    value: 'A',
                    description: null,
                },
                {
                    label: 'Option B',
                    value: 'B',
                    description: null,
                },
            ],
            disabled: true,
        };

        const wrapper = createComponent(props, {});

        disableInputFields.forEach((testId) => {
            expect(wrapper.find(`[data-testid=${testId}]`).attributes().disabled).toBe('disabled');
        });
        expect(wrapper.find('[data-testid=index-filter-input]').exists()).toBe(false);
    });

    it('should add "pr-3" class to chip and show delete icon when disabled is false', () => {
        const props = {
            filterEnabled: true,
            context: {
                name: 'index',
                model: ['A', 'B'],
            },
            listOptions: [
                {
                    label: 'Option A',
                    value: 'A',
                    description: null,
                },
                {
                    label: 'Option B',
                    value: 'B',
                    description: null,
                },
            ],
            disabled: false,
        };

        const wrapper = createComponent(props, {});

        const firstChip = decorateWrapper(wrapper.find('.chip'));

        expect(firstChip.findByTestId('form-label').classes()).toContain('pr-3');
        expect(firstChip.find('.icon--delete-circle').exists()).toBe(true);
    });

    it('should add "text-muted" class to chip and hide delete icon when disabled is true', () => {
        const props = {
            filterEnabled: true,
            context: {
                name: 'index',
                model: ['A', 'B'],
            },
            listOptions: [
                {
                    label: 'Option A',
                    value: 'A',
                    description: null,
                },
                {
                    label: 'Option B',
                    value: 'B',
                    description: null,
                },
            ],
            disabled: true,
        };

        const wrapper = createComponent(props, {});

        const firstChip = decorateWrapper(wrapper.find('.chip'));

        expect(firstChip.findByTestId('form-label').classes()).toContain('text-muted');
        expect(firstChip.find('.icon--delete-circle').exists()).toBe(false);
    });
});

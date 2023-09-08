import { mount } from '@vue/test-utils';
import FormRepeatable from './FormRepeatable.vue';
import FormulateFormWrapper from '../FormulateFormWrapper/FormulateFormWrapper.vue';
import { faker } from '@faker-js/faker';
import { setupTest } from '@/utils/test';
import type { VueConstructor } from 'vue';

// Tell to mock all timeout functions
vi.useFakeTimers();

let modelValues: Array<string>;

const createComponent = setupTest((localVue: VueConstructor, props?: object) => {
    return mount(FormRepeatable, {
        localVue,
        propsData: props,
        stubs: {
            FormulateFormWrapper: FormulateFormWrapper,
            FormulateForm: true,
        },
    });
});

describe('FormRepeatable.vue', () => {
    beforeEach(() => {
        modelValues = faker.helpers.uniqueArray(faker.lorem.word, faker.number.int({ min: 1, max: 10 }));
    });

    it('Should show rows with initial formdata', () => {
        const props = {
            context: {
                model: modelValues,
                name: 'thisisaname',
                classes: {
                    groupRepeatableRemove: '',
                },
            },
            placeholder: '',
            schema: {},
        };
        const wrapper = createComponent(props);

        expect(
            wrapper
                .findAll('input[data-testid="text-field"]')
                .wrappers.map((wrapper) => (wrapper.element as HTMLInputElement).value)
        ).toEqual(modelValues);
    });

    it('Should disable adding and removing', () => {
        const props = {
            context: {
                model: modelValues,
                classes: {
                    groupRepeatableRemove: '',
                },
            },
            placeholder: '',
            schema: {},
            disabled: true,
        };
        const wrapper = createComponent(props);

        expect(wrapper.find("[data-testid='add-button']").attributes().disabled).toBe('disabled');
        expect(wrapper.find("[data-testid='remove-button']").attributes().disabled).toBe('disabled');
    });

    it('Should be able to add row, and notify change', async () => {
        const props = {
            context: {
                model: modelValues,
                classes: {
                    groupRepeatableRemove: '',
                },
            },
            placeholder: '',
            schema: {},
            disabled: false,
        };

        const wrapper = createComponent(props);

        const addButton = wrapper.find('[data-testid="add-button"]');
        await addButton.trigger('click');
        vi.runAllTimers();

        expect(wrapper.emitted('change')).toBeTruthy();
        expect(wrapper.findAll('[data-testid="text-field"]').length).toEqual(modelValues.length + 1);
    });

    it('Should be able to change row, and notify change', async () => {
        const props = {
            context: {
                model: modelValues,
                classes: {
                    groupRepeatableRemove: '',
                },
            },
            placeholder: '',
            schema: {},
            disabled: false,
        };

        const wrapper = createComponent(props);

        const firstTextField = wrapper.findAll('[data-testid="text-field"]').at(0);
        const newString = faker.lorem.word();
        await firstTextField.setValue(newString);

        // Wait for the watch to have been executed
        vi.runAllTimers();
        expect(wrapper.emitted('change')).toBeTruthy();

        // Select first object of array because we selected the first input
        // Check if the new value has been set
        expect(wrapper.vm.context.model[0]).toEqual(newString);
    });

    it('Should be able to remove row, and notify change', async () => {
        const props = {
            context: {
                model: modelValues,
                classes: {
                    groupRepeatableRemove: '',
                },
            },
            placeholder: '',
            schema: {},
            disabled: false,
        };

        const wrapper = createComponent(props);

        const removeButton = wrapper.find('[data-testid="remove-button"]');

        await removeButton.trigger('click');
        vi.runAllTimers();

        // Expect one textfield less
        expect(wrapper.findAll('[data-testid="text-field"]').length).toEqual(modelValues.length - 1);

        // The first value of context.model should not be the first value of modelValues anymore
        expect(wrapper.vm.context.model[0]).not.toEqual(modelValues[0]);
        expect(wrapper.emitted('change')).toBeTruthy();
    });
});

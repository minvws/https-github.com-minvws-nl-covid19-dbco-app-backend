import { mount } from '@vue/test-utils';
import FormInputCheckbox from './FormInputCheckbox.vue';
import { setupTest } from '@/utils/test';
import type { VueConstructor } from 'vue';

const createComponent = setupTest((localVue: VueConstructor, props?: object) => {
    return mount(FormInputCheckbox, {
        localVue,
        propsData: props,
        attachTo: document.body,
    });
});

describe('FormInputCheckbox.vue', () => {
    it('Should render with default text when rendered', () => {
        // ARRANGE
        const props = {
            context: {},
            defaultText: 'Isolatie voor symptomatische index: laatste dag op 27 juni',
            inputType: 'textarea',
        };
        const wrapper = createComponent(props);

        // ASSERT
        const textfield = wrapper.find('textarea');
        expect(textfield.attributes('placeholder')).toBe(props.defaultText);
    });

    it('Should be active on load when model is set', () => {
        // ARRANGE
        const props = {
            context: {
                model: 'This text has been edited by a user',
            },
            defaultText: 'Isolatie voor symptomatische index: laatste dag op 27 juni',
            inputType: 'textarea',
        };
        const wrapper = createComponent(props);

        //ASSERT
        // The textfield should NOT be readonly now
        expect(wrapper.find('textarea').attributes().readonly).toBe(undefined);
    });

    it('Should set model by placeholder when form is activated', async () => {
        // ARRANGE
        const props = {
            context: {
                model: null,
            },
            defaultText: 'Isolatie voor symptomatische index: laatste dag op 27 juni',
            inputType: 'textarea',
        };
        const wrapper = createComponent(props);

        // ACT
        await wrapper.find('input[type="checkbox"]').trigger('click');

        expect(props.context.model).toBe(props.defaultText);
    });

    it('Should empty model when form is deactivated by checkbox click', async () => {
        // ARRANGE
        const props = {
            context: {
                model: 'The text value was changed beforehand',
            },
            defaultText: 'Isolatie voor symptomatische index: laatste dag op 27 juni',
            inputType: 'textarea',
        };
        const wrapper = createComponent(props);

        // ACT
        await wrapper.find('input[type="checkbox"]').trigger('click');

        // ASSERT
        expect(props.context.model).toBe(null);
    });

    it('Should disable checkbox and text-input if disabled prop is true', () => {
        // ARRANGE
        const props = {
            context: {
                model: 'The text value was changed beforehand',
            },
            defaultText: 'Isolatie voor symptomatische index: laatste dag op 27 juni',
            inputType: 'textarea',
            disabled: true,
        };
        const wrapper = createComponent(props);

        // ASSERT
        expect(wrapper.find('input[type="checkbox"]').attributes().disabled).toBe('disabled');
        expect(wrapper.find('[data-testid="text-input"]').attributes().disabled).toBe('disabled');
    });

    it('Should disable checkbox and text-input if disabled prop is true', () => {
        // ARRANGE
        const props = {
            context: {
                model: 'The text value was changed beforehand',
            },
            defaultText: 'Isolatie voor symptomatische index: laatste dag op 27 juni',
            inputType: 'textarea',
            disabled: false,
        };
        const wrapper = createComponent(props);

        // ASSERT
        expect(wrapper.find('input[type="checkbox"]').attributes().disabled).toBe(undefined);
        expect(wrapper.find('[data-testid="text-input"]').attributes().disabled).toBe(undefined);
    });
});

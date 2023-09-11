import { shallowMount } from '@vue/test-utils';
import FormEditableInput from './FormEditableInput.vue';
import { setupTest } from '@/utils/test';
import type { VueConstructor } from 'vue';

const createComponent = setupTest((localVue: VueConstructor, props?: object) => {
    return shallowMount(FormEditableInput, {
        localVue,
        propsData: props,
    });
});

describe('FormEditableInput.vue', () => {
    it('should display a spinner if data.loading is true', () => {
        // ARRANGE
        const props = {
            context: {}, //NOTE: context is not used in the component
            data: { loading: true, disabled: false, editable: true },
        };
        const wrapper = createComponent(props);

        // ASSERT
        expect(wrapper.find('BSpinner-stub').exists()).toBe(true);
        expect(wrapper.find('img').exists()).toBe(false);
    });

    it('should not display a spinner if data.loading is undefined', () => {
        // ARRANGE
        const props = {
            context: {}, //NOTE: context is not used in the component
            data: { loading: undefined, disabled: false, editable: true },
        };
        const wrapper = createComponent(props);

        // ASSERT
        expect(wrapper.find('[spinner]').exists()).toBe(false);
        expect(wrapper.find('img').exists()).toBe(false);
    });

    it('should not display a spinner if data.loading is false', () => {
        // ARRANGE
        const props = {
            context: {}, //NOTE: context is not used in the component
            data: { loading: false, disabled: false, editable: true },
        };
        const wrapper = createComponent(props);

        // ASSERT
        expect(wrapper.find('class[spinner]').exists()).toBe(false);
        expect(wrapper.find('img').exists()).toBe(false);
    });

    it('should display an edit icon if data.disabled is defined and data.editable is false', () => {
        // ARRANGE
        const props = {
            context: {}, //NOTE: context is not used in the component
            data: { loading: false, disabled: false, editable: false },
        };
        const wrapper = createComponent(props);

        // ASSERT
        expect(wrapper.find('class[spinner]').exists()).toBe(false);
        expect(wrapper.find('img').exists()).toBe(true);
    });

    it('should toggle data.disabled when clicking on the edit icon', async () => {
        const props = {
            context: {}, //NOTE: context is not used in the component
            data: { loading: false, disabled: false, editable: false },
        };
        const wrapper = createComponent(props);

        // ASSERT
        await wrapper.find('img').trigger('click');
        expect(wrapper.vm.$props.data.disabled).toBe(true);
    });
});

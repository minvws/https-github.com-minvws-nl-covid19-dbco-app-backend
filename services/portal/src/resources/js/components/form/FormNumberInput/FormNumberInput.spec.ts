import { mount } from '@vue/test-utils';
import FormNumberInput from './FormNumberInput.vue';

import { setupTest } from '@/utils/test';
import type { VueConstructor } from 'vue';

const createComponent = setupTest((localVue: VueConstructor, props?: object) => {
    return mount(FormNumberInput, {
        localVue,
        propsData: props,
    });
});

describe('FormNumberInput.vue', () => {
    it('should load the component', () => {
        // ARRANGE
        const props = {
            context: {
                model: 0,
            },
        };

        const wrapper = createComponent(props);

        // ASSERT
        expect(wrapper.exists()).toBe(true);
    });

    it('should change the context model on inputting normal number', async () => {
        // ARRANGE
        const props = {
            context: {
                model: 0,
            },
        };

        const wrapper = createComponent(props);

        // ACT
        await wrapper.find('input').setValue(5);

        // ASSERT
        expect(wrapper.vm.$props.context.model).toBe(5);
    });

    it('should change the context model on inputting number in string format', async () => {
        // ARRANGE
        const props = {
            context: {
                model: 0,
            },
        };

        const wrapper = createComponent(props);

        // ACT
        await wrapper.find('input').setValue('5');

        // ASSERT
        expect(wrapper.vm.$props.context.model).toBe(5);
    });

    it('should not accept a value on inputting a non number', async () => {
        // ARRANGE
        const props = {
            context: {
                model: 0,
            },
        };

        const wrapper = createComponent(props);

        // ACT
        await wrapper.find('input').setValue('aaaaa');

        // ASSERT
        expect(wrapper.vm.$props.context.model).toBe('');
    });
});

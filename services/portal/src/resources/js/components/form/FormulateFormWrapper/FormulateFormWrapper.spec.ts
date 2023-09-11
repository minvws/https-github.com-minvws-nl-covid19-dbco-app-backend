import { mount } from '@vue/test-utils';
import FormulateFormWrapper from './FormulateFormWrapper.vue';
import { setupTest } from '@/utils/test';
import type { VueConstructor } from 'vue';

const createComponent = setupTest((localVue: VueConstructor, props?: object) => {
    return mount(FormulateFormWrapper, {
        localVue,
        propsData: props,
    });
});

describe('FormulateFormWrapper.vue', () => {
    it('should load the component', () => {
        // ARRANGE
        const props = {
            value: {},
        };

        const wrapper = createComponent(props);

        // ASSERT
        expect(wrapper.exists()).toBe(true);
    });

    // the change hits the "set" for computed
    // setting the props will already trigger the getter
    it('should emit when property value changes', async () => {
        // ARRANGE
        const props = {
            value: { model: { test: 'test' } },
        };
        const wrapper = createComponent(props);

        // ACT
        await wrapper.setProps({ value: { model: { test2: 'testb' } } });

        // ASSERT
        expect(wrapper.emitted()).toMatchObject({ input: [[{ model: { test2: 'testb' } }]] });
    });

    it('should have provided the rootModel', () => {
        // ARRANGE
        const props = {
            value: { a: { b: 1, c: 2 } },
        };

        const wrapper = createComponent(props);

        // ASSERT
        expect(wrapper.vm._provided.rootModel).toEqual(expect.any(Function));
        expect(wrapper.vm._provided.rootModel()).toEqual(wrapper.vm.$props.value);
    });
});

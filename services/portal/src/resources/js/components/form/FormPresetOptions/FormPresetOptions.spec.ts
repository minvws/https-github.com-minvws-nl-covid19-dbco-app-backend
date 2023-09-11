import { setupTest } from '@/utils/test';
import { mount } from '@vue/test-utils';
import type { VueConstructor } from 'vue';
import FormPresetOptions from './FormPresetOptions.vue';

const createComponent = setupTest((localVue: VueConstructor, props?: object) => {
    return mount(FormPresetOptions, {
        localVue,
        propsData: props,
        stubs: {
            FormulateFormWrapper: true,
        },
    });
});

describe('FormPresetOptions.vue', () => {
    it('should load the component', () => {
        // ARRANGE
        const props = {
            context: {
                name: 'elementName',
            },
        };

        const wrapper = createComponent(props);

        // ASSERT
        expect(wrapper.find('formulateformwrapper-stub').exists()).toBe(true);
    });

    it('should call computed checkbox', () => {
        // ARRANGE
        const props = {
            context: {
                model: ['aaaa', 'bbbb'],
                name: 'elementName',
            },
        };

        const checkboxSpy = vi.spyOn((FormPresetOptions as any).computed, 'checkbox');
        createComponent(props);

        // ASSERT
        expect(checkboxSpy).toBeCalledTimes(1);
    });
});

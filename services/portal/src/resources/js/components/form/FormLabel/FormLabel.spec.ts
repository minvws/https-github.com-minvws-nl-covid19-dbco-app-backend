import { shallowMount } from '@vue/test-utils';
import FormLabel from './FormLabel.vue';
import { createContainer, setupTest } from '@/utils/test';
import type { VueConstructor } from 'vue';

const createComponent = setupTest((localVue: VueConstructor, props?: object) => {
    return shallowMount(FormLabel, {
        localVue,
        propsData: props,
        attachTo: createContainer(),
    });
});

describe('FormLabel.vue', () => {
    // NOTE: [BootstrapVue warn]: tooltip - The provided target is no valid HTML element.

    it('should load a label', () => {
        // ARRANGE
        const props = {
            context: {
                id: 0,
                classes: {
                    label: 'testClassesLabel',
                },
                label: 'testLabel',
            },
        };

        const wrapper = createComponent(props);

        // ASSERT
        expect(wrapper.find('label[class="testClassesLabel"]').exists()).toBe(true);
    });

    it('should load an i element if there is a description', () => {
        // ARRANGE
        const props = {
            context: {
                id: 0,
                classes: {
                    label: 'testClassesLabel',
                },
                label: 'testLabel',
            },
            description: 'testTooltip',
        };

        const wrapper = createComponent(props);

        // ASSERT
        expect(wrapper.find('i').exists()).toBe(true);
    });

    it('should not load an i element if there is no description', () => {
        // ARRANGE
        const props = {
            context: {
                id: 0,
                classes: {
                    label: 'testClassesLabel',
                },
                label: 'testLabel',
            },
        };

        const wrapper = createComponent(props);

        // ASSERT
        expect(wrapper.find('i').exists()).toBe(false);
    });
});

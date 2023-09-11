import { setupTest } from '@/utils/test';
import { shallowMount } from '@vue/test-utils';
import type { VueConstructor } from 'vue';
import FormButtonContent from './FormButtonContent.vue';

const createComponent = setupTest((localVue: VueConstructor, props?: object) => {
    return shallowMount(FormButtonContent, {
        localVue,
        propsData: props,
    });
});

describe('FormButtonContent.vue', () => {
    it('should load the component with context.label as innertext', () => {
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
        expect(wrapper.find('div[class="testClassesLabel"]').text()).toBe('testLabel');
    });
});

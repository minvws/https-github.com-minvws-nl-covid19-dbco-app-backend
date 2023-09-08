import { setupTest } from '@/utils/test';
import { shallowMount } from '@vue/test-utils';
import type { VueConstructor } from 'vue';
import FormRelationShipDropdown from './FormRelationshipDropdown.vue';

const createComponent = setupTest((localVue: VueConstructor, props?: object) => {
    return shallowMount(FormRelationShipDropdown, {
        localVue,
        propsData: props,
        stubs: {
            FormulateInput: true,
        },
    });
});

describe('FormRelationshipDropdown.vue', () => {
    it('should load the component', () => {
        // ARRANGE
        const props = {
            context: {
                attributes: {},
                model: '',
            },
        };

        const wrapper = createComponent(props);

        // ACT
        // ASSERT
        expect(wrapper.find('formulateinput-stub').exists()).toBe(true);
    });
});

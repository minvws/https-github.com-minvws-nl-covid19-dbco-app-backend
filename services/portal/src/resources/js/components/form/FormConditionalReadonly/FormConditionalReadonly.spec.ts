import * as formUtils from '@/utils/form';
import { setupTest } from '@/utils/test';
import { shallowMount } from '@vue/test-utils';
import { vi } from 'vitest';
import type { VueConstructor } from 'vue';
import { Store } from 'vuex';
import FormConditionalReadonly from './FormConditionalReadonly.vue';

const props = {
    context: {
        model: {
            test: 'test',
        },
        attributes: { name: 'testAttributes' },
    },
    inputType: 'test',
    condition: {}, // FormCondition from formTypes.ts
    readonly: {
        name: 'testName',
        placeholder: 'holderOfPlaces',
    },
};

const createComponent = setupTest((localVue: VueConstructor, props?: object) => {
    return shallowMount(FormConditionalReadonly, {
        localVue,
        propsData: props,
        store: new Store({}), // this test just needs a dummy store.
        stubs: {
            FormulateInput: true,
        },
    });
});

describe('FormConditionalReadonly.vue', () => {
    it('should return true from isCondition and display the corresponding attributes', () => {
        // ARRANGE
        vi.spyOn(formUtils, 'formConditionMet').mockReturnValue(false);

        const wrapper = createComponent(props);

        // ASSERT
        expect(wrapper.find('[name="testAttributes"]').exists()).toBe(true);
        expect(wrapper.find('[type="test"]').exists()).toBe(true);
    });
});

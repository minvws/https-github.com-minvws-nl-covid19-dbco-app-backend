import { shallowMount } from '@vue/test-utils';
import { faker } from '@faker-js/faker';
import FormErrors from './FormErrors.vue';
import { createDefaultLocalVue } from '../../test/local-vue';

type Props = {
    messages: string[];
};

function createComponent(propsData: Props) {
    return shallowMount(FormErrors, {
        localVue: createDefaultLocalVue(),
        propsData,
    });
}

describe('FormErrors.vue', () => {
    it('should be able to render with multiple error messages', () => {
        const error1 = faker.lorem.sentence();
        const error2 = faker.lorem.sentence();
        const wrapper = createComponent({ messages: [error1, error2] });
        expect(wrapper.text()).toContain(error1);
        expect(wrapper.text()).toContain(error2);
    });
});

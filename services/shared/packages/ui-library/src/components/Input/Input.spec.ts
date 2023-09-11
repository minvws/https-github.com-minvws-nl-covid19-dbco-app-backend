import { mount } from '@vue/test-utils';
import { createDefaultLocalVue } from '../../test/local-vue';
import Input from './Input.vue';

type Props = {
    type?: string;
};

function createComponent(propsData: Props) {
    return mount(Input, {
        localVue: createDefaultLocalVue(),
        propsData,
    });
}

describe('InputField.vue', () => {
    it('should render with default type text', async () => {
        const wrapper = createComponent({});
        expect(wrapper.find('input').attributes('type')).toBe('text');
    });

    it('can render with different types', async () => {
        const wrapper = createComponent({ type: 'email' });
        expect(wrapper.find('input').attributes('type')).toBe('email');
    });
});

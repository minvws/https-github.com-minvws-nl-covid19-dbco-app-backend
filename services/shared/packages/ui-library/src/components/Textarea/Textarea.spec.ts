import { mount } from '@vue/test-utils';
import { createDefaultLocalVue } from '../../test/local-vue';
import Textarea from './Textarea.vue';

type Props = {
    disabled?: boolean;
};

function createComponent(propsData: Props) {
    return mount(Textarea, {
        localVue: createDefaultLocalVue(),
        propsData,
    });
}

describe('Textarea.vue', () => {
    it('should render with default not disabled', () => {
        const wrapper = createComponent({});
        expect(wrapper.find('textarea').attributes('disabled')).toBeUndefined();
    });
    it('could render as disabled', () => {
        const wrapper = createComponent({ disabled: true });
        expect(wrapper.find('textarea').attributes('disabled')).toBe('disabled');
    });
});

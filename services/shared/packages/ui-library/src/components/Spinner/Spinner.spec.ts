import { shallowMount } from '@vue/test-utils';
import Spinner from './Spinner.vue';
import { createDefaultLocalVue } from '../../test/local-vue';

function createComponent(propsData?: object) {
    return shallowMount(Spinner, {
        localVue: createDefaultLocalVue(),
        propsData,
    });
}

describe('Spinner.vue', () => {
    it('should be able to render with default props', () => {
        const wrapper = createComponent();
        expect(wrapper.attributes('role')).toBe('progressbar');
    });

    it('should be able to render with size prop', () => {
        const wrapper = createComponent({ size: 'lg' });
        expect(wrapper.classes()).toEqual(expect.arrayContaining(['tw-w-7', 'tw-h-7']));
    });
});

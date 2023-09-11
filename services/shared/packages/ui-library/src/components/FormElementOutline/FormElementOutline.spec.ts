import { mount } from '@vue/test-utils';
import { createDefaultLocalVue } from '../../test/local-vue';
import FormElementOutline from './FormElementOutline.vue';
import { defineComponent, ref } from 'vue';

const TestComponent = defineComponent({
    components: { FormElementOutline },
    props: { invalid: Boolean, useFocusOutline: Boolean, round: Boolean, square: Boolean },
    setup(props) {
        const propRef = ref(props);
        return { propRef };
    },
    template: `
<FormElementOutline v-bind="propRef" v-slot="slotProps">
    <span data-testid="input" :class="[...slotProps.styles]" />
</FormElementOutline>`,
});

function createComponent(propsData: object) {
    return mount(TestComponent as any, {
        localVue: createDefaultLocalVue(),
        propsData,
    });
}

describe('FormElementOutline.vue', () => {
    it('should pass on the input styles in the slot props', () => {
        const wrapper = createComponent({});
        const styles = wrapper.find('span[data-testid="input"]').classes();
        expect(styles).include('tw-peer');
    });

    it('should render with invalid styling when invalid prop is provided', () => {
        const wrapper = createComponent({ invalid: true });

        expect(wrapper.find('span').classes()).toContain('!tw-border-red-600');
    });

    it('should render with focus outline styling when useFocusOutline prop is provided', () => {
        const wrapper = createComponent({ useFocusOutline: true });

        expect(wrapper.find('span').classes()).toContain('focus:tw-border-gray-500');
    });

    it('should render with square corners when square prop is provided', () => {
        const wrapper = createComponent({ square: true });

        expect(wrapper.find('span').classes()).toContain('!tw-rounded-none');
    });

    it('should render as pill when round prop is provided', () => {
        const wrapper = createComponent({ round: true });

        expect(wrapper.find('span').classes()).toContain('!tw-rounded-full');
    });
});

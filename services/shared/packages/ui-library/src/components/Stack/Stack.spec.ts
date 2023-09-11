import { mount } from '@vue/test-utils';
import Stack from './Stack.vue';
import { createDefaultLocalVue } from '../../test/local-vue';

function createComponent(propsData?: object) {
    return mount(Stack, {
        localVue: createDefaultLocalVue(),
        propsData,
        slots: { default: 'Content' },
    });
}

type StyleProps = {
    spacing?: '0' | '0.5' | '1' | '2' | '3' | '4' | '6' | '8' | '10';
    tag?: 'div' | 'section' | 'article';
    direction?: 'column' | 'row';
};

describe('Stack.vue', () => {
    it('should render as a div by default', () => {
        const wrapper = createComponent();
        expect(wrapper.element.tagName).toBe('DIV');
    });

    it('should be able to render as different elements', () => {
        const props = { as: 'section' };
        const wrapper = createComponent(props);
        expect(wrapper.element.tagName).toBe('SECTION');
    });

    it.each<[StyleProps, string]>([
        [{}, 'tw-flex-col'],
        [{ spacing: '8' }, 'tw-gap-8'],
        [{ direction: 'row' }, 'tw-flex-row'],
    ])('when the property %j is set it should render with class "%s" ', (props, expectedClass) => {
        const wrapper = createComponent(props);
        expect(wrapper.classes()).include(expectedClass);
    });
});

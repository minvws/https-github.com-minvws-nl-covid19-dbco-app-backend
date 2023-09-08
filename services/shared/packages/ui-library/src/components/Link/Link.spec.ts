import { mount } from '@vue/test-utils';
import { createDefaultLocalVue } from '../../test/local-vue';
import Icon from '../Icon/Icon.vue';
import Link from './Link.vue';

function createComponent(propsData?: object) {
    return mount(Link, {
        localVue: createDefaultLocalVue(),
        propsData,
        slots: { default: 'Label' },
    });
}

type StyleProps = {
    variant?: 'underlined' | 'plain';
    size?: 'sm' | 'md' | 'lg';
};

describe('Link.vue', () => {
    it('should render with an icon when an icon left is set', async () => {
        const wrapper = createComponent({ iconLeft: 'arrow-left' });
        expect(wrapper.findComponent(Icon).exists()).toBe(true);
    });

    it('should render with an icon when an icon right is set', async () => {
        const wrapper = createComponent({ iconLeft: 'arrow-right' });
        expect(wrapper.findComponent(Icon).exists()).toBe(true);
    });

    it.each<[StyleProps, string]>([
        [{}, 'tw-text-violet-700'],
        [{ variant: 'underlined' }, 'tw-underline'],
        [{ variant: 'plain' }, 'tw-no-underline'],
        [{ size: 'sm' }, 'tw-body-sm'],
        [{ size: 'md' }, 'tw-body-md'],
        [{ size: 'lg' }, 'tw-body-lg'],
    ])('when the property %j is set it should render with class "%s" ', (props, expectedClass) => {
        const wrapper = createComponent(props);
        expect(wrapper.classes()).include(expectedClass);
    });
});

import { mount } from '@vue/test-utils';
import Button from './Button.vue';
import Icon from '../Icon/Icon.vue';
import Spinner from '../Spinner/Spinner.vue';
import { createDefaultLocalVue } from '../../test/local-vue';

const createComponent = (propsData?: object) => {
    return mount(Button, {
        localVue: createDefaultLocalVue(),
        propsData,
        slots: { default: 'Label' },
    });
};

type StyleProps = {
    variant?: 'solid' | 'outline' | 'plain';
    size?: 'sm' | 'md' | 'lg';
    color?: 'red' | 'violet';
};

describe('Button.vue', () => {
    it('should render without an icon or spinner and as a button by default', async () => {
        const wrapper = createComponent();
        expect(wrapper.element.tagName).toBe('BUTTON');
        expect(wrapper.findComponent(Icon).exists()).toBe(false);
    });

    it('should render with an icon when an icon left is set', async () => {
        const wrapper = createComponent({ iconLeft: 'arrow-left' });
        expect(wrapper.findComponent(Icon).exists()).toBe(true);
    });

    it('should render with an icon when an icon right is set', async () => {
        const wrapper = createComponent({ iconLeft: 'arrow-right' });
        expect(wrapper.findComponent(Icon).exists()).toBe(true);
    });

    it('should render with spinner when loading is set to true', async () => {
        const wrapper = createComponent();
        expect(wrapper.findComponent(Spinner).exists()).toBe(false);
        await wrapper.setProps({ loading: true });
        expect(wrapper.findComponent(Spinner).exists()).toBe(true);
    });

    it('should disable the button when loading is set to true', async () => {
        const wrapper = createComponent();
        expect(wrapper.attributes('disabled')).toBeUndefined();
        await wrapper.setProps({ loading: true });
        expect(wrapper.attributes('disabled')).toBe('disabled');
    });

    it.each<[StyleProps, string]>([
        [{}, 'tw-text-white'],
        [{ variant: 'outline' }, 'tw-bg-white'],
        [{ variant: 'plain' }, 'tw-bg-transparent'],
        [{ variant: 'outline', color: 'violet' }, 'tw-text-violet-700'],
        [{ variant: 'plain', color: 'red' }, 'tw-text-red-600'],
        [{ size: 'sm' }, 'tw-min-h-[32px]'],
        [{ size: 'md' }, 'tw-min-h-[44px]'],
        [{ size: 'lg' }, 'tw-min-h-[48px]'],
    ])('when the property %j is set it should render with class "%s" ', (props, expectedClass) => {
        const wrapper = createComponent(props);
        expect(wrapper.classes()).include(expectedClass);
    });
});

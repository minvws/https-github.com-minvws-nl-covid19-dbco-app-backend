import { mount } from '@vue/test-utils';
import Badge from './Badge.vue';
import Icon from '../Icon/Icon.vue';
import { createDefaultLocalVue } from '../../test/local-vue';

const createComponent = (propsData?: object) => {
    return mount(Badge, {
        localVue: createDefaultLocalVue(),
        propsData,
        slots: { default: 'Label' },
    });
};

type StyleProps = {
    color?: 'gray' | 'violet' | 'blue' | 'green' | 'yellow' | 'red' | 'seaGreen';
};

describe('Badge.vue', () => {
    it('should render without an icon by default', () => {
        const wrapper = createComponent();
        expect(wrapper.element.tagName).toBe('DIV');
        expect(wrapper.findComponent(Icon).exists()).toBe(false);
    });

    it('should render with an icon when an icon left is set', () => {
        const wrapper = createComponent({ iconLeft: 'arrow-left' });
        expect(wrapper.findComponent(Icon).exists()).toBe(true);
    });

    it.each<[StyleProps, string]>([
        [{}, 'tw-bg-gray-100'],
        [{ color: 'violet' }, 'tw-bg-violet-100'],
        [{ color: 'blue' }, 'tw-bg-blue-100'],
        [{ color: 'green' }, 'tw-bg-green-100'],
        [{ color: 'yellow' }, 'tw-bg-yellow-100'],
        [{ color: 'red' }, 'tw-bg-red-100'],
        [{ color: 'seaGreen' }, 'tw-bg-seaGreen-100'],
    ])('when the property %j is set it should render with class "%s" ', (props, expectedClass) => {
        const wrapper = createComponent(props);
        expect(wrapper.classes()).include(expectedClass);
    });
});

import { mount } from '@vue/test-utils';
import IconButton from './IconButton.vue';
import Spinner from '../Spinner/Spinner.vue';
import { createDefaultLocalVue } from '../../test/local-vue';
import type { IconName } from '../Icon/icons';
import { iconNames } from '../Icon/icons';
import { faker } from '@faker-js/faker';

const randomIcon = () => iconNames[Math.floor(Math.random() * iconNames.length)];

type Props = {
    href?: string;
    ariaLabel?: string;
    icon?: IconName;
    size?: 'sm' | 'md' | 'lg';
};

const createComponent = (propsData: Props = {}) => {
    return mount(IconButton, {
        localVue: createDefaultLocalVue(),
        propsData: {
            ...propsData,
            ariaLabel: propsData.ariaLabel || faker.lorem.sentence(),
            icon: propsData.icon || randomIcon(),
        },
    });
};

type StyleProps = {
    variant?: 'solid' | 'outline' | 'plain';
    size?: 'sm' | 'md' | 'lg';
    color?: 'red' | 'violet';
};

describe('IconButton.vue', () => {
    it('should render with spinner when loading is set to true', async () => {
        const wrapper = createComponent();
        expect(wrapper.findComponent(Spinner).exists()).toBe(false);
        await wrapper.setProps({ loading: true });
        expect(wrapper.findComponent(Spinner).exists()).toBe(true);
    });

    it.each<[StyleProps, string]>([
        [{}, 'tw-w-11'],
        [{ size: 'sm' }, 'tw-w-8'],
        [{ size: 'md' }, 'tw-w-11'],
        [{ size: 'lg' }, 'tw-w-12'],
    ])('when the property %j is set it should render with class "%s" ', (props, expectedClass) => {
        const wrapper = createComponent(props);
        expect(wrapper.classes()).include(expectedClass);
    });
});

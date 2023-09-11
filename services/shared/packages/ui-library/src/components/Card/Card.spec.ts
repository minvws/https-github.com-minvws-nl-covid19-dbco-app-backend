import { shallowMount } from '@vue/test-utils';
import { Heading } from '..';
import { faker } from '@faker-js/faker';
import { createDefaultLocalVue } from '../../test/local-vue';
import Card from './Card.vue';

function createComponent(propsData: object, content?: string) {
    return shallowMount(Card, {
        localVue: createDefaultLocalVue(),
        propsData,
        slots: {
            default: content || faker.lorem.sentence(),
        },
    });
}

describe('Card.vue', () => {
    it('should be able to render with default props', () => {
        const wrapper = createComponent({}, 'test content');
        expect(wrapper.html()).toMatchSnapshot();
    });

    it('should be able to render with as prop', () => {
        const wrapper = createComponent({ as: 'section' });
        expect(wrapper.element.tagName).toBe('SECTION');
    });

    it('should render a heading when title prop is provided', () => {
        const wrapper = createComponent({ title: 'My Title' });

        expect(wrapper.findComponent(Heading).exists()).toBeTruthy();
        expect(wrapper.findComponent(Heading).text()).toContain('My Title');
    });

    it('should render without padding when noPadding prop is provided', () => {
        const wrapper = createComponent({ noPadding: true });

        expect(wrapper.find('div').classes()).not.toContain('tw-p-6');
    });
});

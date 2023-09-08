import { shallowMount } from '@vue/test-utils';
import { faker } from '@faker-js/faker';
import Container from './Container.vue';
import { createDefaultLocalVue } from '../../test/local-vue';

function createComponent(propsData: object, content?: string) {
    return shallowMount(Container, {
        localVue: createDefaultLocalVue(),
        propsData,
        slots: {
            default: content || faker.lorem.sentence(),
        },
    });
}

describe('Container.vue', () => {
    it('should be able to render with default props', () => {
        const wrapper = createComponent({}, 'test content');
        expect(wrapper.html()).toMatchSnapshot();
    });

    it('should be able to render with size prop', () => {
        const wrapper = createComponent({ size: 'md' });
        expect(wrapper.classes()).include('tw-max-w-3xl');
    });

    it('should be able to render with centered content', () => {
        const wrapper = createComponent({ centerContent: true });
        expect(wrapper.classes()).toEqual(expect.arrayContaining(['tw-flex', 'tw-flex-col', 'tw-items-center']));
    });
});

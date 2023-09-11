import { shallowMount } from '@vue/test-utils';
import { faker } from '@faker-js/faker';
import Heading from './Heading.vue';
import { createDefaultLocalVue } from '../../test/local-vue';

function createComponent(propsData?: object, content?: string) {
    return shallowMount(Heading, {
        localVue: createDefaultLocalVue(),
        propsData,
        slots: {
            default: content || faker.lorem.sentence(),
        },
    });
}

describe('Heading.vue', () => {
    it('should be able to render with default props', () => {
        const sentence = faker.lorem.sentence();
        const wrapper = createComponent({}, sentence);

        expect(wrapper.element.tagName).toBe('H2');
        expect(wrapper.text()).toBe(sentence);
    });

    it('should be able to render as different elements', () => {
        const sentence = faker.lorem.sentence();
        const props = { as: 'p' };
        const wrapper = createComponent(props, sentence);

        expect(wrapper.element.tagName).toBe('P');
        expect(wrapper.text()).toBe(sentence);
    });
});

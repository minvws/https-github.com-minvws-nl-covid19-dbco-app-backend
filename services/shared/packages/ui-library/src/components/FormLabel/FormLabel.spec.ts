import { shallowMount } from '@vue/test-utils';
import { faker } from '@faker-js/faker';
import FormLabel from './FormLabel.vue';
import { createDefaultLocalVue } from '../../test/local-vue';

function createComponent(propsData?: object, content?: string, extra?: string) {
    return shallowMount(FormLabel, {
        localVue: createDefaultLocalVue(),
        propsData,
        slots: {
            default: content || faker.lorem.sentence(),
            ...(extra ? { extra } : {}),
        },
    });
}

describe('FormLabel.vue', () => {
    it('should be able to render with text content', () => {
        const sentence = faker.lorem.sentence();
        const wrapper = createComponent({}, sentence);

        expect(wrapper.element.tagName).toBe('LABEL');
        expect(wrapper.text()).toBe(sentence);
    });

    it('should be able to render as a different element and with extra content', () => {
        const extra = faker.lorem.sentence();
        const props = { as: 'p' };
        const wrapper = createComponent(props, faker.lorem.sentence(), extra);

        expect(wrapper.element.tagName).toBe('P');
        expect(wrapper.text()).toContain(extra);
    });
});

import { mount } from '@vue/test-utils';
import { createDefaultLocalVue } from '../../test/local-vue';
import Alert from './Alert.vue';
import { faker } from '@faker-js/faker';

function createComponent(propsData: object, slots: Record<string, string>) {
    return mount(Alert, {
        localVue: createDefaultLocalVue(),
        propsData,
        slots,
    });
}

describe('Alert.vue', () => {
    it('should be able to render with default props', () => {
        const content = faker.lorem.sentence();
        const wrapper = createComponent({}, { default: content });
        const icon = wrapper.find('svg');
        expect(wrapper.text()).toBe(content);
        expect(icon.attributes('aria-label')).toBe('Toelichting');
    });

    it('should be able to render with different variant with additional content', () => {
        const content = faker.lorem.sentence();
        const additionalContent = faker.lorem.sentence();
        const wrapper = createComponent({ variant: 'error' }, { default: content, additional: additionalContent });
        const icon = wrapper.find('svg');
        expect(wrapper.text()).includes(content);
        expect(wrapper.text()).includes(additionalContent);
        expect(icon.attributes('aria-label')).toBe('Fout');
    });

    it('should be able to render with as prop', () => {
        const wrapper = createComponent({ as: 'section' }, { default: faker.lorem.sentence() });
        expect(wrapper.element.tagName).toBe('SECTION');
    });
});

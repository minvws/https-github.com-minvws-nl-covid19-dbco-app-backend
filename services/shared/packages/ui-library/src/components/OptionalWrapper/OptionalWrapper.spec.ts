import { mount } from '@vue/test-utils';
import { faker } from '@faker-js/faker';
import OptionalWrapper from './OptionalWrapper.vue';
import { createDefaultLocalVue } from '../../test/local-vue';

function createComponent(propsData: { shouldWrap: boolean; class?: string }, content: string) {
    return mount(OptionalWrapper, {
        localVue: createDefaultLocalVue(),
        propsData,
        slots: {
            default: content,
        },
    });
}

describe('OptionalWrapper.vue', () => {
    it('should be able to render without wrapper', () => {
        const content = faker.lorem.word();
        const wrapper = createComponent({ shouldWrap: false, class: faker.lorem.word() }, content);

        expect(wrapper.classes()).toEqual([]);
        expect(wrapper.element.tagName).toBe('TEMPLATE');
        expect(wrapper.text()).toBe(content);
    });

    it('should be able to render with wrapper', () => {
        const content = faker.lorem.word();
        const className = faker.lorem.word();
        const wrapper = createComponent({ shouldWrap: true, class: className }, content);

        expect(wrapper.classes()).toEqual([className]);
        expect(wrapper.element.tagName).toBe('DIV');
        expect(wrapper.text()).toBe(content);
    });

    it('should throw if wrapped component has multiple root nodes', () => {
        type RenderFunction = (createElement: () => unknown, context: { children: unknown[] }) => unknown;

        const FunctionalComponent = OptionalWrapper as unknown as {
            render: RenderFunction;
        };
        const ctx = { children: [1, 2] };

        expect(() => FunctionalComponent.render(vi.fn(), ctx)).toThrowError(
            'this component accepts only one root node in its slot'
        );
    });
});

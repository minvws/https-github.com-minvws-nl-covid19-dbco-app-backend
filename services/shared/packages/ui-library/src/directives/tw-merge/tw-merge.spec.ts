import { faker } from '@faker-js/faker';
import { mount } from '@vue/test-utils';
import type { PropType } from 'vue';
import { defineComponent } from 'vue';
import { createDefaultLocalVue } from '../../test/local-vue';

const ComponentA = defineComponent({
    template: `<div v-tw-merge class="class-A tw-text-sm"><slot/></div>`,
});

const ComponentB = defineComponent({
    components: { ComponentA },
    template: `<ComponentA class="class-B tw-text-lg"><slot/></ComponentA>`,
});

const ComponentC = defineComponent({
    components: { ComponentB },
    template: `<ComponentB class="class-C tw-text-md"><slot/></ComponentB>`,
});

const ComponentD = defineComponent({
    props: {
        classBinding: {
            type: [String, Array, Object] as PropType<string | string[] | Record<string, unknown>>,
        },
    },
    components: { ComponentA },
    setup({ classBinding }) {
        return { classBinding };
    },
    template: `<ComponentA class="class-D" :class="classBinding"><slot/></ComponentA>`,
});

const ComponentDuplicates = defineComponent({
    template: `<div>
                <div id="duplicates" v-tw-merge class="tw-text-sm tw-text-md tw-text-lg"><slot/></div>
            </div>`,
});

const ComponentSVG = defineComponent({
    template: `
    <svg v-tw-merge class="class-SVG tw-text-black" width="20" height="20" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
    <path d="M10.5303 18.0303C10.2374 18.3232 9.76256" />
    </svg>`,
});

const ComponentWithSVG = defineComponent({
    components: { ComponentSVG },
    template: `<ComponentSVG class="tw-text-white" />`,
});

function createComponent(Component: any, propsData?: Record<string, unknown>, content?: string) {
    return mount(Component, {
        localVue: createDefaultLocalVue(),
        propsData,
        slots: content
            ? {
                  default: content,
              }
            : undefined,
    });
}

describe('v-tw-merge', () => {
    it.each([
        [ComponentA, ['class-A', 'tw-text-sm']],
        [ComponentB, ['class-A', 'class-B', 'tw-text-lg']],
        [ComponentC, ['class-A', 'class-B', 'class-C', 'tw-text-md']],
    ])('should merge parent inherited tailwind classes from top to bottom', (Component, expectedClassList) => {
        const content = faker.lorem.word();
        const wrapper = createComponent(Component, {}, content);

        expect(wrapper.element.tagName).toBe('DIV');
        expect(wrapper.text()).toBe(content);
        expect(wrapper.classes()).toEqual(expectedClassList);
    });

    it.each([
        [undefined, ['class-A', 'tw-text-sm', 'class-D']],
        [null, ['class-A', 'tw-text-sm', 'class-D']],
        ['test', ['class-A', 'tw-text-sm', 'class-D', 'test']],
        [
            ['test-1', 'test-2', 'test-3'],
            ['class-A', 'tw-text-sm', 'class-D', 'test-1', 'test-2', 'test-3'],
        ],
        [
            {
                'test-1': true,
                'test-2': false,
                'test-3': '',
                'test-4': 0,
                'test-5': 1,
                'test-6': {},
                'test-7': [],
            },
            ['class-A', 'tw-text-sm', 'class-D', 'test-1', 'test-5', 'test-6', 'test-7'],
        ],
    ])('should handle class bindings', (classBinding, expectedClassList) => {
        const content = faker.lorem.word();
        const wrapper = createComponent(ComponentD, { classBinding }, content);

        expect(wrapper.element.tagName).toBe('DIV');
        expect(wrapper.text()).toBe(content);
        expect(wrapper.classes()).toEqual(expectedClassList);
    });

    it('should NOT merge classes when there is not a parent definition', () => {
        const content = faker.lorem.word();
        const wrapper = createComponent(ComponentDuplicates, {}, content);

        // The element with the directive can not be on the root of the template
        // Otherwise the test container itself will count as the "parent"
        const duplicatesElement = wrapper.find('#duplicates');

        expect(duplicatesElement.element.tagName).toBe('DIV');
        expect(duplicatesElement.text()).toBe(content);
        expect(duplicatesElement.classes()).toEqual(['tw-text-sm', 'tw-text-md', 'tw-text-lg']);
    });

    it('should also work with SVG elements', () => {
        const wrapper = createComponent(ComponentWithSVG);

        expect(wrapper.element.tagName).toBe('svg');
        expect(wrapper.classes()).toEqual(['class-SVG', 'tw-text-white']);
    });
});

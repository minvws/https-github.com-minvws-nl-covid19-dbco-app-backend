import Highlight from '@/components/utils/Highlight/Highlight.vue';
import { setupTest } from '@/utils/test';

import { shallowMount } from '@vue/test-utils';
import type { VueConstructor } from 'vue';

const createComponent = setupTest((localVue: VueConstructor, props?: object) =>
    shallowMount(Highlight, {
        localVue,
        propsData: props,
    })
);

describe('Place.vue', () => {
    it('should show text when an empty query is given', () => {
        const props = {
            text: 'The quick brown fox jumps over the lazy dog',
            query: '',
        };

        const wrapper = createComponent(props);
        expect(wrapper.text()).toBe(props.text);
    });

    it('should keep the delimiter (query)', () => {
        const props = {
            text: 'The quick brown fox jumps over the lazy dog',
            query: 'quick',
        };

        const wrapper = createComponent(props);
        expect(wrapper.text()).toBe(props.text);
    });

    it('should highlight if there is a match (single)', () => {
        const props = {
            text: 'The quick brown fox jumps over the lazy dog',
            query: 'jumps',
        };

        const wrapper = createComponent(props);
        expect(wrapper.findAll('em').length).toBe(1);
    });

    it('should highlight if there is a match (multiple)', () => {
        const props = {
            text: 'The quick brown fox jumps over the lazy dog',
            query: 'the',
        };

        const wrapper = createComponent(props);
        expect(wrapper.findAll('em').length).toBe(2);
    });

    it('should NOT highlight anything if there is no match', () => {
        const props = {
            text: 'The quick brown fox jumps over the lazy dog',
            query: 'abc',
        };

        const wrapper = createComponent(props);
        expect(wrapper.findAll('em').length).toBe(0);
    });

    it('should highlight case-insensitive', () => {
        const props = {
            text: 'The the tHe',
            query: 'the',
        };

        const wrapper = createComponent(props);
        expect(wrapper.findAll('em').length).toBe(3);
    });

    it('should not break when regex characters are used in the query', () => {
        const props = {
            text: 'Test',
            query: ')(][}{^$.*+?|',
        };

        const wrapper = createComponent(props);
        expect(wrapper.isVisible()).toBe(true);
    });
});

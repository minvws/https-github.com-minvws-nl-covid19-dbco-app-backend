import { shallowMount } from '@vue/test-utils';
import FormReadonly from './FormReadonly.vue';
import { createContainer, setupTest } from '@/utils/test';
import type { VueConstructor } from 'vue';

const createComponent = setupTest((localVue: VueConstructor, props?: object) => {
    return shallowMount(FormReadonly, {
        localVue,
        propsData: props,
        attachTo: createContainer(),
    });
});

describe('FormReadonly.vue', () => {
    // NOTE:  [BootstrapVue warn]: tooltip - The provided target is no valid HTML element.

    it('should fill the input field value with 2021/06/01 when format is date', () => {
        // ARRANGE
        const props = {
            context: {
                model: '2021-06-01',
            },
            format: 'date',
        };
        const wrapper = createComponent(props);
        // ASSERT
        expect(wrapper.find('BFormInput-stub[readonly="true"]').attributes().value).toContain('01/06/2021');
    });

    it('should fill the input field value with 2021-06-01 when format is filled in but not with "date"', () => {
        // ARRANGE
        const props = {
            context: {
                model: '2021-06-01',
            },
            format: 'aaaaaa',
        };
        const wrapper = createComponent(props);
        // ASSERT
        expect(wrapper.find('BFormInput-stub[readonly="true"]').attributes().value).toContain('2021-06-01');
    });

    it('should set the tooltip', () => {
        // ARRANGE
        const props = {
            context: {
                model: '2021-06-01',
            },
            format: 'date',
            tooltip: 'testTooltip',
        };
        const wrapper = createComponent(props);

        // ASSERT
        expect(wrapper.find('i').attributes().title).toBe('testTooltip');
    });

    it('should display a date, but not formatted if format is undefined', () => {
        // ARRANGE
        const props = {
            context: {
                model: '2021-06-01',
            },
            format: undefined,
        };
        const wrapper = createComponent(props);

        // ASSERT
        expect(wrapper.find('BFormInput-stub[readonly="true"]').attributes().value).toContain('2021-06-01');
    });

    it('should display the value as "" if context.model and format are undefined', () => {
        // ARRANGE
        const props = {
            context: {
                model: undefined,
            },
            format: undefined,
        };
        const wrapper = createComponent(props);

        // ASSERT
        expect(wrapper.find('BFormInput-stub[readonly="true"]').attributes().value).toContain('');
    });
});

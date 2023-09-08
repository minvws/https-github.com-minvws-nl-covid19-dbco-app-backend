import { shallowMount } from '@vue/test-utils';
import FormFeedback from './FormFeedback.vue';
import { Store } from 'vuex';

import * as formUtils from '@/utils/form';
import { setupTest } from '@/utils/test';
import type { VueConstructor } from 'vue';
import { vi } from 'vitest';

const createComponent = setupTest((localVue: VueConstructor, props?: object, storeData?: Store<any>) => {
    return shallowMount(FormFeedback, {
        localVue,
        propsData: props,
        store: storeData ?? new Store({}),
    });
});
describe('FormFeedback.vue', () => {
    it('should load the component with title property', () => {
        const props = {
            title: 'testTitle',
            conditions: [
                {
                    prop: 'test.someTestValue',
                    values: ['test'],
                },
            ],
        };

        const wrapper = createComponent(props);

        expect(wrapper.find('span').text()).toBe('testTitle');
    });

    it('should show success icon if conditions true', () => {
        vi.spyOn(formUtils, 'formConditionMet').mockReturnValueOnce(true);

        const props = {
            title: 'testTitle',
            conditions: [
                {
                    prop: 'test.someTestValue',
                    values: ['test'],
                },
            ],
        };

        const wrapper = createComponent(props);

        expect(wrapper.find('.icon--success').exists()).toBe(true);
        expect(wrapper.find('.icon--error').exists()).toBe(false);
    });

    it('should show error icon if conditions false', () => {
        vi.spyOn(formUtils, 'formConditionMet').mockReturnValueOnce(false);

        const props = {
            title: 'testTitle',
            conditions: [
                {
                    prop: 'test.someTestValue',
                    values: ['test'],
                },
            ],
        };

        const wrapper = createComponent(props);

        expect(wrapper.find('.icon--error').exists()).toBe(true);
        expect(wrapper.find('.icon--success').exists()).toBe(false);
    });
});

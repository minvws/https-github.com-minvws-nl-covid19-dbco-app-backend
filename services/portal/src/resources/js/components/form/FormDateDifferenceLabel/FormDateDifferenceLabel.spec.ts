import { setupTest } from '@/utils/test';
import { shallowMount } from '@vue/test-utils';
import type { VueConstructor } from 'vue';
import FormDateDifferenceLabel from './FormDateDifferenceLabel.vue';

const createComponent = setupTest((localVue: VueConstructor, props?: object, rootModel: object = {}) => {
    return shallowMount(FormDateDifferenceLabel, {
        localVue,
        propsData: props,
        provide: {
            getIndex: () => 1,
            rootModel: () => rootModel,
        },
    });
});

describe('FormDateDifferenceLabel.vue', () => {
    it('should show the date difference of given form fields', () => {
        const props = {
            baseDateName: 'testBaseDateName',
            baseDateLabel: 'testBaseDateLabel',
            dateName: 'testDateName',
        };

        const rootModel = {
            testBaseDateName: '2022-01-01T00:00:00',
            testDateName: '2022-01-02T00:00:00',
        };

        const wrapper = createComponent(props, rootModel);

        expect(wrapper.find('div').text()).toBe('1 dag na testBaseDateLabel');
    });

    it('should show the date difference of given form fields within repeatable (by index)', () => {
        // > will be replaced by the index of the repeatable, found through injected getIndex()
        const props = {
            dateName: 'repeatable.>.testDateName',
            baseDateName: 'repeatable.>.testBaseDateName',
            baseDateLabel: 'testBaseDateLabel',
        };

        const rootModel = {
            repeatable: [
                {
                    testBaseDateName: '2022-01-01T00:00:00',
                    testDateName: '2022-01-02T00:00:00',
                },
                {
                    testBaseDateName: '2022-01-01T00:00:00',
                    testDateName: '2022-01-29T00:00:00',
                },
            ],
        };

        const wrapper = createComponent(props, rootModel);

        expect(wrapper.find('div').text()).toBe('4 weken na testBaseDateLabel');
    });
});

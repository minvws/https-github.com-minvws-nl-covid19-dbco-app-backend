import { createLocalVue, shallowMount } from '@vue/test-utils';
import DbcoVersion from './DbcoVersion.vue';

describe('DbcoVersion.vue', () => {
    const localVue = createLocalVue();

    const setWrapper = (data: object = {}) =>
        shallowMount(DbcoVersion, {
            localVue,
            data: () => data,
        });

    it('should render version', () => {
        const data = {
            version: '1.1',
        };

        const wrapper = setWrapper(data);

        expect(wrapper.exists()).toBe(true);
        expect(wrapper.html()).toContain('1.1');
    });
});

import type { Wrapper } from '@vue/test-utils';
import { createLocalVue, shallowMount } from '@vue/test-utils';
// @ts-ignore
import DbcoFormWrap from '@/components/utils/DbcoFormWrap/DbcoFormWrap.vue';

describe('DbcoFormWrap.vue', () => {
    const localVue = createLocalVue();
    let wrapper: Wrapper<Vue>;

    const setWrapper = () => {
        wrapper = shallowMount(DbcoFormWrap, {
            localVue,
        });
    };

    it('should load', () => {
        // ARRANGE
        setWrapper();

        // ASSERT
        expect(wrapper.findComponent({ name: 'DbcoFormWrap' }).exists()).toBe(true);
    });
});

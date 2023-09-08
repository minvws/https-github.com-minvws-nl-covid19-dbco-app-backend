import { setupTest } from '@/utils/test';
import { Badge } from '@dbco/ui-library';
import { mount } from '@vue/test-utils';
import type { VueConstructor } from 'vue';
import PolicyVersionStatusBadge from './PolicyVersionStatusBadge.vue';

type StatusProps = { status?: 'draft' | 'active-soon' | 'active' | 'old' };

const createComponent = setupTest((localVue: VueConstructor, propsData?: object) => {
    return mount(PolicyVersionStatusBadge, {
        localVue,
        propsData,
    });
});

describe('PolicyVersionStatusBadge.vue', () => {
    it('should not render badge with invalid status ', () => {
        const props = { status: 'invalid' };
        const wrapper = createComponent(props);

        expect(wrapper.findComponent(Badge).exists()).toBe(false);
    });

    it.each<[StatusProps, string]>([
        [{ status: 'draft' }, 'tw-bg-violet-100'],
        [{ status: 'active-soon' }, 'tw-bg-blue-100'],
        [{ status: 'active' }, 'tw-bg-green-100'],
        [{ status: 'old' }, 'tw-bg-gray-100'],
    ])('when the property %j is set it should render with class "%s" ', (props, expectedClass) => {
        const wrapper = createComponent(props);
        expect(wrapper.findComponent(Badge).classes()).include(expectedClass);
    });
});

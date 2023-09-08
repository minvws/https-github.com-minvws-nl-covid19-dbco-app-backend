import type { VueConstructor } from 'vue';
import { shallowMount } from '@vue/test-utils';
import { setupTest } from '@/utils/test';
import CalendarItemConfigCardWrapper from './CalendarItemConfigCardWrapper.vue';
import { fakeCalendarItemConfig } from '@/utils/__fakes__/admin';
import { PolicyVersionStatusV1 } from '@dbco/enum';
import { Card, RadioCollapse } from '@dbco/ui-library';
import { adminApi } from '@dbco/portal-api';
import type { CalendarItemConfig } from '@dbco/portal-api/admin.dto';

vi.mock('@/router/router');

const createComponent = setupTest((localVue: VueConstructor, props?: object) => {
    return shallowMount(CalendarItemConfigCardWrapper, {
        localVue,
        propsData: {
            ...props,
        },
    });
});

describe('CalendarItemConfigCardWrapper.vue', () => {
    it('should render RadioCollapse component for hideable config', () => {
        const props = {
            config: fakeCalendarItemConfig({ isHideable: true }),
            versionStatus: PolicyVersionStatusV1.VALUE_draft,
        };
        const wrapper = createComponent(props);

        expect(wrapper.findAllComponents(RadioCollapse).length).toBe(1);
    });

    it('should render Card component for non hideable config', () => {
        const props = { config: fakeCalendarItemConfig(), versionStatus: PolicyVersionStatusV1.VALUE_draft };
        const wrapper = createComponent(props);

        expect(wrapper.findAllComponents(Card).length).toBe(1);
    });

    it('should update the config when isHidden is toggled', () => {
        const spyOnUpdate = vi
            .spyOn(adminApi, 'updateCalendarItemConfig')
            .mockImplementationOnce(() => Promise.resolve(fakeCalendarItemConfig() as CalendarItemConfig));
        const props = {
            config: fakeCalendarItemConfig({ isHideable: true, isHidden: true }),
            versionStatus: PolicyVersionStatusV1.VALUE_draft,
        };
        const wrapper = createComponent(props);

        wrapper.findComponent(RadioCollapse).vm.$emit('change', { target: { value: false } });

        expect(spyOnUpdate).toHaveBeenCalledOnce();
    });
});

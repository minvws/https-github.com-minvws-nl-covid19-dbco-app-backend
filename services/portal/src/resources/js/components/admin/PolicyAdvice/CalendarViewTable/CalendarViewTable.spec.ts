import type { VueConstructor } from 'vue';
import { shallowMount } from '@vue/test-utils';
import { fakerjs, flushCallStack, setupTest } from '@/utils/test';
import CalendarViewTable from './CalendarViewTable.vue';
import { Tbody, Tr } from '@dbco/ui-library';
import { useRouter } from '@/router/router';
import { fakeCalendarView } from '@/utils/__fakes__/admin';
import { adminApi } from '@dbco/portal-api';

vi.mock('@/router/router');

vi.mock('@dbco/portal-api/client/admin.api', () => ({
    getCalendarViews: vi.fn(() => Promise.resolve([fakeCalendarView(), fakeCalendarView()])),
}));

const createComponent = setupTest((localVue: VueConstructor, props?: object) => {
    return shallowMount(CalendarViewTable, {
        localVue,
        propsData: props,
    });
});

describe('CalendarViewTable.vue', () => {
    it('should show table with correct number of views', async () => {
        const wrapper = createComponent({ versionUuid: fakerjs.string.uuid() });
        await flushCallStack();
        const tableBody = wrapper.findComponent(Tbody);
        expect(tableBody.findAllComponents(Tr).length).toBe(2);
    });

    it('should show a message when no items are found', () => {
        vi.spyOn(adminApi, 'getCalendarViews').mockImplementationOnce(() => Promise.resolve([]));
        const wrapper = createComponent({ versionUuid: fakerjs.string.uuid() });
        expect(wrapper.text()).toContain('Geen kalender views gevonden');
    });

    it('should push detail view path to router when table row is clicked', async () => {
        const wrapper = createComponent({ versionUuid: fakerjs.string.uuid() });
        await flushCallStack();
        const tableBodyRow = wrapper.findComponent(Tbody).findAllComponents(Tr).at(0);

        await tableBodyRow.vm.$emit('click');

        expect(useRouter().push).toHaveBeenCalledTimes(1);
    });
});

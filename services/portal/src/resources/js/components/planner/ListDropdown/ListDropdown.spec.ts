import ListDropdown from '@/components/planner/ListDropdown/ListDropdown.vue';
import { BDropdown } from 'bootstrap-vue';
import { shallowMount } from '@vue/test-utils';
import { caseListApi } from '@dbco/portal-api';
import InfiniteLoading from 'vue-infinite-loading';
import { createTestingPinia } from '@pinia/testing';

vi.mock('@/env');
import env from '@/env';
import { flushCallStack, setupTest } from '@/utils/test';
import type { VueConstructor } from 'vue';
import { CaseListStatsMode } from '@dbco/portal-api/client/caseList.api';

const createComponent = setupTest((localVue: VueConstructor) => {
    return shallowMount(ListDropdown, {
        localVue,
        pinia: createTestingPinia({
            stubActions: false,
        }),
        stubs: {
            BDropdown,
        },
    });
});

describe('ListDropdown.vue', () => {
    beforeEach(() => {
        vi.resetAllMocks();
        env.isPlannerGetCaselistsStats0Enabled = false;

        vi.spyOn(caseListApi, 'getLists').mockImplementation(() =>
            Promise.resolve({
                from: 1,
                to: 1,
                total: 1,
                currentPage: 1,
                lastPage: 1,
                data: [
                    {
                        uuid: 'e4d51fa5-05d5-3189-bf7b-d458bd343954',
                        name: 'Lijst blanditiis',
                        isDefault: false,
                        isQueue: false,
                        assignedCasesCount: 3,
                        unassignedCasesCount: 4,
                    },
                ],
            })
        );
    });

    it('should emit and close when clicking a row in the list dropdown', () => {
        const listDropdown = createComponent();
        const list = { uuid: '1234', name: 'Lijstnaam' };

        const spyDropdownHide = vi.spyOn(listDropdown.vm.$refs.listDropdownRef as BDropdown, 'hide');

        listDropdown.findComponent({ name: 'BTable' }).vm.$emit('row-clicked', list);

        // Hide dropdown and emit list selection
        expect(spyDropdownHide).toHaveBeenCalledTimes(1);
        expect(listDropdown.emitted('selected')).toStrictEqual([[list]]);
    });

    it('should get second lists page when scrolling in lists dropdown', async () => {
        const complete = vi.fn();
        const loaded = vi.fn();
        const getLists = vi.spyOn(caseListApi, 'getLists');
        getLists.mockImplementation(
            (stats: CaseListStatsMode, types: string, page = 1) =>
                Promise.resolve({
                    lastPage: 2,
                    from: 0,
                    to: 1,
                    total: 2,
                    currentPage: page,
                    data: [{ name: `item-${page}`, uuid: '1234' }],
                }) as any
        );
        const listDropdown = createComponent();
        await listDropdown.findComponent(InfiniteLoading).vm.$emit('infinite', { complete, loaded });
        await flushCallStack();
        await listDropdown.findComponent(InfiniteLoading).vm.$emit('infinite', { complete, loaded });
        await listDropdown.vm.$nextTick();

        // First page
        expect(getLists).toHaveBeenCalledWith(CaseListStatsMode.Disabled, 'list', 1);

        // Last Page
        expect(getLists).toBeCalledWith(CaseListStatsMode.Disabled, 'list', 2);
        expect(complete).toBeCalledTimes(1);
    });

    it('should fetch counts in two request to prevent waiting for stats', async () => {
        const getLists = vi.spyOn(caseListApi, 'getLists');
        const complete = vi.fn();
        const listDropdown = createComponent();

        await listDropdown.findComponent(InfiniteLoading).vm.$emit('infinite', { complete });
        await flushCallStack();

        expect(getLists).toHaveBeenCalledTimes(2);
        expect(getLists).toBeCalledWith(CaseListStatsMode.Disabled, 'list', 1);
        expect(getLists).toBeCalledWith(CaseListStatsMode.Enabled, 'list', 1, expect.any(Object));
        expect(complete).toBeCalled();
    });

    it('should reset pages when re-opening the dropdown', async () => {
        const listDropdown = createComponent();
        listDropdown.findComponent(InfiniteLoading).vm.$emit('infinite', { complete: vi.fn() });
        await flushCallStack();
        expect(listDropdown.vm.lists).toHaveLength(1);

        listDropdown.findComponent(BDropdown).vm.$emit('show');
        expect(listDropdown.vm.lists).toHaveLength(0);
    });
});

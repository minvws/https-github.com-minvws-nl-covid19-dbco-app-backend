import { shallowMount } from '@vue/test-utils';
import CovidCaseOverviewPlannerView from './CovidCaseOverviewPlannerView.vue';
import type { BTab } from 'bootstrap-vue';
import { BDropdown } from 'bootstrap-vue';
import userInfoStore from '@/store/userInfo/userInfoStore';
import Vuex from 'vuex';
import { PlannerView } from '@dbco/portal-api/caseList.dto';
import { PermissionV1 } from '@dbco/enum';
import { caseListApi } from '@dbco/portal-api';
import ListDropdown from '@/components/planner/ListDropdown/ListDropdown.vue';
import { createTestingPinia } from '@pinia/testing';
import { flushCallStack, setupTest } from '@/utils/test';

vi.mock('@/env');
import env from '@/env';
import type { Route } from 'vue-router';
import type VueRouter from 'vue-router';
import type { VueConstructor } from 'vue';
import { usePlanner } from '@/store/planner/plannerStore';
import type { MockedFunction } from 'vitest';
import { vi } from 'vitest';

vi.mock('@dbco/portal-api/client/caseList.api', () => ({
    createList: vi.fn(() =>
        Promise.resolve({
            uuid: 'abcdef',
        })
    ),
    deleteList: vi.fn(() => Promise.resolve()),
    getList: vi.fn(() => Promise.resolve(lists[0])),
    listCounts: vi.fn(() =>
        Promise.resolve({
            unassigned: 184,
            assigned: 51,
            outsourced: 36,
            queued: 15,
            completed: 774,
        })
    ),
    updateList: vi.fn(() =>
        Promise.resolve({
            uuid: 'ghijkl',
        })
    ),
}));

const lists = [
    { uuid: '1234', name: 'Lijstnaam' },
    { uuid: '5678', name: 'Lijstnaam 2' },
];

const tabs: Record<string, string> = {
    [PlannerView.INTAKELIST]: 'Intakevragenlijsten',
    [PlannerView.UNASSIGNED]: 'Te verdelen',
    [PlannerView.QUEUED]: 'Wachtrij',
    [PlannerView.OUTSOURCED]: 'Uitbesteed',
    [PlannerView.ASSIGNED]: 'Toegewezen',
    [PlannerView.COMPLETED]: 'Te controleren',
    [PlannerView.ARCHIVED]: 'Recent gesloten',
};

const createComponent = setupTest(
    (
        localVue: VueConstructor,
        data: object = {},
        userInfoState: object = {},
        $route: Partial<Route> = { params: {} },
        $router: Partial<VueRouter> = {}
    ) => {
        const userInfoStoreModule = {
            ...userInfoStore,
            state: {
                ...userInfoStore.state,
                ...userInfoState,
            },
        };

        const pinia = createTestingPinia({
            stubActions: false,
        });

        const planner = usePlanner();
        planner.fetchViewFilters = vi.fn(() => Promise.resolve());

        return shallowMount(CovidCaseOverviewPlannerView, {
            localVue,
            data: () => data,
            mocks: {
                $route,
                $router,
            },
            store: new Vuex.Store({
                modules: {
                    userInfo: userInfoStoreModule,
                },
            }),
            pinia,
            stubs: {
                BDropdown,
            },
        });
    }
);

describe('CovidCaseOverviewPlannerView.vue', () => {
    it('should load the component and fetch filters', () => {
        const wrapper = createComponent();

        const planner = usePlanner();

        // Check if planner.fetchViewFilters has been called
        expect(planner.fetchViewFilters).toHaveBeenCalledTimes(1);

        expect(wrapper.exists()).toBe(true);
    });

    it('should show "Alle cases" if no list is selected', () => {
        const wrapper = createComponent({
            list: null,
            lists,
        });

        expect(wrapper.find('.title').text()).toEqual('Alle cases');
    });

    it('should show the list name if a list is selected', () => {
        const wrapper = createComponent({
            list: { uuid: '1234', name: 'Lijstnaam' },
        });

        expect(wrapper.find('.title').text()).toEqual('Lijstnaam');
    });

    it('should set selectedList when clicking a row in the list dropdown and retrieve counts', async () => {
        const push = vi.fn();
        const wrapper = createComponent(
            {
                lists,
            },
            undefined,
            undefined,
            { push }
        );

        wrapper.findComponent(ListDropdown).vm.$emit('selected', lists[0]);

        // call router hooks for updating data when route updates
        await (CovidCaseOverviewPlannerView as any)?.beforeRouteUpdate.call(
            wrapper.vm,
            { params: { list: '1234' } },
            undefined,
            (c: CallableFunction) => c && c()
        );

        // Add list route change to history
        expect(push).toBeCalledWith({ path: '/planner/1234', query: {} });

        // Set list
        expect(wrapper.vm.list).toBe(lists[0]);
    });

    // Tabs
    it('should show all tabs when list=null and organisation.type === "regionalGGD"', () => {
        const wrapper = createComponent(
            {
                tabs,
            },
            {
                organisation: {
                    type: 'regionalGGD',
                },
            }
        );

        expect(wrapper.findAllComponents({ name: 'BTab' }).length).toBe(7);

        // Expect the refs of the tables to be present as well
        expect(Object.keys(wrapper.vm.$refs)).toEqual(expect.arrayContaining(Object.keys(tabs)));
    });

    it('should not show tab of type ListView.INTAKELIST when a env isIntakeMatchCaseEnabled is false', () => {
        env.isIntakeMatchCaseEnabled = false;
        const wrapper = createComponent(
            {
                tabs,
            },
            {
                organisation: {
                    type: 'regionalGGD',
                },
            }
        );

        expect(wrapper.findAllComponents({ name: 'BTab' }).length).toBe(6);

        const tabsVisible = Object.keys(tabs).filter((tab) => tab !== PlannerView.INTAKELIST);

        // Expect the refs of the tables to be present as well
        expect(Object.keys(wrapper.vm.$refs)).toEqual(expect.arrayContaining(tabsVisible));

        env.isIntakeMatchCaseEnabled = true;
    });

    it('should not show tab of type ListView.INTAKELIST and ListView.QUEUED and ListView.OUTSOURCED when a list is selected', () => {
        const wrapper = createComponent(
            {
                list: { uuid: '1234' },
                tabs,
            },
            {
                organisation: {
                    type: 'regionalGGD',
                },
            }
        );

        expect(wrapper.findAllComponents({ name: 'BTab' }).length).toBe(4);

        const tabsVisible = Object.keys(tabs).filter(
            (tab) => tab !== PlannerView.INTAKELIST && tab !== PlannerView.QUEUED && tab !== PlannerView.OUTSOURCED
        );

        // Expect the refs of the tables to be present as well
        expect(Object.keys(wrapper.vm.$refs)).toEqual(expect.arrayContaining(tabsVisible));
    });

    it('should not show tab of type ListView.OUTSOURCED when organisation.type !== "regionalGGD"', () => {
        const wrapper = createComponent(
            {
                tabs,
            },
            {
                organisation: {
                    type: 'external',
                },
            }
        );

        expect(wrapper.findAllComponents({ name: 'BTab' }).length).toBe(6);

        const tabsVisible = expect.arrayContaining(Object.keys(tabs).filter((tab) => tab !== PlannerView.OUTSOURCED));

        // Expect the refs of the tables to be present as well
        expect(Object.keys(wrapper.vm.$refs)).toEqual(tabsVisible);
    });

    it('should return the ref to the table of the currently active tab through tableRef', () => {
        const wrapper = createComponent(
            {
                activeTab: 1,
                tabs,
            },
            {
                organisation: {
                    type: 'external',
                },
            }
        );

        const activeTabs = Object.keys(tabs).filter((tab) => tab !== PlannerView.OUTSOURCED);
        const expectedTableRefName = wrapper.findComponent({ ref: activeTabs[1] }).vm.$vnode.data?.ref;

        expect((wrapper.vm.tableRef as Vue).$vnode.data?.ref).toEqual(expectedTableRefName);
    });

    it('should render the title and tabcount of a list', async () => {
        const spyListCounts = vi.spyOn(caseListApi, 'listCounts');
        const wrapper = createComponent(
            {
                tabs,
            },
            {
                organisation: {
                    type: 'regionalGGD',
                },
            },
            { params: {} }
        );
        // call beforeRouteEnter hook/gaurd to load initial data (list / counts)
        await (CovidCaseOverviewPlannerView as any)?.beforeRouteEnter.call(
            wrapper.vm,
            { params: { list: 'asdf' } },
            undefined,
            (c: CallableFunction) => c(wrapper.vm)
        );
        await flushCallStack();

        const renderedTabs = wrapper.findAllComponents({ name: 'BTab' });
        // Returns VNode[] of title slot: [ VNodeText, VNodeSpan[VNodeText] ]
        const titleSlotVNodes = (renderedTabs.at(0).vm as BTab).$slots.title;

        const title = titleSlotVNodes?.[0].text?.trim();
        const count = titleSlotVNodes?.[1].children?.[0].text;

        expect(title).toBe(tabs[PlannerView.UNASSIGNED]);

        // caseListApi.listCounts should be called for counts for selected list and all cases list
        expect(spyListCounts).toHaveBeenCalledTimes(2);
        expect(count).toBe('184');
    });

    it('should refresh counts on "refreshList" event of CovidCasePlannerTable', () => {
        const wrapper = createComponent();

        const spyListCounts = vi.spyOn(caseListApi, 'listCounts');
        spyListCounts.mockClear();

        wrapper.findAllComponents({ name: 'CovidCasePlannerTable' }).at(0).vm.$emit('refreshList');

        // Retrieved new counts
        expect(spyListCounts).toHaveBeenCalledTimes(1);
    });

    // New case button / form
    it('should not render "Case aanmaken" and FormCase component if user roles do not contain caseCreate', () => {
        const wrapper = createComponent(undefined, {
            user: {
                roles: null,
            },
        });

        expect(wrapper.findComponent({ ref: 'newCaseForm' }).exists()).toBe(false);
        expect(wrapper.find('.new-case-button').exists()).toBe(false);
    });

    it('should render "Case aanmaken" and FormCase component if user roles contain caseCreate', () => {
        const wrapper = createComponent(undefined, {
            permissions: [PermissionV1.VALUE_caseCreate],
        });

        expect(wrapper.findComponent({ ref: 'newCaseForm' }).exists()).toBe(true);
        expect(wrapper.find('[data-testid="new-case-button"]').exists()).toBe(true);
    });

    it('should open new case form after pressing case create button', async () => {
        const wrapper = createComponent(undefined, {
            permissions: [PermissionV1.VALUE_caseCreate],
        });

        (wrapper.vm.$refs.newCaseForm as any).open = vi.fn();
        const spyFormopen = vi.spyOn(wrapper.vm.$refs.newCaseForm as any, 'open');

        await wrapper.find('[data-testid="new-case-button"]').trigger('click');

        expect(spyFormopen).toHaveBeenCalledTimes(1);
    });

    it('should refresh table after case creation', async () => {
        const wrapper = createComponent(undefined, {
            permissions: [PermissionV1.VALUE_caseCreate],
        });

        wrapper.vm.tableRef.resetTable = vi.fn();

        const spyListCounts = vi.spyOn(caseListApi, 'listCounts');
        spyListCounts.mockClear();

        wrapper.findComponent({ ref: 'newCaseForm' }).vm.$emit('created');
        await flushCallStack();

        // Retrieved new counts
        expect(spyListCounts).toHaveBeenCalledTimes(1);
        // Reset referenced table
        expect(wrapper.vm.tableRef.resetTable).toHaveBeenCalledTimes(1);
    });

    // New/edit list modal
    it('should not show the edit modal if no editList is set', () => {
        const wrapper = createComponent({
            editList: null,
            lists,
        });

        expect(wrapper.findComponent({ name: 'BModal' }).exists()).toBe(false);
    });

    it('should show the edit modal if editList is set', () => {
        const wrapper = createComponent({
            editList: lists[0],
            lists,
        });

        expect(wrapper.findComponent({ name: 'BModal' }).exists()).toBe(true);
    });

    it('should pass title "Nieuwe lijst maken" to edit modal when creating a new list', () => {
        const wrapper = createComponent({
            editList: {},
            lists,
        });

        expect(wrapper.findComponent({ name: 'BModal' }).props().title).toBe('Nieuwe lijst maken');
    });

    it('should pass title "Lijst bewerken" to edit modal when editing a list', () => {
        const wrapper = createComponent({
            editList: lists[0],
            lists,
        });

        expect(wrapper.findComponent({ name: 'BModal' }).props().title).toBe('Lijst bewerken');
    });

    it('should pass ok-title "Lijst maken" to edit modal when creating a new list ', () => {
        const wrapper = createComponent({
            editList: {},
            lists,
        });

        expect(wrapper.findComponent({ name: 'BModal' }).props().okTitle).toBe('Lijst maken');
    });

    it('should pass ok-title "Opslaan" to edit modal when editing a list', () => {
        const wrapper = createComponent({
            editList: lists[0],
            lists,
        });

        expect(wrapper.findComponent({ name: 'BModal' }).props().okTitle).toBe('Opslaan');
    });

    it('should pass ok-only=true when creating a new list (no deletion allowed)', () => {
        const wrapper = createComponent({
            editList: {},
            lists,
        });

        expect(wrapper.findComponent({ name: 'BModal' }).props().okOnly).toBe(true);
    });

    it('should pass ok-only=false when editing a list (deletion allowed)', () => {
        const wrapper = createComponent({
            editList: lists[0],
            lists,
        });

        expect(wrapper.findComponent({ name: 'BModal' }).props().okOnly).toBe(false);
    });

    it('should delete list on "cancel" event and get counts', async () => {
        const wrapper = createComponent({
            editList: lists[0],
            lists,
        });

        const spyDeleteList = vi.spyOn(caseListApi, 'deleteList');
        const spyListCounts = vi.spyOn(caseListApi, 'listCounts');
        spyListCounts.mockClear();

        wrapper.findComponent({ name: 'BModal' }).vm.$emit('cancel');
        await wrapper.vm.$nextTick();

        // See mocked caseListApi.deleteList
        expect(spyDeleteList).toHaveBeenCalledTimes(1);
    });

    it('should show a modal and not get counts if deleteList throws an exception', async () => {
        const wrapper = createComponent({
            editList: lists[0],
            lists,
        });

        const modalShowMock = vi.fn();
        wrapper.vm.$modal = { show: modalShowMock };

        (caseListApi.deleteList as MockedFunction<typeof caseListApi.deleteList>).mockRejectedValueOnce('error');

        const spyDeleteList = vi.spyOn(caseListApi, 'deleteList');
        const spyListCounts = vi.spyOn(caseListApi, 'listCounts');
        spyListCounts.mockClear();

        wrapper.findComponent({ name: 'BModal' }).vm.$emit('cancel');

        // Await JS call stack for everything to execute
        await new Promise((resolve) => setTimeout(resolve, 0));

        // See mocked caseListApi.deleteList
        expect(spyDeleteList).toHaveBeenCalledTimes(1);

        // Should not have retrieved new counts
        expect(spyListCounts).toHaveBeenCalledTimes(0);

        // Should have opened modal
        expect(modalShowMock).toHaveBeenCalledTimes(1);
    });

    it('should create new list on "ok" event and hide list modal', async () => {
        const wrapper = createComponent({
            editList: { name: 'List name' },
            lists,
        });

        const spyBvModalHide = vi.spyOn(wrapper.vm.$bvModal, 'hide');
        const spyCreateList = vi.spyOn(caseListApi, 'createList');
        const spyUpdateList = vi.spyOn(caseListApi, 'updateList');
        const spyListCounts = vi.spyOn(caseListApi, 'listCounts');
        spyListCounts.mockClear();

        wrapper.findComponent({ name: 'BModal' }).vm.$emit('ok');

        // Await JS call stack for everything to execute
        await new Promise((resolve) => setTimeout(resolve, 0));

        expect(spyCreateList).toHaveBeenCalledTimes(1);
        expect(spyUpdateList).toHaveBeenCalledTimes(0);
        expect(spyListCounts).toHaveBeenCalledTimes(1);

        // Should have hidden list modal
        expect(spyBvModalHide).toHaveBeenNthCalledWith(1, 'selected-list-modal');
    });

    it('should create update existing list on "ok" event and hide list modal', async () => {
        const wrapper = createComponent({
            editList: lists[0],
            lists,
        });

        const spyBvModalHide = vi.spyOn(wrapper.vm.$bvModal, 'hide');
        const spyCreateList = vi.spyOn(caseListApi, 'createList');
        const spyUpdateList = vi.spyOn(caseListApi, 'updateList');
        const spyListCounts = vi.spyOn(caseListApi, 'listCounts');
        spyListCounts.mockClear();

        wrapper.findComponent({ name: 'BModal' }).vm.$emit('ok');

        // Await JS call stack for everything to execute :(
        await new Promise((resolve) => setTimeout(resolve, 0));

        expect(spyCreateList).toHaveBeenCalledTimes(0);
        expect(spyUpdateList).toHaveBeenCalledTimes(1);

        // Should not have retrieved new counts
        expect(spyListCounts).toHaveBeenCalledTimes(1);

        // Should have hidden list modal
        expect(spyBvModalHide).toHaveBeenNthCalledWith(1, 'selected-list-modal');
    });
});

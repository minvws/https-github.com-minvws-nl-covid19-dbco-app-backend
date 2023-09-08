import { shallowMount } from '@vue/test-utils';
import CovidCaseIntakeTable from './CovidCaseIntakeTable.vue';

import { createTestingPinia } from '@pinia/testing';
import type { StateChanger } from 'vue-infinite-loading';
import InfiniteLoading from 'vue-infinite-loading';
import { caseListApi } from '@dbco/portal-api';
import type { VueConstructor } from 'vue';
import { fakerjs, setupTest } from '@/utils/test';
import { fakePlannerCaseListItem } from '@/utils/__fakes__/fakePlannerCaseListItem';
import { ListFilterOptions } from '@dbco/portal-api/client/caseList.api';

const createComponent = setupTest(
    (localVue: VueConstructor, filter: ListFilterOptions, plannerState: object = {}, data: object = {}) => {
        return shallowMount<CovidCaseIntakeTable>(CovidCaseIntakeTable, {
            data: () => data,
            localVue,
            propsData: {
                filter,
            },
            stubs: {
                BTr: true,
            },
            pinia: createTestingPinia({
                initialState: {
                    planner: plannerState,
                },
                stubActions: false,
            }),
        });
    }
);

describe('CovidCaseIntakeTable.vue', () => {
    it('should show placeholder message when there are no items for the table', () => {
        // GIVEN there are no items for the table
        const plannerStoreData = {};

        // WHEN the table is rendered
        const wrapper = createComponent(ListFilterOptions.Unassigned, plannerStoreData);

        // THEN an element with a placeholder message should be visible
        const placeholder = wrapper.find('.empty-placeholder');
        expect(placeholder.exists()).toBe(true);
    });

    it('should show caseLabels filter', () => {
        // GIVEN the table is rendered in its default state
        const plannerStoreData = {};
        const wrapper = createComponent(ListFilterOptions.Unassigned, plannerStoreData);

        // WHEN the table is rendered
        const filters = wrapper.findAllComponents({ name: 'DbcoFilter' });

        // THEN a filter for caseLabels should be visible
        expect(filters.at(0).attributes('type')).toBe('caseLabels');
    });

    it('should get new items page when scrolling in table', async () => {
        const stateChanger: Partial<StateChanger> = {
            loaded: vi.fn(),
            complete: vi.fn(),
        };
        const getIntakeCases = vi.spyOn(caseListApi, 'getIntakeCases');

        // GIVEN the table is rendered in its default state
        const plannerStoreData = {};
        const wrapper = createComponent(ListFilterOptions.Unassigned, plannerStoreData);

        // WHEN there is still a page of items to load
        getIntakeCases.mockImplementation((page = 1) =>
            Promise.resolve({
                lastPage: 2,
                from: 0,
                to: 1,
                total: 2,
                currentPage: page,
                data: [fakePlannerCaseListItem],
            })
        );

        // AND a scroll triggers the loading of that page
        await wrapper.findComponent(InfiniteLoading).vm.$emit('infinite', stateChanger);
        await wrapper.vm.$nextTick();

        // THEN a new page with these items should be loaded
        expect(wrapper.vm.page).toBe(2);
        expect(wrapper.vm.items).toStrictEqual([fakePlannerCaseListItem]);

        // AND the loader has the status loaded
        expect(stateChanger.loaded).toBeCalledTimes(1);

        // Tested together to ensure that sequential loading works properly

        // WHEN a subsequent scroll triggers the loading of the last page
        await wrapper.findComponent(InfiniteLoading).vm.$emit('infinite', stateChanger);
        await wrapper.vm.$nextTick();

        // THEN a new page with these items should be loaded
        expect(wrapper.vm.page).toBe(2);
        expect(wrapper.vm.items).toStrictEqual([fakePlannerCaseListItem, fakePlannerCaseListItem]);

        // AND the loader has the status complete
        expect(stateChanger.complete).toBeCalledTimes(1);
    });

    // ageCondition1 | ageCondition2  | birthDate                                                     | expected
    it.each([
        [70, '', fakerjs.date.birthdate({ min: 70, max: 70, mode: 'age' }), true],
        [70, ' or above', fakerjs.date.birthdate({ min: 70, max: 100, mode: 'age' }), true],
        [16, '', fakerjs.date.birthdate({ min: 16, max: 16, mode: 'age' }), true],
        [16, ' or below', fakerjs.date.birthdate({ min: 1, max: 16, mode: 'age' }), true],
    ])(
        `should consider case as needing attention when calculated age is %f%s`,
        (_ageCondition, _ageCondition2, birthDate, expected) => {
            // GIVEN the table is rendered in its default state
            const plannerStoreData = {};
            const wrapper = createComponent(ListFilterOptions.Unassigned, plannerStoreData);

            // WHEN the age is calculated with the given birthdate
            const value = wrapper.vm.needsAttention(birthDate);

            // THEN needsAttention should return the expected value
            expect(value).toBe(expected);
        }
    );

    it('should NOT consider case as needing attention when calculated age is between 16 and 70', () => {
        // GIVEN the table is rendered in its default state
        const plannerStoreData = {};
        const wrapper = createComponent(ListFilterOptions.Unassigned, plannerStoreData);

        // WHEN the age is calculated with the given birthdate
        const value = wrapper.vm.needsAttention(fakerjs.date.birthdate({ min: 17, max: 69, mode: 'age' }));

        // THEN needsAttention should return false
        expect(value).toBe(false);
    });

    it('should return to default state when table is reset', async () => {
        // GIVEN the table is rendered in its default state
        const plannerStoreData = {};
        const wrapper = createComponent(ListFilterOptions.Unassigned, plannerStoreData);

        const defaultItems = wrapper.vm.items;
        const defaultPage = wrapper.vm.page;

        // AND the state is updated
        await wrapper.setData({ items: [fakePlannerCaseListItem], page: 3 });

        // WHEN the table is reset
        wrapper.vm.resetTable();

        // THEN the state should return to default
        expect(wrapper.vm.items).toStrictEqual(defaultItems);
        expect(wrapper.vm.page).toBe(defaultPage);
    });

    it('should update selectedFilter when the caseLabel filter is changed', () => {
        // GIVEN the table is rendered in its default state
        const plannerStoreData = {};
        const wrapper = createComponent(ListFilterOptions.Unassigned, plannerStoreData);

        // WHEN the caseLabel filter is changed
        wrapper.vm.updateFilter({ type: 'caseLabels', value: '1234' });

        // THEN the selected value should be stored in the state
        expect(wrapper.vm.selectedFilter.caseLabels).toStrictEqual('1234');
    });
});

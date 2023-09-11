import { caseApi, caseListApi } from '@dbco/portal-api';
import { AssignmentOptionType } from '@dbco/portal-api/assignment';
import i18n from '@/i18n/index';
import organisationStore from '@/store/organisation/organisationStore';
import { TestResultSourceV1, testResultSourceV1Options } from '@dbco/enum';
import { fakerjs, setupTest } from '@/utils/test';
import { fakePlannerCaseListItem } from '@/utils/__fakes__/fakePlannerCaseListItem';
import { createTestingPinia } from '@pinia/testing';
import { shallowMount } from '@vue/test-utils';
import type { BvModal, BvTableFieldArray } from 'bootstrap-vue';
import type { VueConstructor } from 'vue';
import type { StateChanger } from 'vue-infinite-loading';
import InfiniteLoading from 'vue-infinite-loading';
import Vuex from 'vuex';
import applyAssignment from '../ApplyAssignment';
import CovidCasePlannerTable from './CovidCasePlannerTable.vue';
import type { PlannerCaseListItem } from '@dbco/portal-api/caseList.dto';
import { ListFilterOptions } from '@dbco/portal-api/client/caseList.api';
import type { AxiosResponse } from 'axios';
import { format } from 'date-fns';

vi.mock('../ApplyAssignment', async () => {
    const actual = await vi.importActual<typeof import('../ApplyAssignment')>('../ApplyAssignment');
    return { default: vi.fn(actual.default) };
});

const mockCaseLabels = [
    {
        uuid: '9091ba51-5fd0-4e3c-b389-ad80f2d440f7',
        label: 'Zorg',
    },
    {
        uuid: '354d80fe-0987-4d2b-a896-40a4d7ad1cc5',
        label: 'Bewoner zorg',
    },
    {
        uuid: '398c819b-d2e3-4726-8b38-b55ec90b7d3c',
        label: 'Medewerker zorg',
    },
];

const mockOrganisations = [
    {
        uuid: '00000000-0000-0000-0000-000000000000',
        name: 'Demo GGD1',
    },
    {
        uuid: '0296ab48-1576-4262-af38-78e9ef06ed07',
        name: 'GGD Gelderland-Midden',
    },
    {
        uuid: '0535e4cd-af98-4113-999e-888f9fdf2a40',
        name: 'GGD Noord- en Oost Gelderland',
    },
    {
        uuid: '08eee942-53ef-4386-96b1-e70bd80d464b',
        name: 'GGD Hollands Noorden',
    },
];

const mockUserAssignmentOptions = [
    {
        type: 'option',
        label: 'Demo GGD1 Callcenter',
        isSelected: false,
        isEnabled: true,
        assignmentType: 'user',
        assignment: { assignedUserUuid: '00000000-0000-0000-0000-000000000014' },
    },
    {
        type: 'option',
        label: 'Demo GGD1 Compliance Officer',
        isSelected: false,
        isEnabled: true,
        assignmentType: 'user',
        assignment: { assignedUserUuid: '00000000-0000-0000-0000-000000000005' },
    },
    {
        type: 'option',
        label: 'Demo GGD1 Contextbeheerder',
        isSelected: false,
        isEnabled: true,
        assignmentType: 'user',
        assignment: { assignedUserUuid: '00000000-0000-0000-0000-100000000001' },
    },
];

const createComponent = setupTest(
    (localVue: VueConstructor, filter: ListFilterOptions, plannerState: object = {}, data: object = {}) => {
        const organisationStoreModule = {
            ...organisationStore,
            state: organisationStore.state,
        };

        return shallowMount<CovidCasePlannerTable>(CovidCasePlannerTable, {
            data: () => data,
            localVue,
            i18n,
            propsData: {
                filter,
            },
            store: new Vuex.Store({
                modules: {
                    organisation: organisationStoreModule,
                },
            }),
            pinia: createTestingPinia({
                initialState: {
                    planner: plannerState,
                },
                stubActions: false,
            }),
            stubs: {
                SvgVue: true,
                CovidCaseDeleteModal: {
                    template: '<div />',

                    methods: {
                        show: vi.fn(),
                    },
                },
                CovidCaseDetailModal: {
                    template: '<div />',

                    methods: {
                        show: vi.fn(),
                    },
                },
            },
        });
    }
);

describe('CovidCasePlannerTable.vue', () => {
    it('should show placeholder message when there are no items for the table', () => {
        // ARRANGE
        const plannerStoreData = {};

        const wrapper = createComponent(ListFilterOptions.Unassigned, plannerStoreData);

        const placeholder = wrapper.find('.empty-placeholder');

        // ASSERT
        expect(placeholder.exists()).toBe(true);
    });

    it('should show organisation filter', () => {
        // ARRANGE
        const plannerStoreData = {
            organisations: [
                {
                    uuid: '00000000-0000-0000-0000-000000000000',
                    name: 'Demo GGD1',
                },
                {
                    uuid: '0296ab48-1576-4262-af38-78e9ef06ed07',
                    name: 'GGD Gelderland-Midden',
                },
                {
                    uuid: '0535e4cd-af98-4113-999e-888f9fdf2a40',
                    name: 'GGD Noord- en Oost Gelderland',
                },
                {
                    uuid: '08eee942-53ef-4386-96b1-e70bd80d464b',
                    name: 'GGD Hollands Noorden',
                },
            ],
        };

        const wrapper = createComponent(ListFilterOptions.Unassigned, plannerStoreData);

        const filters = wrapper.findAllComponents({ name: 'DbcoFilter' });

        // ASSERT
        expect(filters.at(0).attributes('type')).toBe('organisation');
    });

    it('should show test result source filter when filter is set to unassigned', () => {
        // ARRANGE
        const plannerStoreData = {
            testResultSources: testResultSourceV1Options,
        };

        const wrapper = createComponent(ListFilterOptions.Unassigned, plannerStoreData);

        const filters = wrapper.findAllComponents({ name: 'DbcoFilter' });

        // ASSERT
        expect(filters.at(1).attributes('type')).toBe('testResultSource');
    });

    it('should NOT show label filter when filter is set to archived', () => {
        // ARRANGE
        const plannerStoreData = {
            caseLabels: mockCaseLabels,
        };

        const wrapper = createComponent(ListFilterOptions.Archived, plannerStoreData);

        // ASSERT
        expect(wrapper.find('[type=label]').exists()).toBe(false);
    });

    it.each([
        ['organisation', 'IS NOT', ListFilterOptions.Outsourced, 'organisations', { organisations: mockOrganisations }],
        ['label', 'IS NOT', ListFilterOptions.Archived, 'caseLabels', { caseLabels: mockCaseLabels }],
        [
            'userAssignment',
            'IS',
            ListFilterOptions.Assigned,
            'userAssignmentOptions',
            { userAssignmentOptions: mockUserAssignmentOptions },
        ],
    ])(
        'DbcoFilter %#: show show DbcoFilter with type "%s" when ListFilterOptions prop %s set to "%s" and "%s" are present',
        (type, ListFilterState, ListFilterOption, stateProperty, plannerStoreData) => {
            // ARRANGE
            const wrapper = createComponent(ListFilterOptions.Assigned, plannerStoreData);
            const filters = wrapper.findAllComponents({ name: 'DbcoFilter' });

            // ASSERT
            expect(filters.at(0).attributes('type')).toBe(type);
        }
    );

    it('should set organisation in Store and show organisationEditModal when changeOrganisation action is triggered', async () => {
        // ARRANGE
        const plannerStoreData = {};
        const givenCase = fakePlannerCaseListItem();
        const wrapper = createComponent(ListFilterOptions.Assigned, plannerStoreData, {
            items: [givenCase],
        });
        (wrapper.vm.$refs.caseOrganisationEditModal as unknown as BvModal).show = vi.fn();
        const showSpy = vi.spyOn(wrapper.vm.$refs.caseOrganisationEditModal as unknown as BvModal, 'show');
        const spyOnCommit = vi.spyOn(wrapper.vm.$store, 'commit');

        await wrapper.vm.changeOrganisation(givenCase);

        // ASSERT
        expect(spyOnCommit).toHaveBeenCalledWith('organisation/SET_CURRENT', givenCase.organisation, undefined);
        expect(showSpy).toHaveBeenCalledTimes(1);
    });

    it('should get second items page when scrolling in table', async () => {
        // ARRANGE
        const stateChanger: Partial<StateChanger> = {
            loaded: vi.fn(),
            complete: vi.fn(),
        };
        const getListCases = vi.spyOn(caseListApi, 'getListCases');
        getListCases.mockImplementation(
            (
                list: string | undefined,
                listFilter: ListFilterOptions,
                filterParams: Record<string, string | Record<string, number> | null>,
                page = 1
            ) => {
                const lastPage = 2;

                return Promise.resolve({
                    lastPage,
                    from: 0,
                    to: 1,
                    total: 2,
                    currentPage: page,
                    data: page === lastPage ? [] : [fakePlannerCaseListItem() as PlannerCaseListItem],
                });
            }
        );
        const plannerStoreData = {};

        const wrapper = createComponent(ListFilterOptions.Unassigned, plannerStoreData);

        // First page
        await wrapper.findComponent(InfiniteLoading).vm.$emit('infinite', stateChanger);
        await wrapper.vm.$nextTick();

        expect(stateChanger.loaded).toBeCalledTimes(1);

        // Last Page
        await wrapper.findComponent(InfiniteLoading).vm.$emit('infinite', stateChanger);
        await wrapper.vm.$nextTick();

        // ASSERT
        expect(stateChanger.complete).toBeCalledTimes(1);
    });

    it('should set archiveable to true if a closable item is selected', () => {
        // ARRANGE
        const plannerStoreData = {};
        const givenCase = fakePlannerCaseListItem({ isClosable: true });
        const wrapper = createComponent(ListFilterOptions.Assigned, plannerStoreData, {
            items: [givenCase],
        });

        // ASSERT
        expect(wrapper.vm.archiveable([givenCase.uuid])).toBe(true);
    });

    it('should clear casesToArchive and casesToReopen, call resetTable and emit "refreshList" when an action is done', () => {
        // ARRANGE
        const plannerStoreData = {};
        const wrapper = createComponent(ListFilterOptions.Assigned, plannerStoreData, {
            casesToArchive: ['1234', '5678'],
            casesToReopen: ['1234', '5678'],
        });

        const resetSpy = vi.spyOn(wrapper.vm, 'resetTable');

        wrapper.vm.onActionDone();

        // ASSERT
        expect(wrapper.vm.casesToArchive).toStrictEqual([]);
        expect(wrapper.vm.casesToReopen).toStrictEqual([]);
        expect(resetSpy).toHaveBeenCalledTimes(1);
        expect(wrapper.emitted('refreshList')).toBeTruthy();
    });

    it('should update selectedCase in store and show caseForm when openCaseForm action is triggered', async () => {
        // ARRANGE
        const plannerStoreData = {
            selectedCase: fakePlannerCaseListItem({ uuid: '5678' }),
        };
        const givenCase = fakePlannerCaseListItem();
        const wrapper = createComponent(ListFilterOptions.Assigned, plannerStoreData, {
            items: [givenCase],
        });
        (wrapper.vm.$refs.caseForm as unknown as AnyObject).open = vi.fn(); // change AnyObject to InstanceType<typeof FormCase> after ts refactor of component.
        const showSpy = vi.spyOn(wrapper.vm.$refs.caseForm as unknown as AnyObject, 'open');

        await wrapper.vm.openCaseForm(givenCase);

        // ASSERT
        expect(wrapper.vm.selectedCase).toStrictEqual(givenCase);
        expect(showSpy).toHaveBeenCalledTimes(1);
    });

    it('should calculate age with dateOfBirth when it is present', () => {
        // ARRANGE
        const plannerStoreData = {};
        const wrapper = createComponent(ListFilterOptions.Assigned, plannerStoreData, {
            items: [fakePlannerCaseListItem()],
        });
        const age = fakerjs.number.int({ min: 18, max: 105 });
        const birthDate = fakerjs.date.birthdate({ min: age, max: age, mode: 'age' });

        // ASSERT
        expect(wrapper.vm.age(format(birthDate, 'yyyy-MM-dd'))).toBe(age);
    });

    it.each([
        [17, 69, false],
        [0, 16, true],
        [70, 105, true],
    ])(
        'needsAttention age range %#: given an age between %d and %d needsAttention should be %s',
        (minAge, maxAge, needsAttention) => {
            // ARRANGE
            const plannerStoreData = {};
            const wrapper = createComponent(ListFilterOptions.Assigned, plannerStoreData, {
                items: [fakePlannerCaseListItem()],
            });
            const birthDate = format(fakerjs.date.birthdate({ min: minAge, max: maxAge, mode: 'age' }), 'yyyy-MM-dd');
            const value = wrapper.vm.needsAttention(birthDate);

            // ASSERT
            expect(value).toBe(needsAttention);
        }
    );

    it('should not flag case as needing attention if birthdate is null', () => {
        const wrapper = createComponent(
            ListFilterOptions.Assigned,
            {},
            {
                items: [fakePlannerCaseListItem()],
            }
        );
        expect(wrapper.vm.needsAttention(null)).toBe(false);
    });

    it.each([
        [
            'caseListName',
            fakePlannerCaseListItem({
                assignedCaseList: {
                    name: 'caseListName',
                },
            }),
        ],
        [
            '',
            fakePlannerCaseListItem({
                assignedCaseList: {},
            }),
        ],
        [
            '(userName)',
            fakePlannerCaseListItem({
                assignedCaseList: {},
                assignedUser: {
                    name: 'userName',
                },
            }),
        ],
        [
            'caseListName (userName)',
            fakePlannerCaseListItem({
                assignedCaseList: {
                    name: 'caseListName',
                },
                assignedUser: {
                    name: 'userName',
                },
            }),
        ],
        [
            '',
            fakePlannerCaseListItem({
                assignedUser: {},
            }),
        ],
        [
            'userName',
            fakePlannerCaseListItem({
                assignedUser: {
                    name: 'userName',
                },
            }),
        ],
        [
            '',
            fakePlannerCaseListItem({
                assignedOrganisation: {},
            }),
        ],
        [
            'organisationName',
            fakePlannerCaseListItem({
                assignedOrganisation: {
                    name: 'organisationName',
                },
            }),
        ],
        [
            'organisationName (Wachtrij)',
            fakePlannerCaseListItem({
                assignedCaseList: {
                    isQueue: true,
                },
                assignedOrganisation: {
                    name: 'organisationName',
                },
            }),
        ],
        [
            '(Wachtrij)',
            fakePlannerCaseListItem({
                assignedCaseList: {
                    isQueue: true,
                },
                assignedOrganisation: {},
            }),
        ],
        [
            'organisationName (bij BCO-er)',
            fakePlannerCaseListItem({
                assignedUser: {},
                assignedOrganisation: {
                    name: 'organisationName',
                },
            }),
        ],
        [
            '(bij BCO-er)',
            fakePlannerCaseListItem({
                assignedUser: {},
                assignedOrganisation: {},
            }),
        ],
        [`${i18n.t('components.covidCasePlannerTable.assignment.default')}`, null],
    ])('assigneeTitle %#: should update assigneeTitle based on case assignment', (expectedTitle, item) => {
        // ARRANGE
        const plannerStoreData = {};
        const wrapper = createComponent(ListFilterOptions.Assigned, plannerStoreData);

        // ASSERT
        expect(wrapper.vm.getAssigneeTitle(item)).toStrictEqual(expectedTitle);
    });

    it('should show tooltip for outsourced cases when outsource organisation name is present', () => {
        // ARRANGE
        const plannerStoreData = {};
        const wrapper = createComponent(ListFilterOptions.Assigned, plannerStoreData, {
            items: [fakePlannerCaseListItem()],
        });

        const mockItem1 = fakePlannerCaseListItem({
            wasOutsourced: true,
            wasOutsourcedToOrganisation: { name: 'organisationName' },
        });
        const mockItem2 = fakePlannerCaseListItem({
            wasOutsourced: true,
            wasOutsourcedToOrganisation: null,
        });

        // ASSERT
        expect(wrapper.vm.getOutsourcedOrganisationTooltip(mockItem1)).toStrictEqual(
            'Deze case is teruggekomen van organisationName'
        );
        expect(wrapper.vm.getOutsourcedOrganisationTooltip(mockItem2)).toStrictEqual('');
    });

    it('should update casesToArchive and show caseArchiveModal when archiveCases action is triggered', async () => {
        // ARRANGE
        const plannerStoreData = {};
        const wrapper = createComponent(ListFilterOptions.Assigned, plannerStoreData);
        (wrapper.vm.$refs.caseArchiveModal as unknown as BvModal).show = vi.fn();
        const showSpy = vi.spyOn(wrapper.vm.$refs.caseArchiveModal as unknown as BvModal, 'show');

        await wrapper.vm.archiveCases(['12345']);

        // ASSERT
        expect(wrapper.vm.casesToArchive).toStrictEqual(['12345']);
        expect(showSpy).toHaveBeenCalledTimes(1);
    });

    it('should apply assignment and clean up when done', async () => {
        // ARRANGE
        const plannerStoreData = {};
        const wrapper = createComponent(ListFilterOptions.Assigned, plannerStoreData, { selected: ['1234'] });

        await wrapper.vm.assigned({
            option: {
                type: AssignmentOptionType.USER,
            },
        });

        // ASSERT
        expect(applyAssignment).toHaveBeenCalledWith(
            [],
            { option: { type: AssignmentOptionType.USER } },
            ListFilterOptions.Assigned,
            undefined
        );
        expect(wrapper.vm.selected).toStrictEqual([]);
        expect(wrapper.emitted('refreshList')).toBeTruthy();
    });

    it('should show actions for case when is closable, deletable or editable', () => {
        // ARRANGE
        const plannerStoreData = {};
        const givenCase = fakePlannerCaseListItem({ isClosable: true });
        const wrapper = createComponent(ListFilterOptions.Assigned, plannerStoreData, {
            items: [givenCase],
        });

        // ASSERT
        expect(wrapper.vm.archiveable([givenCase.uuid])).toBe(true);
    });

    it('should show caseAssignConflictModal when assign errors are present', async () => {
        // ARRANGE
        const plannerStoreData = {};
        const wrapper = createComponent(ListFilterOptions.Assigned, plannerStoreData);
        (wrapper.vm.$refs.caseAssignConflictModal as unknown as BvModal).show = vi.fn();
        const showSpy = vi.spyOn(wrapper.vm.$refs.caseAssignConflictModal as unknown as BvModal, 'show');

        await wrapper.vm.showAssignErrors('description', { assignmentStatus: 'erroneous', caseId: '1234' });

        // ASSERT
        expect(showSpy).toHaveBeenCalledWith('description', { assignmentStatus: 'erroneous', caseId: '1234' });
    });

    it('should clear selected items when select all is toggled off', async () => {
        // ARRANGE
        const plannerStoreData = {};
        const wrapper = createComponent(ListFilterOptions.Assigned, plannerStoreData, { selected: ['1234'] });

        await wrapper.vm.toggleAll(false);

        // ASSERT
        expect(wrapper.vm.selected).toStrictEqual([]);
    });

    it('should update selected items when select all is toggled on', async () => {
        // ARRANGE
        const plannerStoreData = {};
        const givenCase = fakePlannerCaseListItem({ isEditable: true });
        const wrapper = createComponent(ListFilterOptions.Assigned, plannerStoreData, {
            items: [givenCase],
        });

        await wrapper.vm.toggleAll(true);

        // ASSERT
        expect(wrapper.vm.selected).toStrictEqual([givenCase.uuid]);
    });

    it('should select item when its checkbox is toggled on', async () => {
        // ARRANGE
        const plannerStoreData = {};
        const wrapper = createComponent(ListFilterOptions.Assigned, plannerStoreData, { selected: ['1234'] });

        await wrapper.vm.toggleCheckbox('5678');

        // ASSERT
        expect(wrapper.vm.selected).toStrictEqual(['1234', '5678']);
    });

    it('should deselect item when its checkbox is toggled off', async () => {
        // ARRANGE
        const plannerStoreData = {};
        const wrapper = createComponent(ListFilterOptions.Assigned, plannerStoreData, { selected: ['1234', '5678'] });

        await wrapper.vm.toggleCheckbox('5678');

        // ASSERT
        expect(wrapper.vm.selected).toStrictEqual(['1234']);
    });

    it('should call api and reset table when a case priority is updated', async () => {
        // ARRANGE
        const updatePriority = vi
            .spyOn(caseApi, 'updatePriority')
            .mockImplementationOnce(() => Promise.resolve({} as AxiosResponse));
        const plannerStoreData = {};
        const wrapper = createComponent(ListFilterOptions.Assigned, plannerStoreData, { selected: ['1234', '5678'] });
        const resetSpy = vi.spyOn(wrapper.vm, 'resetTable');

        await wrapper.vm.updatePriority('high');

        // ASSERT
        expect(updatePriority).toHaveBeenCalledWith({ cases: ['1234', '5678'], priority: 'high' });
        expect(resetSpy).toHaveBeenCalledTimes(1);
    });

    it('should update casesToReopen and show caseReopenModal when reopenCases action is triggered', async () => {
        // ARRANGE
        const plannerStoreData = {};
        const wrapper = createComponent(ListFilterOptions.Assigned, plannerStoreData);
        (wrapper.vm.$refs.caseReopenModal as unknown as BvModal).show = vi.fn();
        const showSpy = vi.spyOn(wrapper.vm.$refs.caseReopenModal as unknown as BvModal, 'show');
        const givenCase = fakePlannerCaseListItem();

        await wrapper.vm.reopenCases([givenCase]);

        // ASSERT
        expect(wrapper.vm.casesToReopen).toStrictEqual([givenCase]);
        expect(showSpy).toHaveBeenCalledTimes(1);
    });

    it('should update selectedCase when a table row is clicked', async () => {
        // ARRANGE
        const plannerStoreData = {};
        const targetElement = document.createElement('td');
        const wrapper = createComponent(ListFilterOptions.Assigned, plannerStoreData, { selected: ['5678'] });
        wrapper.vm.$refscaseDetailModal = { show: vi.fn() };
        const givenCase = fakePlannerCaseListItem();

        await wrapper.vm.rowClicked(givenCase, 0, {
            target: targetElement,
        });

        // ASSERT
        expect(wrapper.vm.selectedCase).toStrictEqual(givenCase);
    });

    it('should show modal when delete action is triggered on a case', async () => {
        // ARRANGE
        const plannerStoreData = {};
        const wrapper = createComponent(ListFilterOptions.Assigned, plannerStoreData, { selected: ['5678'] });
        (wrapper.vm.$refs.covidCaseDeleteModal as unknown as BvModal).show = vi.fn();
        const showSpy = vi.spyOn(wrapper.vm.$refs.covidCaseDeleteModal as unknown as BvModal, 'show');

        await wrapper.vm.openCovidCaseDeleteModal('5678');

        // ASSERT
        expect(showSpy).toHaveBeenCalledTimes(1);
    });

    it('should show case status filter', () => {
        // ARRANGE
        const wrapper = createComponent(ListFilterOptions.Unassigned);

        const filters = wrapper.findAllComponents({ name: 'DbcoFilter' });

        // ASSERT
        expect(filters.at(0).attributes('type')).toBe('statusIndexContactTracing');
    });

    it.each([
        [ListFilterOptions.Unassigned, false],
        [ListFilterOptions.Queued, false],
        [ListFilterOptions.Outsourced, false],
        [ListFilterOptions.Assigned, false],
        [ListFilterOptions.Completed, true],
        [ListFilterOptions.Archived, false],
    ])('assigned user exists in the table fields on %s is expected to be %s', (filter, visible) => {
        const wrapper = createComponent(filter);
        const fields: BvTableFieldArray = wrapper.vm.fields;
        const hasLastAssignedUserName = !!fields.find((x) => typeof x !== 'string' && x.key === 'lastAssignedUserName');

        expect(hasLastAssignedUserName).toBe(visible);
    });

    it('should correctly format test result source', () => {
        // ARRANGE
        const givenCase1: PlannerCaseListItem = fakePlannerCaseListItem({
            testResults: [TestResultSourceV1.VALUE_meldportaal],
        }) as PlannerCaseListItem;
        const givenCase2: PlannerCaseListItem = fakePlannerCaseListItem() as PlannerCaseListItem;
        const givenCase3: PlannerCaseListItem = fakePlannerCaseListItem({
            testResults: [TestResultSourceV1.VALUE_coronit, TestResultSourceV1.VALUE_manual],
        }) as PlannerCaseListItem;
        const givenCase4: PlannerCaseListItem = fakePlannerCaseListItem({
            testResults: [TestResultSourceV1.VALUE_coronit, TestResultSourceV1.VALUE_coronit],
        }) as PlannerCaseListItem;

        const wrapper = createComponent(ListFilterOptions.Unassigned);

        // ASSERT
        expect(wrapper.vm.formattedTestResultSource(givenCase1)).toBe(testResultSourceV1Options.meldportaal);
        expect(wrapper.vm.formattedTestResultSource(givenCase2)).toBe('-');
        expect(wrapper.vm.formattedTestResultSource(givenCase3)).toBe(i18n.t('shared.test_result_source_multiple'));
        expect(wrapper.vm.formattedTestResultSource(givenCase4)).toBe(testResultSourceV1Options.coronit);
    });

    it('should sort table when sortable header is clicked', async () => {
        // ARRANGE
        const plannerStoreData = {};
        const wrapper = createComponent(ListFilterOptions.Unassigned, plannerStoreData, {
            items: [fakePlannerCaseListItem(), fakePlannerCaseListItem()],
        });

        const table = wrapper.findComponent({ name: 'BTable' });
        await table.vm.$emit('sort-changed', { sortBy: 'priority' });

        // ASSERT
        expect(wrapper.vm.$data.sortBy).toBe('priority');
    });
});

import { caseApi } from '@dbco/portal-api';
import i18n from '@/i18n/index';
import userInfoStore from '@/store/userInfo/userInfoStore';
import { BcoStatusV1, TestResultSourceV1, testResultSourceV1Options } from '@dbco/enum';
import { setupTest } from '@/utils/test';
import { fakePlannerCaseListItem } from '@/utils/__fakes__/fakePlannerCaseListItem';
import { createTestingPinia } from '@pinia/testing';
import { mount } from '@vue/test-utils';
import type { VueConstructor } from 'vue';
import Vuex from 'vuex';
import CovidCaseDetails from './CovidCaseDetails.vue';
import type { PlannerCaseListItem } from '@dbco/portal-api/caseList.dto';

const caseLabels = [
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
    {
        uuid: '7d3e0ab8-da26-40a4-b72c-bac322d0edc9',
        label: 'School',
    },
    {
        uuid: '4fcfa332-693e-40f5-bdd6-65a7dedab028',
        label: 'Contactberoep',
    },
    {
        uuid: '047f8fd2-3036-4713-a0ee-1059eb394c0e',
        label: 'Maatschappelijke instelling',
    },
    {
        uuid: '2d05afd9-d0ad-4c79-a33b-54952eff85a0',
        label: 'Scheepvaart opvarende',
    },
    {
        uuid: '6513fddb-ad49-4e3d-b084-0915b2e7e501',
        label: 'Vluchten',
    },
    {
        uuid: '92a8d3e4-4c3c-4121-88db-bf8c4778d687',
        label: 'Buitenland',
    },
    {
        uuid: 'ef1aff30-6d6f-4fc5-bf9f-3d187950f7f6',
        label: 'VOI/VOC',
    },
    {
        uuid: 'b59fd6cd-41fb-4526-8ab9-16f102244659',
        label: 'Herhaaluitslag',
    },
    {
        uuid: '4a1d715a-cebe-40a5-b424-3fef6a3c408d',
        label: 'Buiten meldportaal/CoronIT',
    },
    {
        uuid: 'cb7d9b0b-e324-4828-9d63-5190ceff8b0b',
        label: 'Onvolledige gegevens',
    },
    {
        uuid: '293d0656-c18c-4b01-924c-81b44c45b1dd',
        label: 'Index weet uitslag niet',
    },
    {
        uuid: '17670515-232d-48be-ac1d-22c14a6903c5',
        label: 'Uitbraak',
    },
    {
        uuid: '599660f0-1705-4759-b5a7-0d1cacde2d8a',
        label: 'Osiris melding mislukt',
    },
];

const createComponent = setupTest((localVue: VueConstructor, userInfoState: object = {}, plannerState: object = {}) => {
    const userInfoStoreModule = {
        ...userInfoStore,
        state: {
            ...userInfoStore.state,
            ...userInfoState,
        },
    };

    return mount(CovidCaseDetails, {
        localVue,
        i18n,
        propsData: {
            assigneeTitle: 'Test',
        },
        stubs: {
            BModal: true,
            CovidCaseDetails: true,
            CovidCaseHistory: true,
        },
        store: new Vuex.Store({
            modules: {
                userInfo: userInfoStoreModule,
            },
        }),
        pinia: createTestingPinia({
            initialState: {
                planner: plannerState,
            },
            stubActions: false,
        }),
    });
});

describe('CovidCaseDetails.vue', () => {
    it('should be visible', () => {
        // ARRANGE
        const storeData = {
            hasPermission: [],
        };

        // ARRANGE
        const plannerStoreData = {
            caseLabels,
            selectedCase: fakePlannerCaseListItem(),
        };

        const wrapper = createComponent(storeData, plannerStoreData);

        // ASSERT
        expect(wrapper.exists()).toBe(true);
    });

    it('should not render assign dropdown when the case is closed', () => {
        // ARRANGE
        const storeData = {
            hasPermission: [],
        };

        const closedCase = fakePlannerCaseListItem({ bcoStatus: BcoStatusV1.VALUE_archived });

        // ARRANGE
        const plannerStoreData = {
            caseLabels,
            selectedCase: closedCase,
        };

        const wrapper = createComponent(storeData, plannerStoreData);
        const assignDropdown = wrapper.find('.b-dropdown');

        // ASSERT
        expect(assignDropdown.exists()).toBe(false);
    });

    it('should call updatePlannerCaseMeta when selectedCaseLabels is being updated', async () => {
        // ARRANGE
        const storeData = {
            hasPermission: [],
        };

        const selectedCaseLabels = [caseLabels[0], caseLabels[3]];

        vi.spyOn(caseApi, 'updatePlannerCaseMeta').mockReturnValueOnce(
            Promise.resolve({
                data: {
                    caseLabels: selectedCaseLabels,
                },
            })
        );

        // ARRANGE
        const plannerStoreData = {
            caseLabels,
            selectedCase: { ...fakePlannerCaseListItem({ isEditable: true }) } as PlannerCaseListItem,
        };

        const wrapper = createComponent(storeData, plannerStoreData);
        await wrapper.setData({
            selectedCaseLabels: [caseLabels[0].uuid, caseLabels[1].uuid],
        });
        await wrapper.vm.$nextTick();

        // ASSERT
        expect(wrapper.vm.selectedCase.caseLabels).toEqual(expect.arrayContaining(selectedCaseLabels));
    });

    it('should call caseApi.addCaseNote when addCaseNote is being fired', async () => {
        vi.spyOn(caseApi, 'addCaseNote').mockImplementationOnce(() => Promise.resolve());

        // ARRANGE
        const storeData = {
            hasPermission: [],
        };

        // ARRANGE
        const plannerStoreData = {
            caseLabels,
            selectedCase: fakePlannerCaseListItem(),
        };

        const spyUpdateList = vi.spyOn(caseApi, 'addCaseNote');
        spyUpdateList.mockClear();

        const wrapper = createComponent(storeData, plannerStoreData);

        await wrapper.find('textarea').setValue('this is a new note.');
        await wrapper.vm.addCaseNote();

        await wrapper.vm.$nextTick();

        // ASSERT
        expect(spyUpdateList).toHaveBeenCalledTimes(1);
        expect(wrapper.vm.caseNote).toEqual('');
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

        // ARRANGE
        const storeData = {
            hasPermission: [],
        };

        // ARRANGE
        const plannerStoreData = {
            caseLabels,
            selectedCase: fakePlannerCaseListItem(),
        };

        const wrapper = createComponent(storeData, plannerStoreData);

        // ASSERT
        expect(wrapper.vm.formattedTestResultSource(givenCase1)).toBe(testResultSourceV1Options.meldportaal);
        expect(wrapper.vm.formattedTestResultSource(givenCase2)).toBe('-');
        expect(wrapper.vm.formattedTestResultSource(givenCase3)).toBe(i18n.t('shared.test_result_source_multiple'));
        expect(wrapper.vm.formattedTestResultSource(givenCase4)).toBe(testResultSourceV1Options.coronit);
    });
});

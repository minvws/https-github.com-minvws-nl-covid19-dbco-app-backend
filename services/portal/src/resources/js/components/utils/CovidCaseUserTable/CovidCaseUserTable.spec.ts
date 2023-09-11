import { createLocalVue, shallowMount } from '@vue/test-utils';
import CovidCaseUserTable from './CovidCaseUserTable.vue';
import BootstrapVue from 'bootstrap-vue';
import type { UntypedWrapper } from '@/utils/test';

// We need a window object that let's us set the url in the test. normally this isn't available during jest runs.
// Using this code this object will be used and we can look at what gets passed to the href.

const url = 'http://dummy.com';

vi.stubGlobal('location', {
    value: {
        href: url,
    },
});

const cases = [
    {
        uuid: '6a3241b2-c415-41a1-aec8-2659e8f30799',
        owner: '00000000-0000-0000-0000-000000000001',
        organisationUuid: '00000000-0000-0000-0000-000000000000',
        assignedUserUuid: '10000000-0000-0000-0000-000000000003',
        assignedOrganisationUuid: '10000000-0000-0000-0000-000000000000',
        assignedCaseListUuid: null,
        assignedName: 'Demo LS1 Dossierkwaliteit',
        isApproved: false,
        bcoStatus: 'completed',
        indexStatus: 'initial',
        bcoPhase: '1a',
        name: 'Jaap Groen',
        dateOfSymptomOnset: '2022-01-03T00:00:00.000000Z',
        caseLabels: [
            {
                uuid: 'a56fa4ac-3332-423f-9087-a437f0c61651',
                code: 'external',
                label: 'Buiten meldportaal/CoronIT',
                is_selectable: true,
                created_at: '2022-01-17T13:12:12.000000Z',
                updated_at: '2022-01-17T13:12:12.000000Z',
                pivot: {
                    case_uuid: '6a3241b2-c415-41a1-aec8-2659e8f30799',
                    case_label_uuid: 'a56fa4ac-3332-423f-9087-a437f0c61651',
                },
            },
        ],
        organisationLabel: null,
        assignedOrganisationLabel: null,
        updatedAt: '2022-01-31T09:29:54.000000Z',
        tasks: [],
        organisation: {
            uuid: '00000000-0000-0000-0000-000000000000',
            abbreviation: 'GGD1',
            externalId: '00000',
            hpZoneCode: null,
            name: 'Demo GGD1',
            phoneNumber: null,
            bcoPhase: '1a',
        },
        editCommand: 'http://localhost:8084/editcase/6a3241b2-c415-41a1-aec8-2659e8f30799',
    },
];

vi.mock('@dbco/portal-api/client/case.api', () => ({
    getCases: vi.fn(() =>
        Promise.resolve({
            cases: {
                data: cases,
            },
        })
    ),
}));

describe('CovidCaseUserTable.vue', () => {
    const localVue = createLocalVue();
    localVue.use(BootstrapVue);

    const getWrapper = () => {
        return shallowMount(CovidCaseUserTable, {
            localVue,
            stubs: {
                InfiniteLoading: true,
            },
        }) as UntypedWrapper;
    };

    it('should load', () => {
        // ARRANGE
        const wrapper = getWrapper();

        // ASSERT
        expect(wrapper.find('div').exists()).toBe(true);
    });

    // This test is under construction, refactor the InfiniteLoading component before writing this
    it.skip('should load casese via "caseApi.getCases()" if InfiniteLoader is scrolled into view, and infiniteHandler() is being called', async () => {
        const wrapper = getWrapper();

        wrapper.vm.$refs.infiniteLoading.stateChanger = vi.fn();
        wrapper.vm.$refs.infiniteLoading.stateChanger.loaded = vi.fn();
        wrapper.vm.$refs.infiniteLoading.stateChanger.complete = vi.fn();

        await wrapper.vm.infiniteHandler();

        await wrapper.vm.$nextTick();

        const placeRows = wrapper.findAll('[data-testid="case-table-row"]');

        expect(placeRows.length).toBe(1);
    });

    it('should reset cases array on .resetTable()', async () => {
        const wrapper = getWrapper();

        await wrapper.vm.resetTable();

        const placeRows = wrapper.findAll('[data-testid="case-table-row"]');
        expect(placeRows.length).toBe(0);
    });

    it('expect window.location object to change on .navigate()', async () => {
        const wrapper = getWrapper();

        const path = '/editcase/245284d0-5ac6-4dac-b7fe-4be4230bb5f5';
        await wrapper.vm.navigate(path);

        expect(window.location).toContain(path);
    });
});

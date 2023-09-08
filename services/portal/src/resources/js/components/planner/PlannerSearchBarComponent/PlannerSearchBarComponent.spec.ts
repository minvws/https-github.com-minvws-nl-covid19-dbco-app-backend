import { shallowMount } from '@vue/test-utils';
import PlannerSearchBarComponent from './PlannerSearchBarComponent.vue';
import { PlannerView } from '@dbco/portal-api/caseList.dto';
import { createTestingPinia } from '@pinia/testing';
import { setupTest } from '@/utils/test';
import type { VueConstructor } from 'vue';
import { PiniaVuePlugin } from 'pinia';

const searchResult = {
    uuid: 'a434c4f4-ab82-4279-951b-8f5e542a40da',
    caseId: '12345678',
    contactsCount: null,
    dateOfBirth: '1950-01-01',
    dateOfTest: '2021-09-29',
    statusIndexContactTracing: null,
    statusExplanation: '',
    createdAt: '2021-10-04T19:49:26Z',
    updatedAt: '2021-10-05T09:43:18Z',
    organisation: {
        uuid: '00000000-0000-0000-0000-000000000000',
        abbreviation: 'GGD1',
        name: 'Demo GGD1',
        isCurrent: true,
    },
    assignedOrganisation: null,
    assignedCaseList: null,
    assignedUser: {
        uuid: '00000000-0000-0000-0000-000000000001',
        isCurrent: false,
        name: 'Demo GGD1 Gebruiker',
    },
    isEditable: false,
    isDeletable: false,
    label: null,
    plannerView: 'assigned',
    wasOutsourced: false,
    wasOutsourcedToOrganisation: null,
};

const createComponent = setupTest((localVue: VueConstructor, data: object = {}, plannerState: object = {}) => {
    localVue.use(PiniaVuePlugin);
    return shallowMount(PlannerSearchBarComponent, {
        localVue,
        data: () => data,
        stubs: {
            CovidCaseDetailModal: true,
        },
        pinia: createTestingPinia({
            initialState: {
                planner: plannerState,
            },
            stubActions: false,
        }),
        attachTo: document.body,
    });
});

describe('PlannerSearchBarComponent.vue', () => {
    it('should fill and clear input', async () => {
        const data = {
            search: 12345678,
        };
        const wrapper = createComponent(data, {});
        const clear = wrapper.find('.icon--close');
        await clear.trigger('click');

        expect(wrapper.vm.search).toBe(null);
    });

    it('should search cases and find result', () => {
        const data = {
            result: searchResult,
            searched: true,
        };

        const wrapper = createComponent(data);
        const result = wrapper.find('.search-result');

        expect(result.exists()).toBe(true);
        expect(result.find('span[data-testid="searchResultCaseId"]').text()).toBe('12345678');
    });

    it('should search cases and find no result', () => {
        const data = {
            result: null,
            searched: true,
        };

        const wrapper = createComponent(data);
        const result = wrapper.find('.search-no-result');

        expect(result.text()).toBe('Geen resultaten gevonden');
    });

    it.each([
        ['unknown', 'Niet bekend'],
        [PlannerView.UNASSIGNED, 'Te verdelen'],
        [PlannerView.QUEUED, 'Wachtrij'],
        [PlannerView.OUTSOURCED, 'Uitbesteed'],
        [PlannerView.ASSIGNED, 'Toegewezen'],
        [PlannerView.COMPLETED, 'Te controleren'],
        [PlannerView.ARCHIVED, 'Recent gesloten'],
    ])('should search cases and show in which table to find them', (plannerView, expectedText) => {
        const data = {
            result: { ...searchResult, plannerView },
            searched: true,
        };

        const wrapper = createComponent(data);
        const result = wrapper.find('.search-result');

        expect(result.exists()).toBe(true);
        expect(result.find('[data-testid="searchResultInTable"]').text()).toBe(expectedText);
    });
});

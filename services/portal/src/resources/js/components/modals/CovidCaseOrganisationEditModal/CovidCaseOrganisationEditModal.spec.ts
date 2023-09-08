import Vuex from 'vuex';

import organisationStore from '@/store/organisation/organisationStore';
import type { OrganisationStoreState } from '@/store/organisation/organisationTypes';
import { createTestingPinia } from '@pinia/testing';

import { shallowMount } from '@vue/test-utils';

import CovidCaseOrganisationEditModal from './CovidCaseOrganisationEditModal.vue';
import { setupTest } from '@/utils/test';
import type { VueConstructor } from 'vue';

const hide = vi.fn();
const show = vi.fn();

const createComponent = setupTest(
    (
        localVue: VueConstructor,
        data: object = {},
        organisationStoreState?: OrganisationStoreState,
        withDefaultStub?: boolean
    ) => {
        const BModalMock = {
            template: '<div />',
            methods: { hide, show },
        };

        const organisationStoreModule = {
            ...organisationStore,
            state: {
                ...organisationStore.state,
                ...organisationStoreState,
            },
        };

        return shallowMount<CovidCaseOrganisationEditModal>(CovidCaseOrganisationEditModal, {
            localVue,
            data: () => data,
            store: new Vuex.Store({
                modules: {
                    organisation: organisationStoreModule,
                },
            }),
            pinia: createTestingPinia({
                stubActions: false,
            }),
            stubs: {
                BDropdown: true,
                BDropdownItem: true,
                BFormInvalidFeedback: true,
                BModal: withDefaultStub ? true : BModalMock,
            },
        });
    }
);

describe('CovidCaseOrganisationEditModal.vue', () => {
    it('should not render a modal when created', () => {
        // ARRANGE
        createComponent();

        // ASSERT
        expect(show).not.toHaveBeenCalled();
    });

    it('should render a modal when component show method is triggered to store', async () => {
        // ARRANGE
        const wrapper = createComponent();
        await wrapper.vm.$store.commit('organisation/SET_CURRENT', { name: 'Test', uuid: '1234' });
        await wrapper.vm.$nextTick();
        await wrapper.vm.show();

        // ASSERT
        expect(show).toHaveBeenCalled();
    });

    it('should reset state when modal is hidden', async () => {
        // ARRANGE
        const wrapper = createComponent({ note: 'Test', showRequiredMessage: true });
        await wrapper.vm.$store.commit('organisation/SET_CURRENT', { name: 'Test', uuid: '1234' });
        await wrapper.vm.$nextTick();
        await wrapper.vm.onHidden();

        // ASSERT
        expect(wrapper.vm.note).toBe('');
        expect(wrapper.vm.showRequiredMessage).toBe(false);
    });

    it('should show error message when form is submitted without note', async () => {
        // ARRANGE
        const wrapper = createComponent({}, undefined, true);

        expect(wrapper.vm.showRequiredMessage).toBe(false);

        await wrapper.vm.onConfirm();

        // ASSERT
        expect(wrapper.vm.showRequiredMessage).toBe(true);
    });
});

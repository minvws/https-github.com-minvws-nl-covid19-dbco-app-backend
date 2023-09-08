import { caseApi } from '@dbco/portal-api';
import { createWrapper, shallowMount } from '@vue/test-utils';
import { PermissionV1 } from '@dbco/enum';
import type { BvModal } from 'bootstrap-vue';
import CovidCaseDetailModal from './CovidCaseDetailModal.vue';
import userInfoStore from '@/store/userInfo/userInfoStore';
import Vuex from 'vuex';
import organisationStore from '@/store/organisation/organisationStore';
import i18n from '@/i18n/index';
import { fakePlannerCaseListItem } from '@/utils/__fakes__/fakePlannerCaseListItem';

import { createTestingPinia } from '@pinia/testing';
import { flushCallStack, setupTest } from '@/utils/test';
import type { VueConstructor } from 'vue';
import { userCanEdit } from '@/utils/interfaceState';
import { isEditCaseModulePath } from '@/utils/url';
import type { Mock } from 'vitest';
import { vi } from 'vitest';
import { usePlanner } from '@/store/planner/plannerStore';
vi.mock('@/utils/interfaceState');
vi.mock('@/utils/url');

const createComponent = setupTest(
    (
        localVue: VueConstructor,
        userInfoState: object = { hasPermission: [] },
        givenCase = { ...fakePlannerCaseListItem() }
    ) => {
        const userInfoStoreModule = {
            ...userInfoStore,
            state: {
                ...userInfoStore.state,
                ...userInfoState,
            },
        };

        const organisationStoreModule = {
            ...organisationStore,
            state: organisationStore.state,
        };

        return shallowMount<CovidCaseDetailModal>(CovidCaseDetailModal, {
            localVue,
            i18n,
            propsData: {
                selectedCase: givenCase,
            },
            stubs: {
                BModal: true,
                CovidCaseDetails: true,
                CovidCaseHistory: true,
            },
            store: new Vuex.Store({
                modules: {
                    userInfo: userInfoStoreModule,
                    organisation: organisationStoreModule,
                },
            }),
            pinia: createTestingPinia({
                stubActions: true,
            }),
        });
    }
);

describe('CovidCaseDetailModal.vue', () => {
    it('should be visible', () => {
        // ARRANGE
        const wrapper = createComponent();

        const modal = wrapper.findComponent({ name: 'BModal' });

        // ASSERT
        expect(modal.exists()).toBe(true);
    });

    it('should not show footer modal when user is not allowed to edit', () => {
        // ARRANGE
        (userCanEdit as Mock).mockImplementationOnce(() => false);
        (isEditCaseModulePath as Mock).mockImplementation(() => true);
        const wrapper = createComponent();

        // ASSERT
        expect(wrapper.find(`[data-testid="covid-case-detail-modal-footer"]`).props().hideFooter).toBe(true);
    });

    it('should show footer modal when user is allowed to edit', () => {
        // ARRANGE
        (userCanEdit as Mock).mockImplementationOnce(() => true);
        const givenCase = { ...fakePlannerCaseListItem({ assignedUser: { isCurrent: true } }) };
        const wrapper = createComponent(undefined, givenCase);

        // ASSERT
        expect(wrapper.find(`[data-testid="covid-case-detail-modal-footer"]`).props().hideFooter).toBe(false);
    });

    it('should set activeTab when .show is being fired', async () => {
        // ARRANGE
        const wrapper = createComponent();

        const mockedMethod = vi.fn();
        (wrapper.vm.$refs.modal as unknown as BvModal).show = mockedMethod;

        await wrapper.vm.show();

        // ASSERT
        expect(wrapper.vm.activeTab).toBe(0);
    });

    it('should return and not call window.location.assign when .onConfirm is being called and user lacks permission', async () => {
        (userCanEdit as Mock).mockImplementationOnce(() => false);
        (isEditCaseModulePath as Mock).mockImplementation(() => true);

        const wrapper = createComponent();

        // @ts-ignore
        delete window.location;
        // @ts-ignore
        const assign = vi.fn();
        // @ts-ignore
        window.location = { assign };

        await wrapper.vm.onConfirm();
        await wrapper.vm.$nextTick();

        // ASSERT
        expect(assign).toHaveBeenCalledTimes(0);
    });

    it('should redirect when .onConfirm is being called', async () => {
        (userCanEdit as Mock).mockImplementationOnce(() => true);
        // ARRANGE
        const storeData = {
            permissions: [PermissionV1.VALUE_caseUserEdit],
        };
        const givenCase = { ...fakePlannerCaseListItem({ assignedUser: { isCurrent: true } }) };

        const wrapper = createComponent(storeData, givenCase);

        // @ts-ignore
        delete window.location;
        // @ts-ignore
        const assign = vi.fn();
        // @ts-ignore
        window.location = { assign };

        await wrapper.vm.onConfirm();

        await wrapper.vm.$nextTick();

        // ASSERT
        expect(assign).toHaveBeenCalledWith(`/editcase/${givenCase.uuid}`);
    });

    it.each([
        {
            envPermission: false,
            user: undefined,
        },
        {
            envPermission: true,
            user: { isCurrent: false },
        },
        {
            envPermission: true,
            user: undefined,
        },
    ])(
        'should NOT redirect when .onConfirm is triggered by a user without the rights to do so',
        async ({ envPermission, user }) => {
            (userCanEdit as Mock).mockImplementationOnce(() => envPermission);
            // ARRANGE
            const storeData = {
                permissions: [PermissionV1.VALUE_caseUserEdit],
            };
            const givenCase = { ...fakePlannerCaseListItem({ assignedUser: user }) };

            const wrapper = createComponent(storeData, givenCase);

            // @ts-ignore
            delete window.location;
            // @ts-ignore
            const assign = vi.fn();
            // @ts-ignore
            window.location = { assign };

            await wrapper.vm.onConfirm();

            await wrapper.vm.$nextTick();

            // ASSERT
            expect(assign).toHaveBeenCalledTimes(0);
        }
    );

    it('should emit caseUpdated on root when emitUpdate() method is fired', async () => {
        // ARRANGE
        const wrapper = createComponent();

        await wrapper.vm.emitUpdate();

        const rootWrapper = createWrapper(wrapper.vm.$root);

        // ASSERT
        expect(rootWrapper.emitted('caseUpdated')).toBeTruthy();
    });

    it('should call caseApi.getStatus and set bcoStatus in store to fetched value when onActionDone() method is fired', async () => {
        const wrapper = createComponent();

        const newStatus = {
            bcoStatus: 'archived',
            indexStatus: 'initial',
            statusIndexContactTracing: 'unknown',
            statusExplanation: '',
        };

        const apiMock = vi.spyOn(caseApi, 'getStatus').mockResolvedValueOnce(newStatus);
        const spyAction = vi.spyOn(usePlanner(), 'updateSelectedBCOStatus');

        await wrapper.vm.onActionDone();
        await flushCallStack();

        // ASSERT
        expect(apiMock).toHaveBeenCalled();
        expect(spyAction).toHaveBeenCalledWith(newStatus.bcoStatus);
    });

    it('should call emitUpdate() when onOrganisationChange() method is fired', async () => {
        // ARRANGE
        const wrapper = createComponent();
        (wrapper.vm.$refs.modal as unknown as BvModal).hide = vi.fn();

        await wrapper.vm.onOrganisationChange();
        const rootWrapper = createWrapper(wrapper.vm.$root);

        // ASSERT
        expect(rootWrapper.emitted('caseUpdated')).toBeTruthy();
    });

    it('should call emitUpdate() when onArchive() method is fired', async () => {
        // ARRANGE
        const wrapper = createComponent();
        (wrapper.vm.$refs.caseArchiveModal as unknown as BvModal).hide = vi.fn();
        (wrapper.vm.$refs.modal as unknown as BvModal).hide = vi.fn();

        await wrapper.vm.onArchive();
        const rootWrapper = createWrapper(wrapper.vm.$root);

        // ASSERT
        expect(rootWrapper.emitted('caseUpdated')).toBeTruthy();
    });

    it('should call onActionDone() method when onReopen() method is fired', async () => {
        // ARRANGE
        const wrapper = createComponent();
        (wrapper.vm.$refs.caseReopenModal as unknown as BvModal).hide = vi.fn();
        const actionDoneSpy = vi.spyOn(wrapper.vm, 'onActionDone').mockImplementationOnce(() => vi.fn());

        await wrapper.vm.onReopen();

        // ASSERT
        expect(actionDoneSpy).toHaveBeenCalledTimes(1);
    });

    it('should set selectedCase organisation to current in store when organisation edit is triggered', async () => {
        const wrapper = createComponent();
        const spyOnCommit = vi.spyOn(wrapper.vm.$store, 'commit');
        (wrapper.vm.$refs.caseOrganisationEditModal as unknown as BvModal).show = vi.fn();

        await wrapper.vm.openOrganisationEditModal();

        // ASSERT
        expect(spyOnCommit).toHaveBeenCalledWith(
            'organisation/SET_CURRENT',
            wrapper.vm.$props.selectedCase.organisation,
            undefined
        );
    });
});

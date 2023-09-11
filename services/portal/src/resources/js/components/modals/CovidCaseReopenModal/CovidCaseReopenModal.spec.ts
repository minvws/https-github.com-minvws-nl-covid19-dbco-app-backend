import type { BvModal } from 'bootstrap-vue';
import { shallowMount } from '@vue/test-utils';
import { caseApi } from '@dbco/portal-api';
import CovidCaseReopenModal from './CovidCaseReopenModal.vue';
import * as showToast from '@/utils/showToast';
import { createTestingPinia } from '@pinia/testing';
import { flushCallStack, setupTest } from '@/utils/test';
import type { VueConstructor } from 'vue';

const data = {
    reopenNote: '',
    showRequiredMessage: false,
};

const singleCase = {
    uuid: 'b6ac983d-5479-48e6-a978-e02d6f82e5e6',
    caseId: '3543522',
    contactsCount: 0,
    dateOfBirth: '1990-01-01',
    dateOfTest: '2022-01-04',
    statusIndexContactTracing: null,
    statusExplanation: '',
    createdAt: '2022-01-10T08:48:20Z',
    updatedAt: '2022-01-10T08:48:20Z',
    organisation: {
        uuid: '00000000-0000-0000-0000-000000000000',
        abbreviation: 'GGD1',
        name: 'Demo GGD1',
        isCurrent: true,
    },
    assignedOrganisation: null,
    assignedCaseList: null,
    assignedUser: {
        isCurrent: true,
    },
    isApproved: null,
    isAssignable: true,
    isClosable: false,
    isDeletable: true,
    isEditable: true,
    isReopenable: true,
    label: null,
    plannerView: 'unassigned',
    bcoStatus: 'archived',
    wasOutsourced: false,
    wasOutsourcedToOrganisation: null,
    priority: 1,
    caseLabels: [
        {
            uuid: '2d05afd9-d0ad-4c79-a33b-54952eff85a0',
            label: 'Scheepvaart opvarende',
        },
    ],
    hasNotes: false,
};

const optionResult1 = {
    assignedUserUuid: '00000000-0000-0000-0000-100000000002',
    staleSince: '',
    cases: ['2954bdcb-7854-451d-a0fd-6b05f69c595d'],
    option: {
        type: 'option',
        label: 'Demo GGD1 Gebruiker & Contextbeheerder',
        isSelected: false,
        isEnabled: true,
        assignmentType: 'user',
        assignment: {
            assignedUserUuid: '00000000-0000-0000-0000-100000000002',
        },
    },
};

const createComponent = setupTest((localVue: VueConstructor, props: object = {}, data: object = {}) => {
    return shallowMount<CovidCaseReopenModal>(CovidCaseReopenModal, {
        localVue,
        data: () => data,
        propsData: {
            ...props,
        },
        pinia: createTestingPinia({
            stubActions: false,
        }),
        stubs: {
            BFormTextarea: true,
            BModal: true,
            BToaster: true,
            DBCOAssignDropdown: true,
        },
    });
});

describe('CovidCaseReopenModal.vue', () => {
    it('should show assignment dropdown if case(s) assignable', () => {
        const wrapper = createComponent({ cases: [singleCase] }, data);

        expect(wrapper.findComponent({ name: 'AssignmentDropdown' }).exists()).toBe(true);
    });

    it('should NOT show assignment dropdown if case(s) NOT assignable', () => {
        const wrapper = createComponent({ cases: [{ ...singleCase, ...{ isAssignable: false } }] }, data);

        expect(wrapper.findComponent({ name: 'AssignmentDropdown' }).exists()).toBe(false);
    });

    it('should change reopenAssigneeTitle on assigment', () => {
        const wrapper = createComponent({ cases: [singleCase] }, data);

        const assignmentDropdown = wrapper.findComponent({ name: 'AssignmentDropdown' });
        assignmentDropdown.vm.$emit('optionSelected', optionResult1);
        expect(wrapper.vm.reopenAssigneeTitle).toBe(optionResult1.option.label);
    });

    it('should call api on confirm if note is filled in', async () => {
        const mockApi = vi.spyOn(caseApi, 'reopenCases').mockResolvedValueOnce({});
        const wrapper = createComponent({ cases: [singleCase] }, { ...data, ...{ reopenNote: 'Test' } });
        (wrapper.vm.$refs.modal as any).hide = vi.fn();

        await wrapper.vm.onConfirm();

        expect(mockApi).toHaveBeenCalledTimes(1);
    });

    it('should show error message on confirm if note is NOT filled in', async () => {
        const wrapper = createComponent({ cases: [singleCase] });

        await wrapper.vm.onConfirm();
        await wrapper.vm.$nextTick();

        const formGroups = wrapper.findAllComponents({ name: 'BFormGroup' });
        const noteFormGroup = formGroups.at(0);
        const errorMessage = noteFormGroup.findComponent({ name: 'BFormInvalidFeedback' });

        expect(errorMessage.exists()).toBe(true);
    });

    it('should call toast with error message and error: true when api call fails', async () => {
        const mockApi = vi.spyOn(caseApi, 'reopenCases').mockRejectedValueOnce({});
        const wrapper = createComponent({ cases: [singleCase] }, { ...data, ...{ reopenNote: 'Test' } });
        const mockToast = vi.spyOn(showToast, 'default').mockResolvedValue();
        (wrapper.vm.$refs.modal as unknown as BvModal).hide = vi.fn();

        await wrapper.vm.onConfirm();
        await flushCallStack();

        expect(mockApi).toHaveBeenCalledTimes(1);
        expect(mockToast).toHaveBeenCalledWith('Er is iets fout gegaan met het heropenen', 'reopen-toast', true);
    });

    it('should clear reopenNote and set showRequiredMessage to false when resetModal method is fired', async () => {
        const wrapper = createComponent({ cases: [singleCase] }, { reopenNote: 'Test', showRequiredMessage: true });

        await wrapper.vm.resetModal();

        expect(wrapper.vm.reopenNote).toBe('');
        expect(wrapper.vm.showRequiredMessage).toBe(false);
    });

    it('should render modal when show method is fired', async () => {
        const wrapper = createComponent({ cases: [singleCase] }, { reopenNote: 'Test', showRequiredMessage: true });
        (wrapper.vm.$refs.modal as unknown as BvModal).show = vi.fn();

        await wrapper.vm.show();

        expect(wrapper.findComponent({ name: 'BModal' }).isVisible()).toBe(true);
    });
});

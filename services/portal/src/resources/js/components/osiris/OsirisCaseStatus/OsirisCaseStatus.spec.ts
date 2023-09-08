import indexStore from '@/store/index/indexStore';
import userInfoStore from '@/store/userInfo/userInfoStore';
import Vuex from 'vuex';

import { caseApi } from '@dbco/portal-api';
import { setupTest } from '@/utils/test';
import { shallowMount } from '@vue/test-utils';

import {
    CasequalityFeedbackV1,
    casequalityFeedbackV1Options,
    ContactTracingStatusV1,
    contactTracingStatusV1Options,
    PermissionV1,
    BcoStatusV1,
} from '@dbco/enum';

import type { VueConstructor } from 'vue';
import OsirisCaseStatus from './OsirisCaseStatus.vue';

const createComponent = setupTest(
    (
        localVue: VueConstructor,
        props: object,
        data: object = {},
        indexStoreState: object = {},
        userInfoStorestate: object = {}
    ) => {
        return shallowMount<OsirisCaseStatus>(OsirisCaseStatus, {
            localVue,
            data: () => data,
            propsData: props,
            store: new Vuex.Store({
                modules: {
                    index: {
                        ...indexStore,
                        state: {
                            ...indexStore.state,
                            ...indexStoreState,
                        },
                    },
                    userInfo: {
                        ...userInfoStore,
                        state: {
                            ...userInfoStore.state,
                            ...userInfoStorestate,
                        },
                    },
                },
            }),
            stubs: {
                FormInfo: true,
            },
        });
    }
);

describe('OsirisCaseStatus.vue', () => {
    const spyProcess = vi.spyOn((OsirisCaseStatus as any).methods, 'process');

    const replace = vi.fn();

    // @ts-ignore
    window.location = { replace };

    const fakeLocation = window.location;

    const props = {
        covidCase: {
            uuid: 'uuid',
        },
    };

    const data = {
        casequalityFeedback: null,
        casequalityFeedbackOptions: Object.entries(casequalityFeedbackV1Options).map((item) => {
            return { value: item[0], text: item[1] };
        }),
        contactTracingOptions: contactTracingStatusV1Options,
        ContactTracingStatus: ContactTracingStatusV1,
        selectedStatus: ContactTracingStatusV1.VALUE_not_approached,
        showCaseQualityFeedbackError: false,
        showConfirmModal: false,
        showExplanationRequiredError: false,
        statusExplanation: '',
    };

    afterEach(() => {
        window.location = fakeLocation;
    });

    // expectedSubmitLabel | expectedStatus
    it.each([
        ['Afronden', ContactTracingStatusV1.VALUE_bco_finished],
        ['Afronden', ContactTracingStatusV1.VALUE_four_times_not_reached],
        ['Indienen', ContactTracingStatusV1.VALUE_not_started],
        ['Indienen', ContactTracingStatusV1.VALUE_two_times_not_reached],
        ['Indienen', ContactTracingStatusV1.VALUE_callback_request],
        ['Indienen', ContactTracingStatusV1.VALUE_loose_end],
    ])(
        'should render with %s as button text when the $expectedStatus status is selected',
        async (expectedSubmitLabel, expectedStatus) => {
            // GIVEN the expected status
            const indexStoreData = {
                meta: {
                    statusIndexContactTracing: expectedStatus,
                },
            };

            // AND the user can NOT approve
            const propsData = {
                covidCase: {
                    uuid: 'uuid',
                    bcoStatus: 'draft',
                },
            };

            const userInfoStoreData = {
                permissions: [PermissionV1.VALUE_caseApprove],
            };

            // WHEN the component renders
            const wrapper = createComponent(propsData, data, indexStoreData, userInfoStoreData);

            await wrapper.vm.$nextTick();

            // THEN the component shows the expected submit label
            const modal = wrapper.findComponent({ name: 'BModal' });

            expect(modal.props('okTitle')).toEqual(expectedSubmitLabel);
        }
    );

    it.each([
        ['Case teruggeven', CasequalityFeedbackV1.VALUE_reject_and_reopen],
        ['Case teruggeven', CasequalityFeedbackV1.VALUE_complete],
        ['Case sluiten', CasequalityFeedbackV1.VALUE_approve_and_archive],
    ])(
        'should render with %s as button text when the %s status is selected',
        async (expectedSubmitLabel, expectedCaseQualityFeedback) => {
            // GIVEN a status
            const indexStoreData = {
                meta: {
                    statusIndexContactTracing: ContactTracingStatusV1.VALUE_bco_finished,
                },
            };

            // AND a user that can approve
            const propsData = {
                covidCase: {
                    uuid: 'uuid',
                    bcoStatus: 'completed',
                },
            };

            const userInfoStoreData = {
                permissions: [PermissionV1.VALUE_caseApprove],
            };

            // WHEN the expected case quality feedback is set
            const newData = { ...data, ...{ casequalityFeedback: expectedCaseQualityFeedback } };

            // AND the component renders
            const wrapper = createComponent(propsData, newData, indexStoreData, userInfoStoreData);

            await wrapper.vm.$nextTick();

            // THEN the component shows the expected submit label
            const modal = wrapper.findComponent({ name: 'BModal' });

            expect(modal.props('okTitle')).toEqual(expectedSubmitLabel);
        }
    );

    it('should emit a cancellation when it is triggered', async () => {
        // GIVEN the component renders in its default state
        const wrapper = createComponent(props, data);
        await wrapper.vm.$nextTick();

        // WHEN the modal's cancellation is triggered
        await wrapper.vm.hide({ trigger: 'cancel' });

        // THEN this cancellation is emitted
        expect(wrapper.emitted('cancel')).toBeTruthy();
    });

    it('should NOT emit a cancellation on modal confirmation', async () => {
        // GIVEN the component renders in its default state
        const wrapper = createComponent(props, data);
        await wrapper.vm.$nextTick();

        // WHEN the modal's confirmation is triggered
        await wrapper.vm.hide({ trigger: 'ok' });

        // THEN a cancellation is not emitted
        expect(wrapper.emitted('cancel')).toBeFalsy();
    });

    it('should show error message if required explanation is NOT present when submitting', async () => {
        // GIVEN a status that requires an explanation
        const indexStoreData = {
            meta: {
                statusIndexContactTracing: ContactTracingStatusV1.VALUE_two_times_not_reached,
            },
        };

        // WHEN the component renders
        const wrapper = createComponent(props, data, indexStoreData);

        await wrapper.vm.$nextTick();

        // AND the submit method is triggered
        await wrapper.vm.submit();

        // THEN the error message should be visible
        const formGroups = wrapper.findAllComponents({ name: 'BFormGroup' });
        const errorMessage = formGroups.at(3).findComponent({ name: 'BFormInvalidFeedback' });
        expect(errorMessage.isVisible()).toBe(true);
    });

    it('should NOT process update if required explanation is NOT present when submitting', async () => {
        // GIVEN a status that requires an explanation
        const indexStoreData = {
            meta: {
                statusIndexContactTracing: ContactTracingStatusV1.VALUE_two_times_not_reached,
            },
        };

        // WHEN the component renders
        const wrapper = createComponent(props, data, indexStoreData);

        await wrapper.vm.$nextTick();

        // AND the submit method is triggered
        await wrapper.vm.submit();

        // THEN the process method should NOT have been called
        expect(spyProcess).toHaveBeenCalledTimes(0);
    });

    it('should show a confirmation modal when a confirmation is required', async () => {
        vi.spyOn(caseApi, 'updateContactStatus').mockImplementationOnce(() =>
            Promise.resolve({ response: { data: {} } })
        );

        // GIVEN a case quality feedback option that requires confirmation
        const newData = { ...data, ...{ casequalityFeedback: CasequalityFeedbackV1.VALUE_archive } };

        // AND a status that does not require an explanation
        const indexStoreData = {
            meta: {
                statusIndexContactTracing: ContactTracingStatusV1.VALUE_bco_finished,
            },
        };

        // AND a user that can approve
        const propsData = {
            covidCase: {
                uuid: 'uuid',
                bcoStatus: 'completed',
            },
        };
        const userInfoStoreData = {
            permissions: [PermissionV1.VALUE_caseApprove],
        };

        // WHEN the component renders
        const wrapper = createComponent(propsData, newData, indexStoreData, userInfoStoreData);
        await wrapper.vm.$nextTick();

        wrapper.vm.$modal = {
            show: vi.fn((modalDefinition) => modalDefinition.onConfirm()),
        };

        // AND the submit method is triggered
        await wrapper.vm.submit();

        // THEN showConfirmModal is set to true
        expect(wrapper.vm.showConfirmModal).toBe(true);

        // AND the confirmation modal is visible
        const confirmModal = wrapper.findByTestId('osiris-confirm-modal');
        expect(confirmModal.isVisible).toBeTruthy();

        // AND the modal is confirmed
        await wrapper.vm.process();

        // AND the window location should be replaced
        expect(window.location.replace).toHaveBeenCalledWith('/');
    });

    it('should not add status from store to state if unknown', async () => {
        // GIVEN the store returns the status as unknown
        const indexStoreData = {
            meta: {
                statusIndexContactTracing: 'unknown',
            },
        };

        // WHEN the component renders
        const wrapper = createComponent(props, data, indexStoreData);

        await wrapper.vm.$nextTick();

        // THEN the status in data should not be unknown
        expect(wrapper.vm.selectedStatus).not.toEqual('unknown');
    });

    it('should include case quality feedback in update dispatch if user can approve', async () => {
        const indexStoreData = {
            meta: {
                statusIndexContactTracing: ContactTracingStatusV1.VALUE_not_started,
            },
        };

        vi.spyOn(caseApi, 'updateContactStatus').mockImplementationOnce(() =>
            Promise.resolve({ response: { data: {} } })
        );

        // GIVEN a user that can approve
        const propsData = {
            covidCase: {
                uuid: 'uuid',
                meta: {
                    bcoStatus: 'completed',
                },
            },
        };

        const userInfoStoreData = {
            permissions: [PermissionV1.VALUE_caseApprove],
        };

        // AND case quality feedback is set
        const newData = { ...data, ...{ casequalityFeedback: CasequalityFeedbackV1.VALUE_reject_and_reopen } };

        // WHEN the component renders
        const wrapper = createComponent(propsData, newData, indexStoreData, userInfoStoreData);

        const modalShowMock = vi.fn();
        wrapper.vm.$modal = { show: modalShowMock };

        const spyOnDispatch = vi.spyOn(wrapper.vm.$store, 'dispatch');

        await wrapper.vm.$nextTick();

        // AND the submit method is triggered
        await wrapper.vm.submit();

        // THEN update is dispatched with case quality feedback
        expect(spyOnDispatch).toHaveBeenCalledWith('index/UPDATE_CONTACT_STATUS', {
            uuid: propsData.covidCase.uuid,
            statusIndexContactTracing: wrapper.vm.selectedStatus,
            statusExplanation: wrapper.vm.statusExplanation,
            casequalityFeedback: wrapper.vm.casequalityFeedback,
        });
    });

    it('should show case quality feedback form group WITHOUT default checked option', async () => {
        // GIVEN a user that can approve
        const propsData = {
            covidCase: {
                uuid: 'uuid',
                meta: {
                    bcoStatus: BcoStatusV1.VALUE_completed,
                },
            },
        };
        const userInfoStoreData = {
            permissions: [PermissionV1.VALUE_caseApprove],
        };

        // WHEN the component renders
        const wrapper = createComponent(propsData, data, {}, userInfoStoreData);
        await wrapper.vm.$nextTick();

        // THEN the case quality feedback formgroup is visible
        const formGroups = wrapper.findAllComponents({ name: 'BFormGroup' });
        expect(formGroups.length).toBe(5);

        // AND there should be no default checked option
        const caseQualityRadioGroup = formGroups.at(3).findComponent({ name: 'BFormRadioGroup' });
        expect(caseQualityRadioGroup.props('checked')).toBe(null);
    });

    it('should show error message on submit if user can approve and case quality feedback is missing', async () => {
        // GIVEN a user that can approve
        const propsData = {
            covidCase: {
                uuid: 'uuid',
                meta: {
                    bcoStatus: BcoStatusV1.VALUE_completed,
                },
            },
        };
        const userInfoStoreData = {
            permissions: [PermissionV1.VALUE_caseApprove],
        };

        // WHEN the component renders
        const wrapper = createComponent(propsData, data, {}, userInfoStoreData);
        await wrapper.vm.$nextTick();

        // AND the submit method is triggered before case quality feedback is provided
        await wrapper.vm.submit();

        // THEN the error message should be visible
        const formGroups = wrapper.findAllComponents({ name: 'BFormGroup' });
        const caseQualityRadioGroup = formGroups.at(3).findComponent({ name: 'BFormRadioGroup' });
        const errorMessage = caseQualityRadioGroup.findComponent({ name: 'BFormInvalidFeedback' });
        expect(errorMessage.isVisible()).toBe(true);
    });
});

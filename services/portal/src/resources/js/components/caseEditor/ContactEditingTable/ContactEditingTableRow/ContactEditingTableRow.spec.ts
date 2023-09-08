import { createLocalVue, shallowMount } from '@vue/test-utils';
import ContactEditingTableRow from './ContactEditingTableRow.vue';
import Vuex, { Store } from 'vuex';
import BootstrapVue, { BFormInput, BTd } from 'bootstrap-vue';
import type { IndexStoreState } from '@/store/index/indexStore';
import indexStore from '@/store/index/indexStore';
import type { UserInfoState } from '@/store/userInfo/userInfoStore';
import userInfoStore from '@/store/userInfo/userInfoStore';
import { isNo, isYes } from '@/components/form/ts/formOptions';
import { PermissionV1, InformedByV1 } from '@dbco/enum';
import { userCanEdit } from '@/utils/interfaceState';
import type { Mock } from 'vitest';
import { vi } from 'vitest';
import type { UntypedWrapper } from '@/utils/test';
import { TaskGroup } from '@dbco/portal-api/task.dto';
import { createTestingPinia } from '@pinia/testing';
vi.mock('@/utils/interfaceState');

describe('ContactEditingTableRow.vue', () => {
    const localVue = createLocalVue();
    localVue.use(BootstrapVue);
    localVue.use(Vuex);

    const setWrapper = (
        props?: object,
        indexStoreState: Partial<IndexStoreState> = {},
        userInfoState: Partial<UserInfoState> = {},
        data: object = {},
        BFormInputStubbed = true
    ) => {
        const indexStoreModule = {
            ...indexStore,
            state: {
                ...indexStore.state,
                ...indexStoreState,
            },
        };

        const userInfoStoreModule = {
            ...userInfoStore,
            state: {
                ...userInfoStore.state,
                ...userInfoState,
            },
        };

        return shallowMount(ContactEditingTableRow, {
            data: () => data,
            localVue,
            propsData: props,
            store: new Store({
                modules: {
                    index: indexStoreModule,
                    userInfo: userInfoStoreModule,
                },
            }),
            pinia: createTestingPinia(),
            stubs: {
                BFormInput: BFormInputStubbed ? true : BFormInput,
                DatePicker: true,
                BTd: BTd,
            },
            mocks: {
                $filters: {
                    dateFormatLong: vi.fn((date) => date),
                },
            },
        }) as UntypedWrapper;
    };

    it('should show correct columns for contact group', () => {
        const props = {
            task: {
                uuid: '5160bab8-3bfc-4aaf-a970-834cfe2be489',
                dateOfLastExposure: '2021-05-17', // after source
                accessible: true,
                group: TaskGroup.Contact,
            },
        };

        const indexState: Partial<IndexStoreState> = {
            uuid: '00001',
            fragments: {
                symptoms: {
                    hasSymptoms: isYes,
                },
                immunity: {
                    isImmune: isNo,
                },
                test: {
                    dateOfSymptomOnset: '2021-05-31',
                    dateOfTest: '2021-06-01',
                },
            },
        };

        const wrapper = setWrapper(props, indexState);

        const tableCols = wrapper.findAll('td');
        expect(tableCols).toHaveLength(6);
        expect(wrapper.vm.isMedicalPeriodInfoIncomplete).toBe(false);
        expect(wrapper.vm.isMedicalPeriodInfoNotDefinitive).toBe(true);

        const expectedElements = [
            'task-label-input',
            'task-context-input',
            'date-of-last-exposure',
            'task-category',
            'task-communication',
        ];

        expectedElements.forEach((dataTestId) => {
            const field = wrapper.find(`[data-testid=${dataTestId}]`);
            expect(field.exists()).toBe(true);
        });

        const notExpectedElements = ['task-is-source'];

        notExpectedElements.forEach((dataTestId) => {
            const field = wrapper.find(`[data-testid=${dataTestId}]`);
            expect(field.exists()).toBe(false);
        });
    });

    it('should show correct columns for non contact group', () => {
        const props = {
            task: {
                uuid: '5160bab8-3bfc-4aaf-a970-834cfe2be489',
                dateOfLastExposure: '2021-05-17', // after source
                accessible: true,
                group: TaskGroup.PositiveSource,
            },
        };

        const indexState: Partial<IndexStoreState> = {
            uuid: '00001',
            fragments: {
                symptoms: {
                    hasSymptoms: isYes,
                },
                immunity: {
                    isImmune: isNo,
                },
                test: {
                    dateOfSymptomOnset: '2021-05-31',
                    dateOfTest: '2021-06-01',
                },
            },
        };

        const wrapper = setWrapper(props, indexState);

        const tableCols = wrapper.findAll('td');
        expect(tableCols).toHaveLength(5);

        const expectedElements = ['task-label-input', 'date-of-last-exposure', 'task-category', 'task-is-source'];

        expectedElements.forEach((dataTestId) => {
            const field = wrapper.find(`[data-testid=${dataTestId}]`);
            expect(field.exists()).toBe(true);
        });

        const notExpectedElements = ['task-context-input', 'task-communication'];

        notExpectedElements.forEach((dataTestId) => {
            const field = wrapper.find(`[data-testid=${dataTestId}]`);
            expect(field.exists()).toBe(false);
        });
    });

    it('should show correct data if task is not accessible', () => {
        const props = {
            task: {
                uuid: '5160bab8-3bfc-4aaf-a970-834cfe2be489',
                dateOfLastExposure: '2021-05-17', // after source
                accessible: false,
                group: TaskGroup.Contact,
                category: 'category',
                communication: InformedByV1.VALUE_index,
            },
        };

        const indexState: Partial<IndexStoreState> = {
            uuid: '00001',
            fragments: {
                symptoms: {
                    hasSymptoms: isYes,
                },
                immunity: {
                    isImmune: isNo,
                },
                test: {
                    dateOfSymptomOnset: '2021-05-31',
                    dateOfTest: '2021-06-01',
                },
            },
        };

        const wrapper = setWrapper(props, indexState);

        const tableCols = wrapper.findAll('td');
        expect(tableCols).toHaveLength(6);

        expect(tableCols.at(0).element.innerHTML).toContain('Incubatieperiode voorbij - geen besmetting bekend');
        expect(tableCols.at(1).element.innerHTML).toBe('');
        expect(tableCols.at(2).element.innerHTML).toContain('2021-05-17');
        expect(tableCols.at(3).element.innerHTML).toContain('CATEGORY');
        expect(tableCols.at(4).element.innerHTML).toContain('Index');
    });

    it('should show loading icon when saving', () => {
        const props = {
            task: {
                uuid: '5160bab8-3bfc-4aaf-a970-834cfe2be489',
                dateOfLastExposure: '2021-05-17', // after source
                accessible: true,
                group: TaskGroup.Contact,
            },
            isSaving: true,
        };

        const indexState: Partial<IndexStoreState> = {
            uuid: '00001',
            fragments: {
                symptoms: {
                    hasSymptoms: isYes,
                },
                immunity: {
                    isImmune: isNo,
                },
                test: {
                    dateOfSymptomOnset: '2021-05-31',
                    dateOfTest: '2021-06-01',
                },
            },
        };

        const wrapper = setWrapper(props, indexState);

        expect(wrapper.findComponent({ name: 'BSpinner' }).exists()).toBe(true);
    });

    it('should show dossierNumber if not contact group and has dossierNumber', () => {
        const props = {
            task: {
                uuid: '5160bab8-3bfc-4aaf-a970-834cfe2be489',
                dateOfLastExposure: '2021-05-17', // after source
                accessible: false,
                group: TaskGroup.PositiveSource,
                category: 'category',
                communication: InformedByV1.VALUE_index,
                dossierNumber: '0000001',
            },
        };

        const indexState: Partial<IndexStoreState> = {
            uuid: '00001',
            fragments: {
                symptoms: {
                    hasSymptoms: isYes,
                },
                immunity: {
                    isImmune: isNo,
                },
                test: {
                    dateOfSymptomOnset: '2021-05-31',
                    dateOfTest: '2021-06-01',
                },
            },
        };

        const wrapper = setWrapper(props, indexState);

        const tableCols = wrapper.findAll('td');
        expect(tableCols).toHaveLength(5);

        expect(tableCols.at(0).element.innerHTML).toContain('000001');
    });

    it('should show niet gekoppeld if not contact group and no dossierNumber', () => {
        const props = {
            task: {
                uuid: '5160bab8-3bfc-4aaf-a970-834cfe2be489',
                dateOfLastExposure: '2021-05-17', // after source
                accessible: false,
                group: TaskGroup.PositiveSource,
                category: 'category',
                communication: InformedByV1.VALUE_index,
            },
        };

        const indexState: Partial<IndexStoreState> = {
            uuid: '00001',
            fragments: {
                symptoms: {
                    hasSymptoms: isYes,
                },
                immunity: {
                    isImmune: isNo,
                },
                test: {
                    dateOfSymptomOnset: '2021-05-31',
                    dateOfTest: '2021-06-01',
                },
            },
        };

        const wrapper = setWrapper(props, indexState);

        const tableCols = wrapper.findAll('td');
        expect(tableCols).toHaveLength(5);

        expect(tableCols.at(0).element.innerHTML).toContain('Contact niet gekoppeld aan bronpersoon in HPzone');
    });

    it('if user doesnt have correct permissions input fields for contact group should be disabled', async () => {
        const props = {
            task: {
                uuid: '5160bab8-3bfc-4aaf-a970-834cfe2be489',
                dateOfLastExposure: '2021-06-06', // after contact
                accessible: true,
                group: TaskGroup.Contact,
            },
        };

        const data = { hoveredTask: props.task.uuid };

        const indexState: Partial<IndexStoreState> = {
            uuid: '91BDF70D-3B86-48B6-8DC2-74512B929D65',
            fragments: {
                symptoms: {
                    hasSymptoms: isYes,
                },
                immunity: {
                    isImmune: isNo,
                },
                test: {
                    dateOfSymptomOnset: '2021-05-31',
                    dateOfTest: '2021-06-01',
                },
            },
        };

        (userCanEdit as Mock).mockImplementation(() => false);

        const wrapper = setWrapper(props, indexState, {}, data);

        expect(wrapper.exists()).toBe(true);

        await wrapper.vm.$nextTick();

        const inputFieldsToBeDisabled = [
            'remove-button',
            'task-label-input',
            'task-context-input',
            'date-of-last-exposure',
            'task-category',
            'task-communication',
        ];

        inputFieldsToBeDisabled.forEach((dataTestId) => {
            const field = wrapper.find(`[data-testid=${dataTestId}]`);
            expect(field.attributes().disabled).toBe('true');
        });
    });

    it('if user has correct permissions input fields for contact group should not be disabled', async () => {
        const props = {
            task: {
                uuid: '5160bab8-3bfc-4aaf-a970-834cfe2be489',
                dateOfLastExposure: '2021-06-06', // after contact
                accessible: true,
                group: TaskGroup.Contact,
            },
        };

        const data = { hoveredTask: props.task.uuid };

        const indexState: Partial<IndexStoreState> = {
            uuid: '91BDF70D-3B86-48B6-8DC2-74512B929D65',
            fragments: {
                symptoms: {
                    hasSymptoms: isYes,
                },
                immunity: {
                    isImmune: isNo,
                },
                test: {
                    dateOfSymptomOnset: '2021-05-31',
                    dateOfTest: '2021-06-01',
                },
            },
        };

        const userInfoState: Partial<UserInfoState> = {
            permissions: [PermissionV1.VALUE_taskUserDelete, PermissionV1.VALUE_taskEdit],
        };

        (userCanEdit as Mock).mockImplementation(() => true);

        const wrapper = setWrapper(props, indexState, userInfoState, data);

        expect(wrapper.exists()).toBe(true);

        await wrapper.vm.$nextTick();

        const inputFieldsToBeDisabled = [
            'remove-button',
            'task-label-input',
            'task-context-input',
            'date-of-last-exposure',
            'task-category',
            'task-communication',
        ];

        inputFieldsToBeDisabled.forEach((dataTestId) => {
            const field = wrapper.find(`[data-testid=${dataTestId}]`);
            expect(field.attributes().disabled).toBe(undefined);
        });
    });

    it('if user doesnt have correct permissions input fields for non contact group should be disabled', async () => {
        const props = {
            task: {
                uuid: '5160bab8-3bfc-4aaf-a970-834cfe2be489',
                dateOfLastExposure: '2021-06-06', // after contact
                accessible: true,
                group: TaskGroup.PositiveSource,
            },
        };

        const data = { hoveredTask: props.task.uuid };

        const indexState: Partial<IndexStoreState> = {
            uuid: '91BDF70D-3B86-48B6-8DC2-74512B929D65',
            fragments: {
                symptoms: {
                    hasSymptoms: isYes,
                },
                immunity: {
                    isImmune: isNo,
                },
                test: {
                    dateOfSymptomOnset: '2021-05-31',
                    dateOfTest: '2021-06-01',
                },
            },
        };

        (userCanEdit as Mock).mockImplementation(() => false);

        const wrapper = setWrapper(props, indexState, {}, data);

        expect(wrapper.exists()).toBe(true);

        await wrapper.vm.$nextTick();

        const inputFieldsToBeDisabled = [
            'remove-button',
            'task-label-input',
            'date-of-last-exposure',
            'task-category',
            'task-is-source',
        ];

        inputFieldsToBeDisabled.forEach((dataTestId) => {
            const field = wrapper.find(`[data-testid=${dataTestId}]`);
            expect(field.attributes().disabled).toBe('true');
        });
    });

    it('if user has correct permissions input fields for non contact group should not be disabled', async () => {
        const props = {
            task: {
                uuid: '5160bab8-3bfc-4aaf-a970-834cfe2be489',
                dateOfLastExposure: '2021-06-06', // after contact
                accessible: true,
                group: TaskGroup.PositiveSource,
            },
        };

        const data = { hoveredTask: props.task.uuid };

        const indexState: Partial<IndexStoreState> = {
            uuid: '91BDF70D-3B86-48B6-8DC2-74512B929D65',
            fragments: {
                symptoms: {
                    hasSymptoms: isYes,
                },
                immunity: {
                    isImmune: isNo,
                },
                test: {
                    dateOfSymptomOnset: '2021-05-31',
                    dateOfTest: '2021-06-01',
                },
            },
        };

        const userInfoState: Partial<UserInfoState> = {
            permissions: [PermissionV1.VALUE_taskUserDelete, PermissionV1.VALUE_taskEdit],
        };

        (userCanEdit as Mock).mockImplementation(() => true);

        const wrapper = setWrapper(props, indexState, userInfoState, data);

        expect(wrapper.exists()).toBe(true);

        await wrapper.vm.$nextTick();

        const inputFieldsToBeDisabled = [
            'remove-button',
            'task-label-input',
            'date-of-last-exposure',
            'task-category',
            'task-is-source',
        ];

        inputFieldsToBeDisabled.forEach((dataTestId) => {
            const field = wrapper.find(`[data-testid=${dataTestId}]`);
            expect(field.attributes().disabled).toBe(undefined);
        });
    });

    it('should blur label textfield when datePicker is being opened', () => {
        const props = {
            task: {
                uuid: '5160bab8-3bfc-4aaf-a970-834cfe2be489',
                dateOfLastExposure: '2021-05-17', // after source
                accessible: true,
            },
        };

        const indexState: Partial<IndexStoreState> = {
            uuid: '00001',
            fragments: {
                symptoms: {
                    hasSymptoms: isYes,
                },
                immunity: {
                    isImmune: isNo,
                },
                test: {
                    dateOfSymptomOnset: '2021-05-31',
                    dateOfTest: '2021-06-01',
                },
            },
        };

        const spyOnblurLabelInput = vi.spyOn((ContactEditingTableRow as any).methods, 'blurLabelInput');

        const wrapper = setWrapper(props, indexState);

        const datePicker = wrapper.findComponent({ name: 'DatePicker' });
        datePicker.vm.$emit('opened');

        expect(spyOnblurLabelInput).toHaveBeenCalledTimes(1);
    });

    it('should render symptomatic contacts which are after contact period while using group "contact" with a warning', async () => {
        const props = {
            task: {
                uuid: '5160bab8-3bfc-4aaf-a970-834cfe2be489',
                dateOfLastExposure: '2021-06-06', // after contact
                accessible: true,
                group: TaskGroup.Contact,
            },
        };

        const indexState: Partial<IndexStoreState> = {
            uuid: '00001',
            fragments: {
                symptoms: {
                    hasSymptoms: isYes,
                    wasSymptomaticAtTimeOfCall: null,
                },
                immunity: {
                    isImmune: isNo,
                },
                test: {
                    dateOfSymptomOnset: '2021-05-31',
                    dateOfTest: '2021-06-01',
                },
            },
        };

        const wrapper = setWrapper(props, indexState);
        await wrapper.vm.$nextTick();

        expect(wrapper.findComponent({ name: 'DatePicker' }).props('inputWarning')).toEqual(
            'Het laatste contact was niet in de besmettelijke periode. Controleer de laatste contactdatum.'
        );
    });

    it('should render non symptomatic contacts which are after contact period while using group "contact" with a warning', async () => {
        const props = {
            task: {
                uuid: '5160bab8-3bfc-4aaf-a970-834cfe2be489',
                dateOfLastExposure: '2021-06-07', // after contact
                accessible: true,
                group: TaskGroup.Contact,
            },
        };

        const indexState: Partial<IndexStoreState> = {
            uuid: '00001',
            fragments: {
                symptoms: {
                    hasSymptoms: isNo,
                },
                immunity: {
                    isImmune: isNo,
                },
                test: {
                    dateOfSymptomOnset: '2021-05-31',
                    dateOfTest: '2021-06-01',
                },
            },
        };

        const wrapper = setWrapper(props, indexState);
        await wrapper.vm.$nextTick();

        expect(wrapper.findComponent({ name: 'DatePicker' }).props('inputWarning')).toEqual(
            'Het laatste contact was niet in de besmettelijke periode. Controleer de laatste contactdatum.'
        );
    });

    it('should render contacts which are before source period while using group "positivesource" with a warning', async () => {
        const props = {
            task: {
                uuid: '5160bab8-3bfc-4aaf-a970-834cfe2be489',
                dateOfLastExposure: '2021-05-16', // before source
                accessible: true,
                group: TaskGroup.PositiveSource,
            },
        };

        const indexState: Partial<IndexStoreState> = {
            uuid: '00001',
            fragments: {
                symptoms: {
                    hasSymptoms: isYes,
                },
                immunity: {
                    isImmune: isNo,
                },
                test: {
                    dateOfSymptomOnset: '2021-05-31',
                    dateOfTest: '2021-06-01',
                },
            },
        };

        const wrapper = setWrapper(props, indexState);
        await wrapper.vm.$nextTick();

        expect(wrapper.findComponent({ name: 'DatePicker' }).props('inputWarning')).toEqual(
            'Het laatste contact was niet in de bronperiode. Weet je zeker dat dit een broncontact is?'
        );
    });

    it('isFieldValid should be true when error array contains fieldname', async () => {
        const props = {
            task: {
                uuid: '5160bab8-3bfc-4aaf-a970-834cfe2be489',
                dateOfLastExposure: '2021-05-17', // after source
                accessible: true,
                group: TaskGroup.Contact,
            },
            errors: ['task.label'],
        };

        const indexState: Partial<IndexStoreState> = {
            uuid: '00001',
            fragments: {
                symptoms: {
                    hasSymptoms: isYes,
                },
                immunity: {
                    isImmune: isNo,
                },
                test: {
                    dateOfSymptomOnset: '2021-05-31',
                    dateOfTest: '2021-06-01',
                },
            },
        };

        const wrapper = setWrapper(props, indexState, {}, {}, false);
        await wrapper.vm.$nextTick();

        const labelInput = wrapper.find('[data-testid=task-label-input]');
        expect(labelInput.attributes('class')).toContain('is-invalid');

        const contextInput = wrapper.find('[data-testid=task-context-input]');
        expect(contextInput.attributes('class')).not.toContain('is-invalid');

        const datePickerInput = wrapper.find('[data-testid=date-of-last-exposure]');
        expect(datePickerInput.attributes('inputclass')).not.toContain('is-invalid');
    });
});

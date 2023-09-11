import * as AppHooks from '@/components/AppHooks';
import { isNo, isYes } from '@/components/form/ts/formOptions';
import { ContextGroup } from '@/components/form/ts/formTypes';
import type { IndexStoreState } from '@/store/index/indexStore';
import indexStore from '@/store/index/indexStore';
import type { UserInfoState } from '@/store/userInfo/userInfoStore';
import userInfoStore from '@/store/userInfo/userInfoStore';
import { PermissionV1 } from '@dbco/enum';
import { userCanEdit } from '@/utils/interfaceState';
import { fakerjs, setupTest } from '@/utils/test';
import { fakeContext } from '@/utils/__fakes__/context';
import { shallowMount } from '@vue/test-utils';
import type { Mock } from 'vitest';
import { vi } from 'vitest';
import type { ComponentCustomProperties, VueConstructor } from 'vue';
import { Store } from 'vuex';
import ContextEditingTableRow from './ContextEditingTableRow.vue';
import { createTestingPinia } from '@pinia/testing';

vi.mock('@/utils/interfaceState');

const createComponent = setupTest(
    (
        localVue: VueConstructor,
        props?: object,
        indexStoreState: Partial<IndexStoreState> = {},
        userInfoState: Partial<UserInfoState> = {}
    ) => {
        localVue.directive('mask', vi.fn());

        const indexStoreModule = {
            ...indexStore,
            state: {
                ...indexStore.state,
                uuid: fakerjs.string.uuid(),
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

        return shallowMount<ContextEditingTableRow>(ContextEditingTableRow, {
            localVue,
            propsData: {
                context: fakeContext(undefined, false),
                group: ContextGroup.All,
                ...props,
            },
            store: new Store({
                modules: {
                    index: indexStoreModule,
                    userInfo: userInfoStoreModule,
                },
            }),
            pinia: createTestingPinia(),
            stubs: {
                DatePicker: { template: '<div><slot name="alert"></slot></div>' },
            },
            attachTo: document.body,
        });
    }
);

describe('ContextEditingTableRow.vue', () => {
    it.each([
        {
            isDatePickerOpen: false,
            isSaving: false,
            shouldEmit: false,
        },
        {
            isDatePickerOpen: false,
            isSaving: true,
            shouldEmit: false,
        },
        {
            isDatePickerOpen: true,
            isSaving: false,
            shouldEmit: false,
        },
        {
            isDatePickerOpen: true,
            isSaving: true,
            shouldEmit: true,
        },
    ])(
        'should emit "click" if isDatePickerOpen=$isDatePickerOpen and isSaving=$isSaving => emits? $shouldEmit',
        async (isDatePickerOpen, isSaving, shouldEmit) => {
            const props = {
                isDatePickerOpen,
                isSaving,
            };
            const wrapper = createComponent(props);

            const tableRow = wrapper.findComponent({ name: 'BTr' });
            const tableCell = wrapper.find('btd-stub');

            // Trigger underlying element
            await tableCell.trigger('click');

            if (shouldEmit) {
                expect(tableRow.emitted().click?.length).toBe(1);
            } else {
                expect(tableRow.emitted().click).toBeUndefined();
            }
        }
    );

    it(`should disable the delete button if user does not have permission "${PermissionV1.VALUE_contextDelete}"`, () => {
        (userCanEdit as Mock).mockImplementation(() => true);

        const userInfoState: Partial<UserInfoState> = {
            permissions: [],
        };
        const wrapper = createComponent({}, {}, userInfoState);

        expect(wrapper.findByTestId('delete-button').attributes('disabled')).toBe('true');
    });

    it(`should disable the delete button if user is not allowed to edit`, () => {
        (userCanEdit as Mock).mockImplementation(() => false);

        const userInfoState: Partial<UserInfoState> = {
            permissions: [PermissionV1.VALUE_contextDelete],
        };
        const wrapper = createComponent({}, {}, userInfoState);

        expect(wrapper.findByTestId('delete-button').attributes('disabled')).toBe('true');
    });

    it(`should enable the delete button if user has permission "${PermissionV1.VALUE_contextDelete}" and is allowd to edit`, () => {
        (userCanEdit as Mock).mockImplementation(() => true);

        const userInfoState: Partial<UserInfoState> = {
            permissions: [PermissionV1.VALUE_contextDelete],
        };
        const wrapper = createComponent({}, {}, userInfoState);

        expect(wrapper.findByTestId('delete-button').attributes('disabled')).toBeUndefined();
    });

    it('should have enabled fields if user does have edit rights', () => {
        (userCanEdit as Mock).mockImplementation(() => true);
        const wrapper = createComponent();

        const inputFieldsToBeDisabled = [
            'label-input',
            'remarks-textarea',
            'moments-datepicker',
            'relationship-select',
            'is-source-checkbox',
        ];

        inputFieldsToBeDisabled.every((dataTestId) =>
            expect(wrapper.findByTestId(dataTestId).attributes().disabled).toBeUndefined()
        );
    });

    it('should have disabled fields if user does not have edit rights', () => {
        (userCanEdit as Mock).mockImplementation(() => false);
        const wrapper = createComponent();

        const inputFieldsToBeDisabled = [
            'label-input',
            'remarks-textarea',
            'moments-datepicker',
            'relationship-select',
            'is-source-checkbox',
        ];

        inputFieldsToBeDisabled.every((dataTestId) =>
            expect(wrapper.findByTestId(dataTestId).attributes().disabled).toMatch(/true|disabled/)
        );
    });

    it('should emit delete with context uuid if delete button is clicked', async () => {
        const wrapper = createComponent();

        const deleteButton = wrapper.findByTestId('delete-button');
        await deleteButton.trigger('click');

        expect(wrapper.emitted().delete).toBeDefined();
        expect(wrapper.emitted().delete?.length).toBe(1);
        expect(wrapper.emitted().delete?.[0]).toEqual([wrapper.vm.$props.context.uuid]);
    });

    it('should show place label if context place is set and has a label', () => {
        const props = {
            context: {
                ...fakeContext(),
                place: {
                    label: fakerjs.company.name(),
                },
            },
        };
        const wrapper = createComponent(props);

        expect(wrapper.findByTestId('label-text').exists()).toBe(true);
        expect(wrapper.findByTestId('label-input').exists()).toBe(false);
        expect(wrapper.findByTestId('label-text').text()).toBe(props.context.place.label);
    });

    it('should show input label if place does NOT have a label', () => {
        const props = {
            context: {
                ...fakeContext(),
                place: {
                    label: null,
                },
            },
        };
        const wrapper = createComponent(props);

        expect(wrapper.findByTestId('label-text').exists()).toBe(false);
        expect(wrapper.findByTestId('label-input').exists()).toBe(true);
        expect(wrapper.findByTestId('label-input').attributes('value')).toBe(props.context.label);
    });

    it('should emit change on change/input of the place input label', () => {
        const wrapper = createComponent();

        const labelInput = wrapper.findByTestId('label-input');

        labelInput.vm.$emit('change');
        expect(wrapper.emitted()).toBeDefined();
        expect(wrapper.emitted().change?.length).toBe(1);

        labelInput.vm.$emit('input');
        expect(wrapper.emitted().change?.length).toBe(2);
    });

    it('should show icon with tooltip if context has been linked (placeUuid is set)', () => {
        const props = {
            context: {
                ...fakeContext(),
                placeUuid: fakerjs.string.uuid(),
            },
        };
        const wrapper = createComponent(props);

        expect(wrapper.findByTestId('icon-connected').exists()).toBe(true);
    });

    it('should NOT show icon with tooltip if context has NOT been linked (placeUuid is NOT set)', () => {
        const props = {
            context: {
                ...fakeContext(),
                placeUuid: null,
            },
        };
        const wrapper = createComponent(props);

        expect(wrapper.findByTestId('icon-connected').exists()).toBe(false);
    });

    it('should reset textarea width/height when the remarks textarea gets blurred', () => {
        const wrapper = createComponent();

        const textarea = wrapper.findByTestId<HTMLElement>('remarks-textarea');
        textarea.element.style.width = '100px';
        textarea.element.style.height = '100px';

        textarea.vm.$emit('blur', { target: textarea.element });

        expect((wrapper.findByTestId('remarks-textarea').element as HTMLTextAreaElement).style).toMatchObject({
            width: '',
            height: '',
        });
    });

    it('should emit change on change/input of the remarks textarea', () => {
        const wrapper = createComponent();

        const textarea = wrapper.findByTestId('remarks-textarea');
        textarea.vm.$emit('change');
        expect(wrapper.emitted()).toBeDefined();
        expect(wrapper.emitted().change?.length).toBe(1);

        textarea.vm.$emit('input');
        expect(wrapper.emitted().change?.length).toBe(2);
    });

    it('should blur active element when date picker is opened', () => {
        const wrapper = createComponent();

        const inputEl = document.createElement('input');
        const spyBlur = vi.spyOn(inputEl, 'blur');
        Object.defineProperty(document, 'activeElement', { value: inputEl });

        const datepicker = wrapper.findByTestId('moments-datepicker');
        datepicker.vm.$emit('opened');

        expect(spyBlur).toHaveBeenCalledOnce();
    });

    describe('after closing the datepicker', () => {
        it('should return early if no changes have been made', () => {
            const wrapper = createComponent();

            const datepicker = wrapper.findByTestId('moments-datepicker');
            datepicker.vm.$emit('opened');
            datepicker.vm.$emit('close', wrapper.vm.$props.context.moments);

            expect(wrapper.emitted('change')).toBeUndefined();
        });

        it.each([
            {
                // only contagious dates
                group: ContextGroup.Source,
                moments: ['2021-11-05'],
                shouldModalShow: true,
                modalTitle: 'besmettelijke periode',
                modalText: 'contactonderzoek',
            },
            {
                // only source dates
                group: ContextGroup.Source,
                moments: ['2021-10-28'],
                shouldModalShow: false,
                modalTitle: '',
                modalText: '',
            },
            {
                // a mix of source and contagious dates
                group: ContextGroup.Source,
                moments: ['2021-10-28', '2021-11-05'],
                shouldModalShow: false,
                modalTitle: '',
                modalText: '',
            },
            {
                // overlap date
                group: ContextGroup.Source,
                moments: ['2021-10-30'],
                shouldModalShow: false,
                modalTitle: '',
                modalText: '',
            },
            {
                // only source dates
                group: ContextGroup.Contagious,
                moments: ['2021-10-28'],
                shouldModalShow: true,
                modalTitle: 'bronperiode',
                modalText: 'brononderzoek',
            },
            {
                // only contagious dates
                group: ContextGroup.Contagious,
                moments: ['2021-11-05'],
                shouldModalShow: false,
                modalTitle: '',
                modalText: '',
            },
            {
                // a mix of source and contagious dates
                group: ContextGroup.Contagious,
                moments: ['2021-10-28', '2021-11-05'],
                shouldModalShow: false,
                modalTitle: '',
                modalText: '',
            },
            {
                // overlap date
                group: ContextGroup.Contagious,
                moments: ['2021-10-30'],
                shouldModalShow: false,
                modalTitle: '',
                modalText: '',
            },
        ])(
            '%#: for group "$group" given new dates "$moments" warning model should be "$shouldModalShow" and title contains "$modalTitle" + text contains "$modalText"',
            ({ group, moments, shouldModalShow, modalTitle, modalText }) => {
                const modalMock = { show: vi.fn(), hide: vi.fn() };
                const modalSpy = vi.spyOn(AppHooks, 'useModal');
                modalSpy.mockImplementationOnce(() => modalMock);

                const props = { group };
                const indexState: Partial<IndexStoreState> = {
                    fragments: {
                        symptoms: {
                            hasSymptoms: isYes,
                        },
                        immunity: {
                            isImmune: isNo,
                        },
                        test: {
                            dateOfSymptomOnset: '2021-11-01',
                            dateOfTest: '2021-11-02',
                        },
                    },
                };

                const wrapper = createComponent(props, indexState);

                const datepicker = wrapper.findByTestId('moments-datepicker');
                datepicker.vm.$emit('opened');
                datepicker.vm.$emit('close', moments);

                if (shouldModalShow) {
                    expect(modalMock.show).toHaveBeenCalledWith(
                        expect.objectContaining({
                            title: expect.stringContaining(modalTitle),
                            text: expect.stringContaining(modalText),
                        })
                    );
                } else {
                    expect(modalMock.show).not.toHaveBeenCalled();
                }
            }
        );

        it('should emit "change" and update moments if modal is confirmed', () => {
            const modalMock = { show: vi.fn(), hide: vi.fn() };
            const modalSpy = vi.spyOn(AppHooks, 'useModal');
            modalSpy.mockImplementationOnce(() => modalMock);

            const props = {
                group: ContextGroup.Contagious,
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
                        dateOfSymptomOnset: '2022-01-10',
                        dateOfTest: '2022-01-12',
                    },
                },
            };
            const wrapper = createComponent(props, indexState);

            const datepicker = wrapper.findByTestId('moments-datepicker');
            datepicker.vm.$emit('opened');
            datepicker.vm.$emit('close', ['2022-01-07']);

            expect(modalMock.show).toHaveBeenCalledTimes(1);
            const modalShowCall = modalMock.show.mock.lastCall?.[0] as Parameters<
                ComponentCustomProperties['$modal']['show']
            >[0];
            modalShowCall.onConfirm?.();

            expect(wrapper.emitted('change')).toBeDefined();
            expect(wrapper.emitted('change')?.[0][0].moments).toEqual(['2022-01-07']);
        });

        it.each([{ moments: [] }, { moments: ['2022-01-01'] }])(
            'should NOT emit "change" and reset moments to original state=$moments if modal is cancelled',
            ({ moments }) => {
                const modalMock = { show: vi.fn(), hide: vi.fn() };
                const modalSpy = vi.spyOn(AppHooks, 'useModal');
                modalSpy.mockImplementationOnce(() => modalMock);

                const props = {
                    context: {
                        ...fakeContext(),
                        moments,
                    },
                    group: ContextGroup.Contagious,
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
                            dateOfSymptomOnset: '2022-01-10',
                            dateOfTest: '2022-01-12',
                        },
                    },
                };
                const wrapper = createComponent(props, indexState);

                const datepicker = wrapper.findByTestId('moments-datepicker');
                datepicker.vm.$emit('opened');
                datepicker.vm.$emit('close', ['2022-01-07']);

                expect(modalMock.show).toHaveBeenCalledTimes(1);
                const modalShowCall = modalMock.show.mock.lastCall?.[0] as Parameters<
                    ComponentCustomProperties['$modal']['show']
                >[0];
                modalShowCall.onCancel?.();

                expect(wrapper.emitted('change')).toBeUndefined();
                expect(wrapper.vm.$props.context.moments).toEqual(moments);
            }
        );

        it.each([
            {
                uuid: fakerjs.string.uuid(),
                moments: [],
            },
            {
                uuid: null,
                moments: ['2022-01-01'],
            },
        ])(
            'should emit "change" and update moments if dates are changed and not in other period + uuid=$uuid, moments=$moments',
            ({ uuid, moments }) => {
                const modalMock = { show: vi.fn(), hide: vi.fn() };
                const modalSpy = vi.spyOn(AppHooks, 'useModal');
                modalSpy.mockImplementationOnce(() => modalMock);

                const props = {
                    context: {
                        ...fakeContext(),
                        uuid,
                        moments,
                    },
                    group: ContextGroup.Contagious,
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
                            dateOfSymptomOnset: '2022-01-10',
                            dateOfTest: '2022-01-12',
                        },
                    },
                };
                const wrapper = createComponent(props, indexState);

                const datepicker = wrapper.findByTestId('moments-datepicker');
                datepicker.vm.$emit('opened');
                datepicker.vm.$emit('close', ['2022-01-11']);

                expect(modalMock.show).toHaveBeenCalledTimes(0);
                expect(wrapper.emitted('change')).toBeDefined();
                expect(wrapper.emitted('change')?.[0][0].moments).toEqual(['2022-01-11']);
            }
        );
    });

    it(`should assign date warning to datepicker when group is "${ContextGroup.Source} and last moment is before source period"`, () => {
        const props = {
            context: {
                ...fakeContext(),
                moments: ['2021-12-01'],
            },
            group: ContextGroup.Source,
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
                    dateOfSymptomOnset: '2022-01-10',
                    dateOfTest: '2022-01-12',
                },
            },
        };
        const wrapper = createComponent(props, indexState);

        expect(wrapper.findComponent({ name: 'DatePicker' }).attributes('input-warning')).toEqual(
            'Het laatste bezoek was vóór de bronperiode. Deze context hoeft niet te worden opgenomen in het dossier.'
        );
    });

    it(`should NOT assign date warning to datepicker when group is "${ContextGroup.Source} and last moment is NOT before source period"`, () => {
        const props = {
            context: {
                ...fakeContext(),
                moments: ['2022-01-01'],
            },
            group: ContextGroup.Source,
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
                    dateOfSymptomOnset: '2022-01-10',
                    dateOfTest: '2022-01-12',
                },
            },
        };
        const wrapper = createComponent(props, indexState);

        expect(wrapper.findComponent({ name: 'DatePicker' }).attributes('input-warning')).toBeUndefined();
    });

    it(`should assign date warning to datepicker when group is "${ContextGroup.Contagious} and last moment is after contagious period"`, () => {
        const props = {
            context: {
                ...fakeContext(),
                moments: ['2022-02-01'],
            },
            group: ContextGroup.Contagious,
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
                    dateOfSymptomOnset: '2022-01-10',
                    dateOfTest: '2022-01-12',
                },
            },
        };
        const wrapper = createComponent(props, indexState);

        expect(wrapper.findComponent({ name: 'DatePicker' }).attributes('input-warning')).toEqual(
            'Het laatste bezoek was na de besmettelijke periode. Deze context hoeft niet te worden opgenomen in het dossier.'
        );
    });

    it(`should NOT assign date warning to datepicker when group is "${ContextGroup.Contagious} and last moment is NOT after contagious period"`, () => {
        const props = {
            context: {
                ...fakeContext(),
                moments: ['2022-01-11'],
            },
            group: ContextGroup.Contagious,
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
                    dateOfSymptomOnset: '2022-01-10',
                    dateOfTest: '2022-01-12',
                },
            },
        };
        const wrapper = createComponent(props, indexState);

        expect(wrapper.findComponent({ name: 'DatePicker' }).attributes('input-warning')).toBeUndefined();
    });

    it('should show warning in datepicker if isMedicalPeriodInfoIncomplete=true', () => {
        const wrapper = createComponent();

        expect(wrapper.findComponent({ name: 'DatePicker' }).find('forminfo-stub').attributes('text')).toEqual(
            'De bron- en/of besmettelijke periode kunnen nog niet worden getoond. Vul minimaal in: klachten, EZD, testdatum.'
        );
    });

    it('should show warning in datepicker if isMedicalPeriodInfoIncomplete=false and isMedicalPeriodInfoNotDefinitive=true', () => {
        const indexState: Partial<IndexStoreState> = {
            uuid: '00001',
            fragments: {
                symptoms: {
                    hasSymptoms: isYes,
                },
                test: {
                    dateOfSymptomOnset: '2022-01-10',
                    dateOfTest: '2022-01-12',
                },
            },
        };
        const wrapper = createComponent({}, indexState);

        expect(wrapper.findComponent({ name: 'DatePicker' }).find('forminfo-stub').attributes('text')).toEqual(
            'Vul voor definitieve besmettelijke periode minimaal in: klachten, ziekenhuisopname en verminderde afweer.'
        );
    });

    it('should NOT show warning in datepicker if isMedicalPeriodInfoIncomplete=false and isMedicalPeriodInfoNotDefinitive=false', () => {
        const indexState: Partial<IndexStoreState> = {
            uuid: '00001',
            fragments: {
                hospital: {
                    isAdmitted: isNo,
                },
                symptoms: {
                    hasSymptoms: isYes,
                    stillHadSymptomsAt: '2022-01-15',
                    wasSymptomaticAtTimeOfCall: isNo,
                },
                test: {
                    dateOfSymptomOnset: '2022-01-10',
                    dateOfTest: '2022-01-12',
                },
            },
        };
        const wrapper = createComponent({}, indexState);

        expect(wrapper.findComponent({ name: 'DatePicker' }).find('forminfo-stub').exists()).toBe(false);
    });

    it('should emit "change" if the relationship is changed', () => {
        const wrapper = createComponent();
        wrapper.findByTestId('relationship-select').vm.$emit('change');

        expect(wrapper.emitted('change')).toBeTruthy();
    });

    it(`should show is source checkbox if group is NOT "${ContextGroup.Contagious}" and more than one moment is set`, () => {
        const props = {
            context: {
                ...fakeContext(),
                moments: ['2022-01-01', '2022-01-02'],
            },
            group: ContextGroup.Source,
        };

        const wrapper = createComponent(props);

        expect(wrapper.findByTestId('is-source-checkbox').exists()).toBe(true);
    });

    it(`should NOT show is source checkbox if group is "${ContextGroup.Contagious}" and more than one moment is set`, () => {
        const props = {
            context: {
                ...fakeContext(),
                moments: ['2022-01-01', '2022-01-02'],
            },
            group: ContextGroup.Contagious,
        };

        const wrapper = createComponent(props);

        expect(wrapper.findByTestId('is-source-checkbox').exists()).toBe(false);
    });

    it(`should NOT show is source checkbox if group is NOT "${ContextGroup.Contagious}" and no moment is set`, () => {
        const props = {
            context: {
                ...fakeContext(),
                moments: [],
            },
            group: ContextGroup.Source,
        };

        const wrapper = createComponent(props);

        expect(wrapper.findByTestId('is-source-checkbox').exists()).toBe(false);
    });

    it('should show loading icon when saving', () => {
        const props = {
            isSaving: true,
        };

        const wrapper = createComponent(props);

        expect(wrapper.findComponent({ name: 'BSpinner' }).exists()).toBe(true);
    });

    it('should show edit context button when NOT saving', () => {
        const props = {
            isSaving: false,
        };

        const wrapper = createComponent(props);

        expect(wrapper.findByTestId('context-edit-button').exists()).toBe(true);
    });

    it.each([
        {
            name: 'label',
            testId: 'label-input',
        },
        {
            name: 'remarks',
            testId: 'remarks-textarea',
        },
        {
            name: 'relationship',
            testId: 'relationship-select',
        },
        {
            name: 'isSource',
            testId: 'is-source-checkbox',
        },
    ])('field "$name" should get state "false" assigned if field is invalid', async ({ name, testId }) => {
        const props = {
            context: {
                ...fakeContext(),
                moments: ['2022-01-01'],
            },
        };
        const wrapper = createComponent(props);
        const field = wrapper.findByTestId(testId);

        expect(field.props('state')).toBe(null);

        await wrapper.setProps({ errors: [`context.${name}`] });
        expect(field.props('state')).toBe(false);
    });

    it('datePicker should get input-class "is-invalid" assigned if field is invalid', async () => {
        const wrapper = createComponent();
        const datepicker = wrapper.findByTestId('moments-datepicker');

        expect(datepicker.vm.$attrs).toEqual(
            expect.objectContaining({
                'input-class': { 'is-invalid': false },
            })
        );

        await wrapper.setProps({ errors: [`context.moments`] });

        expect(datepicker.vm.$attrs).toEqual(
            expect.objectContaining({
                'input-class': { 'is-invalid': true },
            })
        );
    });
});

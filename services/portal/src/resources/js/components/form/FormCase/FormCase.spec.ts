import { bsnApi, caseApi } from '@dbco/portal-api';
import FormulateFormWrapper from '@/components/form/FormulateFormWrapper/FormulateFormWrapper.vue';
import { fakerjs, setupTest } from '@/utils/test';
import { fakePlannerCaseListItem } from '@/utils/__fakes__/fakePlannerCaseListItem';
import { createTestingPinia } from '@pinia/testing';
import { shallowMount } from '@vue/test-utils';
import type { ComponentCustomProperties, VueConstructor } from 'vue';
import FormCase from './FormCase.vue';
import type { PlannerCase } from '@dbco/portal-api/case.dto';
import type { AxiosResponse } from 'axios';

const formulateSubmit = vi.fn();
const modalMock = { show: vi.fn(), hide: vi.fn() };

vi.mock('@/components/AppHooks', () => ({
    useFormulate: () => ({
        submit: formulateSubmit,
    }),
    useModal: () => modalMock,
}));

const createComponent = setupTest(
    async (localVue: VueConstructor, props: object = {}, data: object = {}, isOpen = true) => {
        const wrapper = shallowMount<FormCase>(FormCase, {
            localVue,
            pinia: createTestingPinia(),
            data: () => data,
            propsData: props,
            stubs: {
                FormulateFormWrapper,
            },
        });

        if (isOpen) await (wrapper.vm as AnyObject).open();

        return wrapper;
    }
);

describe('FormCase.vue', () => {
    beforeEach(() => {
        vi.spyOn(caseApi, 'getPlannerCase').mockImplementation(() =>
            Promise.resolve({
                data: {
                    caseLabels: [
                        { uuid: fakerjs.string.uuid(), label: fakerjs.lorem.word() },
                        { uuid: fakerjs.string.uuid(), label: fakerjs.lorem.word() },
                    ],
                    index: {
                        firstname: fakerjs.person.firstName(),
                        lastname: fakerjs.person.lastName(),
                    },
                    contact: {
                        phone: fakerjs.phone.number(),
                    },
                },
            } as { data: PlannerCase })
        );
    });

    it('should NOT show BModal if schema is not loaded', async () => {
        const wrapper = await createComponent(undefined, undefined, false);

        expect(wrapper.findComponent({ ref: 'modalRef' }).exists()).toBe(false);
    });

    it('should show BModal if open method is called', async () => {
        const wrapper = await createComponent();

        expect(wrapper.findComponent({ ref: 'modalRef' }).exists()).toBe(true);
    });

    it('should disable buttons when isLoading=true', async () => {
        const wrapper = await createComponent(undefined, { isLoading: true });

        expect(wrapper.findComponent({ ref: 'modalRef' }).props()).toEqual(
            expect.objectContaining({
                cancelDisabled: true,
                okDisabled: true,
            })
        );
    });

    it('should use title="Case aanmaken" and okTitle="Doorgaan" if selectedCaseUuid i NOTs set', async () => {
        const wrapper = await createComponent();

        expect(wrapper.findComponent({ ref: 'modalRef' }).props()).toEqual(
            expect.objectContaining({
                title: 'Case aanmaken',
                okTitle: 'Doorgaan',
            })
        );
    });

    it('should use title="Wijzigingen toepassen" and okTitle="Opslaan" if selectedCaseUuid is set', async () => {
        const wrapper = await createComponent({ selectedCase: fakePlannerCaseListItem() });

        expect(wrapper.findComponent({ ref: 'modalRef' }).props()).toEqual(
            expect.objectContaining({
                title: 'Wijzigingen toepassen',
                okTitle: 'Opslaan',
            })
        );
    });

    describe('form submit', () => {
        it('should submit form when event "ok" is emitted', async () => {
            const wrapper = await createComponent();
            await wrapper.findComponent({ ref: 'modalRef' }).vm.$emit('ok', new Event('ok'));

            expect(formulateSubmit).toHaveBeenCalled();
        });

        it('should submit VueFormulate form with the right name', async () => {
            const wrapper = await createComponent();
            const expectedFormName = 'case-form';

            expect(wrapper.findComponent({ name: 'FormulateFormWrapper' }).attributes('name')).toBe(expectedFormName);
            wrapper.findComponent({ ref: 'modalRef' }).vm.$emit('ok', new Event('ok'));

            expect(formulateSubmit).toHaveBeenCalledWith(expectedFormName);
        });

        it.each([
            { deleteProp: null, expected: true },
            { deleteProp: 'index.bsn', expected: false },
            { deleteProp: 'index.dateOfBirth', expected: false },
            { deleteProp: 'index.address', expected: false },
        ])(
            'should call bsnLookup on submit if all BSN parameters are filled, deleteProp=$deleteProp, expected=$expected',
            async ({ deleteProp, expected }) => {
                const spyBsnLookup = vi.spyOn(bsnApi, 'bsnLookup').mockImplementation(() =>
                    Promise.resolve({
                        guid: fakerjs.string.uuid(),
                        censoredBsn: `****${fakerjs.number.int({ min: 100, max: 999 })}`,
                        letters: fakerjs.lorem.word(),
                    })
                );

                const data = {
                    formValues: {
                        'index.bsn': fakerjs.number.int({ min: 100000000, max: 999999999 }),
                        'index.dateOfBirth': fakerjs.date.past().toISOString(),
                        'index.address': {
                            houseNumber: fakerjs.location.buildingNumber(),
                            postalCode: fakerjs.location.zipCode('####??'),
                        },
                    },
                };

                if (deleteProp) delete data.formValues[deleteProp as keyof typeof data.formValues];

                const wrapper = await createComponent();
                await wrapper.setData(data);
                await wrapper.vm.onSubmit();

                expect(spyBsnLookup).toHaveBeenCalledTimes(expected ? 1 : 0);
            }
        );

        it('should assign errors if BSN lookup fails with BSN field error', async () => {
            const spyBsnLookup = vi.spyOn(bsnApi, 'bsnLookup').mockImplementation(() =>
                Promise.reject({
                    response: {
                        data: {
                            errors: {
                                bsn: ['bsn-error'],
                            },
                        },
                    },
                })
            );

            const data = {
                formValues: {
                    'index.bsn': fakerjs.number.int({ min: 100000000, max: 999999999 }),
                    'index.dateOfBirth': fakerjs.date.past().toISOString(),
                    'index.address': {
                        houseNumber: fakerjs.location.buildingNumber(),
                        postalCode: fakerjs.location.zipCode('####??'),
                    },
                },
            };

            const wrapper = await createComponent();
            await wrapper.setData(data);
            await wrapper.vm.onSubmit();

            expect(spyBsnLookup).toHaveBeenCalledTimes(1);
            expect(wrapper.vm.formErrors).toEqual(expect.objectContaining({ 'index.bsn': expect.anything() }));
        });

        it('should throw error BSN lookup fails without BSN field error', async () => {
            vi.spyOn(bsnApi, 'bsnLookup').mockImplementation(() =>
                Promise.reject({ response: { data: { errors: {} } } })
            );

            const data = {
                formValues: {
                    'index.bsn': fakerjs.number.int({ min: 100000000, max: 999999999 }),
                    'index.dateOfBirth': fakerjs.date.past().toISOString(),
                    'index.address': {
                        houseNumber: fakerjs.location.buildingNumber(),
                        postalCode: fakerjs.location.zipCode('####??'),
                    },
                },
            };

            const wrapper = await createComponent();
            await wrapper.setData(data);

            await expect(wrapper.vm.onSubmit()).rejects.toThrow();
        });

        it('should create new case on submit if no selectedCase was passed', async () => {
            const spyCreateCase = vi.spyOn(caseApi, 'createCase').mockImplementation(() =>
                Promise.resolve({
                    data: { uuid: fakerjs.string.uuid() },
                })
            );

            const wrapper = await createComponent();
            wrapper.vm.modalRef = { hide: vi.fn() };

            await wrapper.vm.onSubmit();

            expect(spyCreateCase).toHaveBeenCalled();
        });

        it('should update case on submit if selectedCase was passed', async () => {
            const spyUpdateCase = vi.spyOn(caseApi, 'updatePlannerCase').mockImplementation(() =>
                Promise.resolve({
                    data: { uuid: fakerjs.string.uuid() },
                })
            );

            const wrapper = await createComponent({ selectedCase: fakePlannerCaseListItem() });
            wrapper.vm.modalRef = { hide: vi.fn() };

            await wrapper.vm.onSubmit();

            expect(spyUpdateCase).toHaveBeenCalled();
        });

        it('should include assignedCaseListUuid if prop list is passed', async () => {
            const spyCreateCase = vi.spyOn(caseApi, 'createCase').mockImplementation(() =>
                Promise.resolve({
                    data: { uuid: fakerjs.string.uuid() },
                })
            );

            const listUuid = fakerjs.string.uuid();
            const wrapper = await createComponent({ list: listUuid });
            wrapper.vm.modalRef = { hide: vi.fn() };

            await wrapper.vm.onSubmit();

            expect(spyCreateCase).toHaveBeenCalledWith(expect.objectContaining({ assignedCaseListUuid: listUuid }));
        });

        it('should NOT include assignedCaseListUuid if prop list is NOT passed', async () => {
            const spyCreateCase = vi.spyOn(caseApi, 'createCase').mockImplementation(() =>
                Promise.resolve({
                    data: { uuid: fakerjs.string.uuid() },
                })
            );

            const wrapper = await createComponent();
            wrapper.vm.modalRef = { hide: vi.fn() };

            await wrapper.vm.onSubmit();

            expect(spyCreateCase).toHaveBeenCalledWith(
                expect.not.objectContaining({ assignedCaseListUuid: expect.anything() })
            );
        });
    });

    describe('BSN modal', () => {
        it('should create/update case if "continue" has been emitted', async () => {
            const spyCreateCase = vi.spyOn(caseApi, 'createCase').mockImplementation(() =>
                Promise.resolve({
                    data: { uuid: fakerjs.string.uuid() },
                })
            );

            const guid = fakerjs.string.uuid();
            const wrapper = await createComponent();
            wrapper.vm.modalRef = { hide: vi.fn() };
            await wrapper.setData({ bsnModelData: { guid } });

            await wrapper.findComponent({ name: 'BsnModal' }).vm.$emit('continue');

            expect(spyCreateCase).toHaveBeenCalled();
        });

        it('should include pseudoBsnGuid in data if bsnInfo is passed', async () => {
            const spyCreateCase = vi.spyOn(caseApi, 'createCase').mockImplementation(() =>
                Promise.resolve({
                    data: { uuid: fakerjs.string.uuid() },
                })
            );

            const guid = fakerjs.string.uuid();
            const wrapper = await createComponent();
            wrapper.vm.modalRef = { hide: vi.fn() };
            await wrapper.setData({ bsnModelData: { guid } });

            await wrapper.findComponent({ name: 'BsnModal' }).vm.$emit('continue', { guid });

            expect(spyCreateCase).toHaveBeenCalledWith(expect.objectContaining({ pseudoBsnGuid: guid }));
        });
    });

    describe('delete modal', () => {
        it('should show modal when event "delete" is emitted', async () => {
            const wrapper = await createComponent();
            await wrapper.findComponent({ name: 'FormulateFormWrapper' }).vm.$emit('delete');

            expect(modalMock.show).toHaveBeenCalled();
        });

        it('should call deleteCase after confirmation and emit "deleted" after 200ms', async () => {
            const deleteCase = vi
                .spyOn(caseApi, 'deleteCase')
                .mockImplementationOnce(() => Promise.resolve({} as AxiosResponse));

            const givenCase = fakePlannerCaseListItem();
            const wrapper = await createComponent({ selectedCase: givenCase });
            await wrapper.findComponent({ name: 'FormulateFormWrapper' }).vm.$emit('delete');

            const modalShowCall = modalMock.show.mock.lastCall?.[0] as Parameters<
                ComponentCustomProperties['$modal']['show']
            >[0];
            modalShowCall.onConfirm?.();

            expect(deleteCase).toHaveBeenCalledWith(givenCase.uuid);
            expect(wrapper.emitted('deleted')).toBeUndefined();

            await new Promise((resolve) => setTimeout(resolve, 200));
            expect(wrapper.emitted('deleted')).toBeUndefined();
        });
    });
});

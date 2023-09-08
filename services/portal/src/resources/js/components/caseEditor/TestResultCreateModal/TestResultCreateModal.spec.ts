import { fakerjs, setupTest } from '@/utils/test';
import { shallowMount } from '@vue/test-utils';
import type { VueConstructor } from 'vue';
import TestResultsCreateModal from './TestResultCreateModal.vue';
import * as caseApi from '@dbco/portal-api/client/case.api';
import { fakeCreateManualTestResult } from '@/utils/__fakes__/testResults';
import type { AxiosError, AxiosResponse } from 'axios';
import type { SpyInstance } from 'vitest';
const renderComponent = setupTest(
    (localVue: VueConstructor, props: { case: string } = { case: fakerjs.string.uuid() }) => {
        return shallowMount(TestResultsCreateModal, {
            localVue,
            propsData: props,
            stubs: {
                FormulateFormWrapper: true,
                BModal: true,
            },
        });
    }
);
describe('TestResultCreateModal', () => {
    let createTestResultSpy: SpyInstance;
    beforeEach(() => {
        createTestResultSpy = vi
            .spyOn(caseApi, 'createTestResult')
            .mockImplementation(() => Promise.resolve({} as AxiosResponse));
    });
    it('should call create test result endpoint when submit form event is emitted', () => {
        const uuid = fakerjs.string.uuid();

        const wrapper = renderComponent({ case: uuid });
        const formValues = fakeCreateManualTestResult();

        wrapper.get('formulateformwrapper-stub').vm.$emit('submit', formValues);

        expect(createTestResultSpy).toBeCalledWith(uuid, formValues);
    });
    it('should emit save and hide modal when create request is succesfull', async () => {
        const uuid = fakerjs.string.uuid();
        const wrapper = renderComponent({ case: uuid });
        wrapper.vm.modal = { hide: vi.fn() };

        await wrapper.get('formulateformwrapper-stub').vm.$emit('submit', fakeCreateManualTestResult());
        await wrapper.vm.$nextTick();

        expect(wrapper.emitted('save')).toBeTruthy();
        expect(wrapper.vm.modal.hide).toBeCalled();
    });

    it('should emit cancel when modal cancel is emitted', async () => {
        const wrapper = renderComponent();
        await wrapper.vm.cancelModal();

        expect(wrapper.emitted('cancel')).toBeTruthy();
    });

    it('should put errors on vm when save fails', async () => {
        createTestResultSpy.mockImplementationOnce(() =>
            Promise.reject({
                isAxiosError: true,
                response: {
                    data: {
                        errors: {
                            field: ['Error'],
                        },
                    },
                },
            } as AxiosError)
        );
        const wrapper = renderComponent();
        await wrapper.get('formulateformwrapper-stub').vm.$emit('submit', fakeCreateManualTestResult());
        await wrapper.vm.$nextTick();

        expect(wrapper.vm.errors).toEqual({
            field: '{"warning":["Error"]}',
        });
        expect(wrapper.emitted('save')).toBeFalsy();
    });
    it('should prevent closing dialog on calling onOkHandler', () => {
        const wrapper = renderComponent();
        const event = { preventDefault: vi.fn() };
        wrapper.vm.onOkButtonHandler(event);

        expect(event.preventDefault).toBeCalled();
    });
});

import * as AppHooks from '@/components/AppHooks';

import type { Mock, SpyInstance } from 'vitest';
import { vi } from 'vitest';
import { TestResultSourceV1, TestResultTypeOfTestV1, testResultTypeOfTestV1Options } from '@dbco/enum';
import { fakeTestResult, fakeTestResultWithoutOptionalFields } from '@/utils/__fakes__/testResults';
import { fakerjs, setupTest } from '@/utils/test';
import type { IndexStoreState } from '@/store/index/indexStore';
import indexStore from '@/store/index/indexStore';

import type { AxiosResponse } from 'axios';
import { IndexStoreAction } from '@/store/index/indexStoreAction';
import { StoreType } from '@/store/storeType';
import TestResults from './TestResults.vue';
import type { VueConstructor } from 'vue';
import Vuex from 'vuex';
import { caseApi } from '@dbco/portal-api';
import { noop } from 'lodash';
import { shallowMount } from '@vue/test-utils';
import { userCanEdit } from '@/utils/interfaceState';

vi.mock('@/utils/interfaceState');

const dispatch = vi.fn();

const createComponent = setupTest(
    (localVue: VueConstructor, indexStoreState: Partial<IndexStoreState> = { testResults: [] }) => {
        const indexStoreModule = {
            ...indexStore,
            state: {
                ...indexStore.state,
                ...indexStoreState,
            },
        };

        const store = new Vuex.Store({
            modules: {
                index: indexStoreModule,
            },
        });

        store.dispatch = dispatch;

        return shallowMount(TestResults, {
            localVue,
            store,
            mocks: { $filters: { dateFnsFormat: vi.fn((value) => value) } },
            stubs: {
                TestResultCreateModal: true,
            },
        });
    }
);

afterEach(() => {
    dispatch.mockClear();
});

describe('TestResults.vue', () => {
    it('should render no results placeholder when no test results', () => {
        const wrapper = createComponent();
        expect(wrapper.text()).toContain('Er zijn (nog) geen testuitslagen bekend.');
    });

    it('should render a list of test results', () => {
        const fakeTestResults = [fakeTestResult(), fakeTestResult()];
        const indexState: Partial<IndexStoreState> = { testResults: fakeTestResults };
        const wrapper = createComponent(indexState);
        const testResults = wrapper.findAll('li');

        expect(testResults.length).toBe(2);
        testResults.wrappers.forEach((result, index) => {
            expect(result.find('.result-title').text()).toContain(
                testResultTypeOfTestV1Options.find(({ value }) => fakeTestResults[index].typeOfTest === value)?.label
            );
        });
    });

    it('should not render result details by default', () => {
        const indexState: Partial<IndexStoreState> = { testResults: [fakeTestResult()] };
        const wrapper = createComponent(indexState);
        const testResult = wrapper.findAll('li').at(0);

        expect(testResult.find('.result-details').exists()).toBe(false);
    });

    it('should show result details when item header is clicked', async () => {
        const indexState: Partial<IndexStoreState> = { testResults: [fakeTestResult()] };
        const wrapper = createComponent(indexState);

        const testResult = wrapper.findAll('li').at(0);
        await testResult.find('header').trigger('click');

        expect(testResult.find('.result-details').exists()).toBe(true);
    });

    it('should hide result details when item header is clicked again', async () => {
        const indexState: Partial<IndexStoreState> = { testResults: [fakeTestResult()] };
        const wrapper = createComponent(indexState);

        const testResult = wrapper.findAll('li').at(0);
        await testResult.find('header').trigger('click');
        await testResult.find('header').trigger('click');

        expect(testResult.find('.result-details').exists()).toBe(false);
    });

    it.each([
        ['testuitslag (type onbekend)', TestResultTypeOfTestV1.VALUE_unknown],
        ['testuitslag (type anders)', TestResultTypeOfTestV1.VALUE_custom],
    ])('should show "%s" in title when type of test is %s', (label, typeOfTest) => {
        const indexState: Partial<IndexStoreState> = {
            testResults: [{ ...fakeTestResultWithoutOptionalFields(), typeOfTest }],
        };
        const wrapper = createComponent(indexState);
        const testResult = wrapper.findAll('li').at(0);

        expect(testResult.find('.result-title').text()).toContain(label);
    });

    it('should render custom type of test when type is custom', () => {
        const customTypeOfTest = 'PaddestoelenKabouterTest';
        const indexState: Partial<IndexStoreState> = {
            testResults: [
                {
                    ...fakeTestResultWithoutOptionalFields(),
                    typeOfTest: TestResultTypeOfTestV1.VALUE_custom,
                    customTypeOfTest,
                },
            ],
        };
        const wrapper = createComponent(indexState);
        const testResult = wrapper.findAll('li').at(0);
        expect(testResult.find('.result-title').text()).toContain(customTypeOfTest);
    });

    it.each(['test-location', 'sample-location', 'monster-number', 'date-of-result', 'laboratory'])(
        'should show "-" when data for field %s is not provided',
        async (testIdForOptionalField) => {
            const indexState: Partial<IndexStoreState> = { testResults: [fakeTestResultWithoutOptionalFields()] };
            const wrapper = createComponent(indexState);

            const testResult = wrapper.findAll('li').at(0);
            await testResult.find('header').trigger('click');
            expect(testResult.find(`[data-testid="${testIdForOptionalField}"]`).text()).toBe('-');
        }
    );

    it('should fetch test results when component is mounted', () => {
        const indexState: Partial<IndexStoreState> = { uuid: fakerjs.string.uuid(), testResults: [] };
        createComponent(indexState);

        expect(dispatch).toHaveBeenCalledTimes(1);
        expect(dispatch).toHaveBeenCalledWith(`${StoreType.INDEX}/${IndexStoreAction.GET_TEST_RESULTS}`);
    });

    it('should show loading indicator when loading test results', async () => {
        const indexState: Partial<IndexStoreState> = { uuid: fakerjs.string.uuid(), testResults: [] };

        const wrapper = createComponent(indexState);
        await wrapper.vm.$nextTick();

        expect(wrapper.find('[data-testid="loading-indicator"]').exists()).toBeTruthy();
    });

    it('should show modal when add button is clicked', async () => {
        const wrapper = createComponent();
        expect(wrapper.vm.isModalVisible).toBeFalsy();

        await wrapper.findByTestId('add-test-result-button').trigger('click');

        expect(wrapper.vm.isModalVisible).toBeTruthy();
        expect(wrapper.find('testresultcreatemodal-stub').exists()).toBeTruthy();
    });

    it('should hide modal when TestResultCreateModal is canceled', async () => {
        const wrapper = createComponent();

        await wrapper.setData({ isModalVisible: true });
        await wrapper.find('testresultcreatemodal-stub').vm.$emit('cancel');
        expect(wrapper.find('testresultcreatemodal-stub').exists()).toBeFalsy();
    });

    it('should disable the add test button when userCanEdit is FALSE', () => {
        (userCanEdit as Mock).mockImplementation(() => false);
        const wrapper = createComponent();
        expect(wrapper.findByTestId('add-test-result-button').attributes().disabled).toBe('true');
    });

    describe('Delete', () => {
        let deleteSpy: SpyInstance;
        beforeEach(() => {
            deleteSpy = vi
                .spyOn(caseApi, 'deleteTestResult')
                .mockImplementationOnce(() => Promise.resolve({} as AxiosResponse));
        });

        it('should show confirmation dialog when clicking delete button', async () => {
            (userCanEdit as Mock).mockImplementation(() => true);
            const modalSpy = vi.spyOn(AppHooks, 'useModal');
            const show = vi.fn();
            modalSpy.mockImplementationOnce(() => ({
                show,
                hide: vi.fn(),
            }));
            const wrapper = createComponent({
                testResults: [fakeTestResult({ source: TestResultSourceV1.VALUE_manual })],
            });

            // should find only one delete btn
            await wrapper.find('[data-testid="testresults-btn-delete"]').trigger('click');

            expect(modalSpy).toBeCalled();
            expect(show).toBeCalled();
        });

        it('should call delete endpoint when user are you sure dialog is confirmed', async () => {
            (userCanEdit as Mock).mockImplementation(() => true);
            const modalSpy = vi.spyOn(AppHooks, 'useModal');
            let confirm: (() => void) | undefined = () => noop();
            modalSpy.mockImplementationOnce(() => ({
                show: vi.fn(({ onConfirm: callback }) => {
                    confirm = callback;
                }),
                hide: vi.fn(),
            }));
            const wrapper = createComponent({
                testResults: [fakeTestResult({ source: TestResultSourceV1.VALUE_manual })],
            });

            await wrapper.find('[data-testid="testresults-btn-delete"]').trigger('click');
            confirm();

            expect(deleteSpy).toBeCalled();
        });

        it('should only show delete button for results with manual source', () => {
            const testResults = [
                fakeTestResult({ source: TestResultSourceV1.VALUE_manual }),
                fakeTestResult({ source: TestResultSourceV1.VALUE_coronit }),
            ];
            const wrapper = createComponent({ testResults });

            const buttons = wrapper.findAll('[data-testid="testresults-btn-delete"]');

            expect(buttons.length).toEqual(1);
        });

        it('should disable the remove test button when userCanEdit is FALSE', () => {
            (userCanEdit as Mock).mockImplementation(() => false);
            const testResults = [
                fakeTestResult({ source: TestResultSourceV1.VALUE_manual }),
                fakeTestResult({ source: TestResultSourceV1.VALUE_coronit }),
            ];
            const wrapper = createComponent({ testResults });
            expect(wrapper.findByTestId('testresults-btn-delete').attributes().disabled).toBe('disabled');
        });
    });
});

import { contextApi } from '@dbco/portal-api';
import * as AppHooks from '@/components/AppHooks';
import { isNo, isYes } from '@/components/form/ts/formOptions';
import { ContextGroup } from '@/components/form/ts/formTypes';
import type { IndexStoreState } from '@/store/index/indexStore';
import indexStore from '@/store/index/indexStore';
import { createContainer, fakerjs, flushCallStack, setupTest } from '@/utils/test';
import { fakeContext } from '@/utils/__fakes__/context';
import { shallowMount } from '@vue/test-utils';
import { vi } from 'vitest';
import type { ComponentCustomProperties, VueConstructor } from 'vue';
import Vuex from 'vuex';
import ContextEditingTable from './ContextEditingTable.vue';
import type { AxiosResponse } from 'axios';
import { createTestingPinia } from '@pinia/testing';

vi.mock('@/utils/interfaceState');
vi.mock('@/utils/url');

const dataContexts = [
    { ...fakeContext(), moments: ['2021-05-26'] },
    { ...fakeContext(), moments: ['2021-06-01'] },
    { ...fakeContext(), moments: [] },
    { ...fakeContext(), moments: ['2021-05-20'] },
];

const createComponent = setupTest(
    async (localVue: VueConstructor, props?: object, indexStoreState: Partial<IndexStoreState> = {}) => {
        const indexStoreModule = {
            ...indexStore,
            state: {
                ...indexStore.state,
                uuid: fakerjs.string.uuid(),
                ...indexStoreState,
            },
        };

        const wrapper = shallowMount(ContextEditingTable, {
            localVue,
            propsData: {
                group: ContextGroup.All,
                ...props,
            },
            store: new Vuex.Store({
                modules: {
                    index: indexStoreModule,
                },
            }),
            stubs: {
                ContextEditingModal: true,
                DatePicker: true,
            },
            attachTo: createContainer(),
            pinia: createTestingPinia(),
        });
        await wrapper.vm.$nextTick();

        return wrapper;
    }
);

describe('ContextEditingTable.vue', () => {
    beforeEach(() => {
        vi.spyOn(contextApi, 'getContexts').mockImplementation(() => Promise.resolve({ contexts: dataContexts }));
    });

    it('should show the table if loaded is true', async () => {
        const wrapper = await createComponent();
        await flushCallStack();

        expect(wrapper.findComponent({ name: 'BSpinner' }).exists()).toBe(false);
        expect(wrapper.findComponent({ name: 'BTableSimple' }).exists()).toBe(true);
    });

    it('should show spinner if loaded is false', async () => {
        const wrapper = await createComponent();
        await wrapper.setData({ isLoaded: false });

        expect(wrapper.findComponent({ name: 'BSpinner' }).exists()).toBe(true);
        expect(wrapper.findComponent({ name: 'BTableSimple' }).exists()).toBe(false);
    });

    it('should load contexts on mount', async () => {
        const indexState = {
            uuid: fakerjs.string.uuid(),
        };

        const spyGetContexts = vi.spyOn(contextApi, 'getContexts');
        await createComponent({}, indexState);

        expect(spyGetContexts).toHaveBeenCalledTimes(1);
        expect(spyGetContexts).toBeCalledWith(indexState.uuid);
    });

    it('should NOT load contexts on mount if uuid is falsy', async () => {
        const indexState = {
            uuid: undefined,
        };

        const spyGetContexts = vi.spyOn(contextApi, 'getContexts');
        await createComponent({}, indexState);

        expect(spyGetContexts).not.toBeCalled();
    });

    it(`should show column source if group is NOT "${ContextGroup.Contagious}"`, async () => {
        const props = { group: ContextGroup.Source };
        const wrapper = await createComponent(props);
        await flushCallStack();

        expect(wrapper.findByTestId('source-cell-th').exists()).toBe(true);
    });

    it(`should NOT show column source if group="${ContextGroup.Contagious}"`, async () => {
        const props = { group: ContextGroup.Contagious };
        const wrapper = await createComponent(props);

        expect(wrapper.findByTestId('source-cell-th').exists()).toBe(false);
    });

    it('should reload contexts when modal is closed', async () => {
        const spyLoadContexts = vi.spyOn(contextApi, 'getContexts');
        const wrapper = await createComponent();

        await wrapper.setData({ selectedContext: dataContexts[0] });
        await flushCallStack();

        spyLoadContexts.mockClear();
        wrapper.findComponent({ name: 'ContextEditingModal' }).vm.$emit('onClose');

        expect(spyLoadContexts).toHaveBeenCalledTimes(1);
    });

    describe('tableRows: rendering', () => {
        /*
            Testing the following variants:
            [group=all]
            [group=contagious,  datesInfectious=null]
            [group=contagious,  datesInfectious=[...]]
            [group=source,      datesSource=null]
            [group=source,      datesSource=[...]]
        */
        it(`should render all contexts when passing group "${ContextGroup.All}"`, async () => {
            const wrapper = await createComponent();
            await flushCallStack();

            // Amount of table rows (including dummy row)
            expect(wrapper.findAllComponents({ name: 'ContextEditingTableRow' }).length).toBe(dataContexts.length + 1);
        });

        it(`should render all contexts while using group "${ContextGroup.Contagious}" and no infectious dates are available`, async () => {
            const props = { group: ContextGroup.Contagious };
            const wrapper = await createComponent(props);
            await flushCallStack();

            // Amount of table rows (including dummy row)
            expect(wrapper.findAllComponents({ name: 'ContextEditingTableRow' }).length).toBe(dataContexts.length + 1);
        });

        it(`should render contexts which are in [contagious period or without date] while using group "${ContextGroup.Contagious}" and infectious dates are available`, async () => {
            const props = { group: ContextGroup.Contagious };

            const indexState: Partial<IndexStoreState> = {
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

            const wrapper = await createComponent(props, indexState);
            await flushCallStack();

            // Contexts between 2021-05-29 till 2021-06-07 + 1 placeholder
            expect(wrapper.findAllComponents({ name: 'ContextEditingTableRow' }).length).toBe(3);
        });

        it(`should render all contexts while using group "${ContextGroup.Source}" and no source dates are available`, async () => {
            const props = { group: ContextGroup.Source };
            const wrapper = await createComponent(props);
            await flushCallStack();

            // Amount of table rows (including dummy row)
            expect(wrapper.findAllComponents({ name: 'ContextEditingTableRow' }).length).toBe(dataContexts.length + 1);
        });

        it(`should render contexts which are in [source period or without date] while using group "${ContextGroup.Source} and source dates are available"`, async () => {
            const props = { group: ContextGroup.Source };

            const indexState: Partial<IndexStoreState> = {
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

            const wrapper = await createComponent(props, indexState);
            await flushCallStack();

            // Amount of table rows (including dummy row)
            expect(wrapper.findAllComponents({ name: 'ContextEditingTableRow' }).length).toBe(4);
        });
    });

    describe('tableRows: persisting', () => {
        it('should create a new context if uuid is empty', async () => {
            vi.spyOn(contextApi, 'getContexts').mockImplementationOnce(() => Promise.resolve({ contexts: [] }));

            const newUuid = fakerjs.string.uuid();
            const spyCreateContext = vi
                .spyOn(contextApi, 'createContext')
                .mockImplementationOnce(() => Promise.resolve({ context: { uuid: newUuid } }));
            const spyUpdateContext = vi
                .spyOn(contextApi, 'updateContext')
                .mockImplementationOnce(() => Promise.resolve({ context: { uuid: newUuid } }));
            const wrapper = await createComponent();
            await flushCallStack();

            const tableRow = wrapper.findComponent({ name: 'ContextEditingTableRow' });
            const context = {
                uuid: '',
                label: 'New context',
            };
            tableRow.vm.$emit('change', context);

            expect(spyCreateContext).toHaveBeenCalledTimes(1);
            expect(spyUpdateContext).toHaveBeenCalledTimes(0);
        });

        it('should NOT create two contexts when quickly triggering persist if uuid is empty', async () => {
            // Instead, it should call createContext once, and then updateContext once
            vi.spyOn(contextApi, 'getContexts').mockImplementationOnce(() => Promise.resolve({ contexts: [] }));

            const newUuid = fakerjs.string.uuid();
            const spyCreateContext = vi
                .spyOn(contextApi, 'createContext')
                .mockImplementationOnce(() => Promise.resolve({ context: { uuid: newUuid } }));
            const spyUpdateContext = vi
                .spyOn(contextApi, 'updateContext')
                .mockImplementationOnce(() => Promise.resolve({ context: { uuid: newUuid } }));

            const wrapper = await createComponent();
            await flushCallStack();

            const tableRow = wrapper.findComponent({ name: 'ContextEditingTableRow' });
            const context = {
                uuid: '',
                label: 'New context',
            };

            tableRow.vm.$emit('change', context);

            // Wait longer than debounce
            await new Promise((resolve) => setTimeout(resolve, 500));
            tableRow.vm.$emit('change', context);

            expect(spyCreateContext).toHaveBeenCalledTimes(1);
            expect(spyUpdateContext).toHaveBeenCalledTimes(1);
        });

        it('should NOT create a context if uuid is empty AND savingUuids contains an empty uuid', async () => {
            const context = {
                uuid: null,
                label: 'Existing context',
            };

            vi.spyOn(contextApi, 'getContexts').mockImplementationOnce(() => Promise.resolve({ contexts: [context] }));
            const spyCreateContext = vi.spyOn(contextApi, 'createContext');
            const spyUpdateContext = vi.spyOn(contextApi, 'updateContext');
            const wrapper = await createComponent();
            await wrapper.setData({ savingUuids: [''] });
            await flushCallStack();

            const tableRow = wrapper.findComponent({ name: 'ContextEditingTableRow' });
            tableRow.vm.$emit('change', context);

            expect(spyCreateContext).toHaveBeenCalledTimes(0);
            expect(spyUpdateContext).toHaveBeenCalledTimes(0);
        });

        it('should update a context if uuid is known', async () => {
            const context = {
                uuid: fakerjs.string.uuid(),
                label: 'Existing context',
            };

            vi.spyOn(contextApi, 'getContexts').mockImplementationOnce(() => Promise.resolve({ contexts: [context] }));
            const spyCreateContext = vi.spyOn(contextApi, 'createContext');
            const spyUpdateContext = vi.spyOn(contextApi, 'updateContext');
            const wrapper = await createComponent();
            await flushCallStack();

            const tableRow = wrapper.findComponent({ name: 'ContextEditingTableRow' });
            tableRow.vm.$emit('change', context);

            expect(spyCreateContext).toHaveBeenCalledTimes(0);
            expect(spyUpdateContext).toHaveBeenCalledTimes(1);
        });

        it('should show alert if update goes wrong and no errors are returned', async () => {
            const spyAlert = vi.spyOn(window, 'alert').mockImplementationOnce(() => {});
            vi.spyOn(contextApi, 'updateContext').mockImplementationOnce(() => Promise.reject({}));

            const context = {
                uuid: fakerjs.string.uuid(),
                label: 'Existing context',
            };

            const wrapper = await createComponent();
            await flushCallStack();

            const tableRow = wrapper.findComponent({ name: 'ContextEditingTableRow' });
            tableRow.vm.$emit('change', context);
            await flushCallStack();

            expect(spyAlert).toHaveBeenCalledOnce();
        });

        it('should NOT show modal when event "delete" is emitted WITHOUT uuid', async () => {
            const modalMock = { show: vi.fn(), hide: vi.fn() };
            const modalSpy = vi.spyOn(AppHooks, 'useModal');
            modalSpy.mockImplementationOnce(() => modalMock);

            const wrapper = await createComponent();
            await flushCallStack();

            const tableRow = wrapper.findComponent({ name: 'ContextEditingTableRow' });
            tableRow.vm.$emit('delete', null);

            expect(modalMock.show).not.toHaveBeenCalled();
        });

        it('should show modal when event "delete" is emitted', async () => {
            const modalMock = { show: vi.fn(), hide: vi.fn() };
            const modalSpy = vi.spyOn(AppHooks, 'useModal');
            modalSpy.mockImplementationOnce(() => modalMock);

            const wrapper = await createComponent();
            await flushCallStack();

            const tableRow = wrapper.findComponent({ name: 'ContextEditingTableRow' });
            tableRow.vm.$emit('delete', dataContexts[0].uuid);

            expect(modalMock.show).toHaveBeenCalled();
        });

        it('should call deleteContext if delete modal is confirmed', async () => {
            const spyDeleteContext = vi
                .spyOn(contextApi, 'deleteContext')
                .mockImplementationOnce(() => Promise.resolve({} as AxiosResponse));

            const modalMock = { show: vi.fn(), hide: vi.fn() };
            const modalSpy = vi.spyOn(AppHooks, 'useModal');
            modalSpy.mockImplementationOnce(() => modalMock);

            const wrapper = await createComponent();
            await flushCallStack();

            const tableRow = wrapper.findComponent({ name: 'ContextEditingTableRow' });
            tableRow.vm.$emit('delete', dataContexts[0].uuid);

            const modalShowCall = modalMock.show.mock.lastCall?.[0] as Parameters<
                ComponentCustomProperties['$modal']['show']
            >[0];
            modalShowCall.onConfirm?.();

            expect(spyDeleteContext).toHaveBeenCalledTimes(1);
        });

        it('should NOT call deleteContext if delete modal is confirmed', async () => {
            const spyDeleteContext = vi.spyOn(contextApi, 'deleteContext');

            const modalMock = { show: vi.fn(), hide: vi.fn() };
            const modalSpy = vi.spyOn(AppHooks, 'useModal');
            modalSpy.mockImplementationOnce(() => modalMock);

            const wrapper = await createComponent();
            await flushCallStack();

            const tableRow = wrapper.findComponent({ name: 'ContextEditingTableRow' });
            tableRow.vm.$emit('delete', dataContexts[0].uuid);

            const modalShowCall = modalMock.show.mock.lastCall?.[0] as Parameters<
                ComponentCustomProperties['$modal']['show']
            >[0];
            modalShowCall.onCancel?.();

            expect(spyDeleteContext).toHaveBeenCalledTimes(0);
        });

        it.each([
            {
                nodeName: 'TR',
                classList: [],
                shouldModalShow: true,
            },
            {
                nodeName: 'TD',
                classList: [],
                shouldModalShow: true,
            },
            {
                nodeName: 'div',
                classList: ['input-group'],
                shouldModalShow: true,
            },
            {
                nodeName: 'TR',
                classList: ['input-group'],
                shouldModalShow: true,
            },
            {
                nodeName: 'div',
                classList: [],
                shouldModalShow: false,
            },
        ])(
            'should edit modal show: $shouldModalShow, when nodeName=$nodeName and classList=$classList',
            async ({ nodeName, classList, shouldModalShow }) => {
                const wrapper = await createComponent();
                await flushCallStack();
                const tableRow = wrapper.findComponent({ name: 'ContextEditingTableRow' });

                const element = document.createElement(nodeName);
                element.classList.add(...classList);

                tableRow.vm.$emit('click', dataContexts[0].uuid, { target: element });
                await wrapper.vm.$nextTick();

                expect(wrapper.findComponent({ name: 'ContextEditingModal' }).exists()).toBe(shouldModalShow);
            }
        );
    });
});

import { shallowMount } from '@vue/test-utils';
import ContactEditingTable from './ContactEditingTable.vue';
import Vuex from 'vuex';
import type { IndexStoreState } from '@/store/index/indexStore';
import indexStore from '@/store/index/indexStore';
import { taskApi } from '@dbco/portal-api';
import { isNo, isYes } from '@/components/form/ts/formOptions';
import taskStore from '@/store/task/taskStore';
import { flushCallStack, setupTest } from '@/utils/test';
import type { VueConstructor } from 'vue';
import { vi } from 'vitest';
import type { AxiosResponse } from 'axios';
import { noop } from 'lodash';
import { TaskGroup } from '@dbco/portal-api/task.dto';
import { createTestingPinia } from '@pinia/testing';

const createComponent = setupTest(
    async (
        localVue: VueConstructor,
        props?: object,
        tasks: object[] = [],
        indexStoreState: Partial<IndexStoreState> = {},
        data: object = {}
    ) => {
        if (!props) {
            props = { group: TaskGroup.Contact };
        }

        const indexStoreModule = {
            ...indexStore,
            state: {
                ...indexStore.state,
                ...indexStoreState,
            },
        };

        const taskStoreModule = {
            ...taskStore,
            state: {
                ...taskStore.state,
            },
        };

        vi.spyOn(taskApi, 'getTasks').mockImplementationOnce(() => Promise.resolve({ tasks }));

        const wrapper = shallowMount(ContactEditingTable, {
            data: () => data,
            localVue,
            propsData: props,
            attachTo: document.body,
            pinia: createTestingPinia(),
            store: new Vuex.Store({
                modules: {
                    index: indexStoreModule,
                    task: taskStoreModule,
                },
            }),
            stubs: {},
        });
        await wrapper.vm.$nextTick();
        return wrapper;
    }
);

describe('ContactEditingTable.vue', () => {
    describe('Component tests', () => {
        beforeEach(() => {
            // Reset the API mock
            global.alert = vi.fn();
            vi.resetAllMocks();
        });

        it('should show spinner while loading', async () => {
            const wrapper = await createComponent();

            const spinner = wrapper.findComponent({ name: 'BSpinner' });

            expect(spinner.exists()).toBe(true);
        });

        it('should render correct headers for contact group', async () => {
            const props = { group: TaskGroup.Contact };
            const tasks = [{ uuid: '5160bab8-3bfc-4aaf-a970-834cfe2be489', accessible: true }];
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

            const wrapper = await createComponent(props, tasks, indexState);
            await wrapper.vm.$nextTick();

            const tableHeaders = wrapper.findAllComponents({ name: 'BTh' });

            expect(tableHeaders.length).toBe(6);
            expect(tableHeaders.at(0).element.innerHTML).toContain('Naam');
            expect(tableHeaders.at(1).element.innerHTML).toContain('Notitie (optioneel)');
            expect(tableHeaders.at(2).element.innerHTML).toContain('Laatste contact');
            expect(tableHeaders.at(3).element.innerHTML).toContain('Categorie');
            expect(tableHeaders.at(4).element.innerHTML).toContain('Wie informeert');
            expect(tableHeaders.at(5).element.innerHTML).toBe('');
        });

        it('should render correct headers for non contact group', async () => {
            const props = { group: TaskGroup.PositiveSource };
            const tasks = [{ uuid: '5160bab8-3bfc-4aaf-a970-834cfe2be489' }];
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

            const wrapper = await createComponent(props, tasks, indexState);
            await wrapper.vm.$nextTick();

            const tableHeaders = wrapper.findAllComponents({ name: 'BTh' });

            expect(tableHeaders.length).toBe(5);
            expect(tableHeaders.at(0).element.innerHTML).toContain('Naam');
            expect(tableHeaders.at(1).element.innerHTML).toContain('Laatste contact');
            expect(tableHeaders.at(2).element.innerHTML).toContain('Categorie');
            expect(tableHeaders.at(3).element.innerHTML).toContain('Bron');
            expect(tableHeaders.at(4).element.innerHTML).toBe('');
        });

        it('should open contact editing modal on row click if group is contact', async () => {
            const props = { group: TaskGroup.Contact };
            const tasks = [
                { uuid: '5160bab8-3bfc-4aaf-a970-834cfe2be489', accessible: true },
                { uuid: '63070340-b39c-4a9e-a6b7-d07eb297618b', accessible: true },
            ];
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

            const wrapper = await createComponent(props, tasks, indexState);
            await wrapper.vm.$nextTick();

            const tableRows = wrapper.findAllComponents({ name: 'ContactEditingTableRow' });
            expect(tableRows).toHaveLength(3);

            expect((wrapper.vm as any).selectedTask).toBeUndefined();

            tableRows.at(0).vm.$emit('click', '5160bab8-3bfc-4aaf-a970-834cfe2be489');

            await wrapper.vm.$nextTick();

            expect((wrapper.vm as any).selectedTask).toBe(tasks[0]);

            await wrapper.vm.$nextTick();

            const contactEditingModal = wrapper.findComponent({ name: 'ContactEditingModal' });
            expect(contactEditingModal.exists()).toBe(true);

            const sourceEditingModal = wrapper.findComponent({ name: 'SourceEditingModal' });
            expect(sourceEditingModal.exists()).toBe(false);
        });

        it('should not open contact editing modal on row click if clicked element is not specifically the table row itself', async () => {
            const props = { group: TaskGroup.Contact };
            const tasks = [
                { uuid: '5160bab8-3bfc-4aaf-a970-834cfe2be489', accessible: true },
                { uuid: '63070340-b39c-4a9e-a6b7-d07eb297618b', accessible: true },
            ];
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

            const wrapper = await createComponent(props, tasks, indexState);
            await wrapper.vm.$nextTick();

            const tableRows = wrapper.findAllComponents({ name: 'ContactEditingTableRow' });
            expect(tableRows).toHaveLength(3);

            expect((wrapper.vm as any).selectedTask).toBe(undefined);

            tableRows.at(0).vm.$emit('click', '5160bab8-3bfc-4aaf-a970-834cfe2be489', {
                nodeName: 'INPUT',
                classList: { contains: vi.fn(() => false) },
            });

            expect((wrapper.vm as any).selectedTask).toBe(undefined);

            await wrapper.vm.$nextTick();

            const contactEditingModal = wrapper.findComponent({ name: 'ContactEditingModal' });
            expect(contactEditingModal.exists()).toBe(false);

            const sourceEditingModal = wrapper.findComponent({ name: 'SourceEditingModal' });
            expect(sourceEditingModal.exists()).toBe(false);
        });

        it('should not open contact editing modal on row click if clicked element is not accessible', async () => {
            const props = { group: TaskGroup.Contact };
            const tasks = [{ uuid: '5160bab8-3bfc-4aaf-a970-834cfe2be489', accessible: false }];
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

            const wrapper = await createComponent(props, tasks, indexState);
            await wrapper.vm.$nextTick();

            const tableRows = wrapper.findAllComponents({ name: 'ContactEditingTableRow' });
            expect(tableRows).toHaveLength(2);

            expect((wrapper.vm as any).selectedTask).toBe(undefined);

            tableRows.at(0).vm.$emit('click', '5160bab8-3bfc-4aaf-a970-834cfe2be489');

            expect((wrapper.vm as any).selectedTask).toBe(undefined);

            await wrapper.vm.$nextTick();

            const contactEditingModal = wrapper.findComponent({ name: 'ContactEditingModal' });
            expect(contactEditingModal.exists()).toBe(false);

            const sourceEditingModal = wrapper.findComponent({ name: 'SourceEditingModal' });
            expect(sourceEditingModal.exists()).toBe(false);
        });

        it('should open source editing modal on row click if group is not contact', async () => {
            const props = { group: TaskGroup.PositiveSource };
            const tasks = [
                { uuid: '5160bab8-3bfc-4aaf-a970-834cfe2be489', accessible: true },
                { uuid: '63070340-b39c-4a9e-a6b7-d07eb297618b', accessible: true },
            ];
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

            const wrapper = await createComponent(props, tasks, indexState);
            await wrapper.vm.$nextTick();

            const tableRows = wrapper.findAllComponents({ name: 'ContactEditingTableRow' });
            expect(tableRows).toHaveLength(3);

            expect((wrapper.vm as any).selectedTask).toBe(undefined);

            tableRows.at(0).vm.$emit('click', '5160bab8-3bfc-4aaf-a970-834cfe2be489');

            await wrapper.vm.$nextTick();

            expect((wrapper.vm as any).selectedTask).toBe(tasks[0]);

            await wrapper.vm.$nextTick();

            const contactEditingModal = wrapper.findComponent({ name: 'ContactEditingModal' });
            expect(contactEditingModal.exists()).toBe(false);

            const sourceEditingModal = wrapper.findComponent({ name: 'SourceEditingModal' });
            expect(sourceEditingModal.exists()).toBe(true);
        });

        it('should delete task on modal confirm', async () => {
            const props = { group: TaskGroup.Contact };
            const tasks = [
                { uuid: '5160bab8-3bfc-4aaf-a970-834cfe2be489', accessible: true },
                { uuid: '63070340-b39c-4a9e-a6b7-d07eb297618b', accessible: true },
            ];
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

            const spyOnDelete = vi
                .spyOn(taskApi, 'deleteTask')
                .mockImplementation(vi.fn((uuid: string) => Promise.resolve({} as AxiosResponse)));

            const wrapper = await createComponent(props, tasks, indexState);
            await wrapper.vm.$nextTick();

            const tableRows = wrapper.findAllComponents({ name: 'ContactEditingTableRow' });
            expect(tableRows).toHaveLength(3);

            // mock modal show so that it calls onConfirm method
            wrapper.vm.$modal = { show: vi.fn((modalDefenition) => modalDefenition.onConfirm()) };

            await wrapper.vm.deleteTask(tasks[0].uuid);
            await flushCallStack();

            expect(spyOnDelete).toBeCalledTimes(1);

            const updatedTableRows = wrapper.findAllComponents({ name: 'ContactEditingTableRow' });
            expect(updatedTableRows).toHaveLength(2);
        });

        it('should not show delete confirm modal if no uuid', async () => {
            const props = { group: TaskGroup.Contact };
            const tasks = [{ uuid: '5160bab8-3bfc-4aaf-a970-834cfe2be489', accessible: true }];
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

            const wrapper = await createComponent(props, tasks, indexState);
            await wrapper.vm.$nextTick();

            const modalShowMock = vi.fn();
            wrapper.vm.$modal = { show: modalShowMock };

            const tableRows = wrapper.findAllComponents({ name: 'ContactEditingTableRow' });
            expect(tableRows).toHaveLength(2);

            await wrapper.vm.deleteTask('');
            await wrapper.vm.$nextTick();

            expect(modalShowMock).not.toHaveBeenCalled();

            const updatedTableRows = wrapper.findAllComponents({ name: 'ContactEditingTableRow' });
            expect(updatedTableRows).toHaveLength(2);
        });

        it('should save new task and add new placeholder row', async () => {
            const props = { group: TaskGroup.Contact };
            const tasks = [
                { uuid: '5160bab8-3bfc-4aaf-a970-834cfe2be489', accessible: true },
                { uuid: '63070340-b39c-4a9e-a6b7-d07eb297618b', accessible: true },
            ];
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

            const response = {
                task: {
                    uuid: '06971deb-11b3-4e9c-a77d-821e9de4aab3',
                    dateOfLastExposure: '2021-06-08',
                    accessible: true,
                    label: 'new',
                },
            };
            const spyOnCreate = vi.spyOn(taskApi, 'createTask').mockImplementation(() => Promise.resolve(response));

            const wrapper = await createComponent(props, tasks, indexState);
            await wrapper.vm.$nextTick();

            const tableRows = wrapper.findAllComponents({ name: 'ContactEditingTableRow' });
            expect(tableRows).toHaveLength(3);

            await wrapper.vm.persist({ uuid: '', dateOfLastExposure: '2021-06-08', accessible: true }, 2);
            await wrapper.vm.$nextTick();

            expect(spyOnCreate).toBeCalledTimes(1);

            const updatedTableRows = wrapper.findAllComponents({ name: 'ContactEditingTableRow' });
            expect(updatedTableRows).toHaveLength(4);
        });

        it('should update task and not add new placeholder row', async () => {
            const props = { group: TaskGroup.Contact };
            const tasks = [
                { uuid: '5160bab8-3bfc-4aaf-a970-834cfe2be489', accessible: true },
                { uuid: '63070340-b39c-4a9e-a6b7-d07eb297618b', accessible: true },
            ];
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

            const response = {
                task: {
                    uuid: '5160bab8-3bfc-4aaf-a970-834cfe2be489',
                    dateOfLastExposure: '2021-06-08',
                    accessible: true,
                    label: 'new',
                },
            };
            const spyOnUpdate = vi.spyOn(taskApi, 'updateTask').mockImplementation(() => Promise.resolve(response));

            const wrapper = await createComponent(props, tasks, indexState);
            await wrapper.vm.$nextTick();

            const tableRows = wrapper.findAllComponents({ name: 'ContactEditingTableRow' });
            expect(tableRows).toHaveLength(3);

            await wrapper.vm.persist(
                { uuid: '5160bab8-3bfc-4aaf-a970-834cfe2be489', accessible: true, label: 'new' },
                0
            );
            await wrapper.vm.$nextTick();

            expect(spyOnUpdate).toBeCalledTimes(1);

            const updatedTableRows = wrapper.findAllComponents({ name: 'ContactEditingTableRow' });
            expect(updatedTableRows).toHaveLength(3);
        });

        it('should show error for generic validation error from be', async () => {
            const props = { group: TaskGroup.Contact };
            const tasks = [{ uuid: '5160bab8-3bfc-4aaf-a970-834cfe2be489', accessible: true }];
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

            const spyOnCreate = vi
                .spyOn(taskApi, 'createTask')
                .mockImplementation(() => Promise.reject({ error: 'some error' }));
            const spyOnAlert = vi.spyOn(window, 'alert');

            const wrapper = await createComponent(props, tasks, indexState);
            await wrapper.vm.$nextTick();

            const tableRows = wrapper.findAllComponents({ name: 'ContactEditingTableRow' });
            expect(tableRows).toHaveLength(2);

            await wrapper.vm.persist({ uuid: '', dateOfLastExposure: '2021-06-08', accessible: true }, 1);
            await wrapper.vm.$nextTick();

            expect(spyOnCreate).toBeCalledTimes(1);
            expect(spyOnAlert).toBeCalledTimes(1);
            expect(spyOnAlert).toBeCalledWith('Er ging iets mis bij het opslaan van de nieuwe contactpersoon');

            const updatedTableRows = wrapper.findAllComponents({ name: 'ContactEditingTableRow' });
            expect(updatedTableRows).toHaveLength(2);
        });

        it('should handle validation errors from be', async () => {
            const props = { group: TaskGroup.Contact };
            const tasks = [{ uuid: '5160bab8-3bfc-4aaf-a970-834cfe2be489', accessible: true }];
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

            const response = {
                task: {
                    uuid: '5160bab8-3bfc-4aaf-a970-834cfe2be489',
                    accessible: true,
                    label: 'new',
                },
                validationResult: {
                    fatal: {
                        failed: { 'task.label': { Required: [] } },
                        errors: { 'task.label': ['Label is verplicht.'] },
                    },
                },
            };
            const spyOnUpdate = vi.spyOn(taskApi, 'updateTask').mockImplementation(() => Promise.resolve(response));

            const wrapper = await createComponent(props, tasks, indexState);
            await wrapper.vm.$nextTick();

            const tableRows = wrapper.findAllComponents({ name: 'ContactEditingTableRow' });
            expect(tableRows).toHaveLength(2);

            await wrapper.vm.persist(
                { uuid: '5160bab8-3bfc-4aaf-a970-834cfe2be489', accessible: true, label: 'new' },
                0
            );
            await wrapper.vm.$nextTick();

            expect(spyOnUpdate).toBeCalledTimes(1);

            const updatedTableRows = wrapper.findAllComponents({ name: 'ContactEditingTableRow' });
            expect(updatedTableRows).toHaveLength(2);
            expect(updatedTableRows.at(0).attributes('errors')).toBe('task.label');
        });

        it('should trigger extra debounced persist and not submit create if currently already saving the new task', async () => {
            const props = { group: TaskGroup.Contact };
            const tasks = [
                { uuid: '5160bab8-3bfc-4aaf-a970-834cfe2be489', accessible: true },
                { uuid: '63070340-b39c-4a9e-a6b7-d07eb297618b', accessible: true },
            ];
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

            const spyOnDebounce = vi.spyOn((ContactEditingTable as any).methods, 'debouncedPersist');
            const response = {
                task: {
                    uuid: '06971deb-11b3-4e9c-a77d-821e9de4aab3',
                    dateOfLastExposure: '2021-06-08',
                    accessible: true,
                    label: 'new',
                },
            };
            const spyOnCreate = vi.spyOn(taskApi, 'createTask').mockImplementation(() => Promise.resolve(response));

            const wrapper = await createComponent(props, tasks, indexState, { savingUuids: [''] });
            await wrapper.vm.$nextTick();

            await wrapper.vm.persist({ uuid: '', dateOfLastExposure: '2021-06-08', accessible: true }, 2);
            await wrapper.vm.$nextTick();

            expect(spyOnDebounce).toBeCalledTimes(1);
            expect(spyOnCreate).toBeCalledTimes(0);
        });

        it('should reset selectedtask and reload contacts when closing modal', async () => {
            const spyOnLoad = vi.spyOn((ContactEditingTable as any).methods, 'load').mockImplementation(vi.fn(noop));

            const wrapper = await createComponent();
            spyOnLoad.mockReset();

            (wrapper.vm as any).selectedTask = '123456';
            await wrapper.vm.onModalClose();

            expect((wrapper.vm as any).selectedTask).toBe(undefined);
            expect(spyOnLoad).toBeCalledTimes(1);

            const contactEditingModal = wrapper.findComponent({ name: 'ContactEditingModal' });
            expect(contactEditingModal.exists()).toBe(false);
        });
    });
});

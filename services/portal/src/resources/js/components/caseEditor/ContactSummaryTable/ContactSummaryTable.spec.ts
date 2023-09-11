import BootstrapVue from 'bootstrap-vue';
import Vuex from 'vuex';

import type { UntypedWrapper } from '@/utils/test';
import { createContainer, flushCallStack } from '@/utils/test';
import { createLocalVue, shallowMount } from '@vue/test-utils';

import ContactSummaryTable from './ContactSummaryTable.vue';

import type { UserInfoState } from '@/store/userInfo/userInfoStore';
import userInfoStore from '@/store/userInfo/userInfoStore';
import type { TaskStoreState } from '@/store/task/taskStore';
import taskStore from '@/store/task/taskStore';
import { TaskActions } from '@/store/task/taskActions';
import i18n from '@/i18n/index';

import _ from 'lodash';
import { faker } from '@faker-js/faker';
import type { Task } from '@dbco/portal-api/task.dto';

vi.mock('@dbco/portal-api/client/copy.api');

document.execCommand = vi.fn();

const mockedFetchTasks = vi.fn();

const taskEnabled = {
    uuid: 'task-uuid',
    accessible: true,
    caseUuid: faker.string.uuid(),
    category: '2b',
    taskContext: 'taskContext 16700D91-A7F4-487E-9C0C-1F82F6C2E41F',
};

const taskDisabled = {
    uuid: 'task-uuid-1',
    accessible: false,
    caseUuid: faker.string.uuid(),
    category: '2b',
    taskContext: 'taskContext 16700D91-A7F4-487E-9C0C-1F82F6C2E41F',
};

describe('ContactSummaryTable.vue', () => {
    // mock the filters because it cannot resolve them.
    const dateFormatLongMock = vi.fn();
    const categoryFormatFullMock = vi.fn();

    const localVue = createLocalVue();
    let wrapper: UntypedWrapper;
    localVue.use(BootstrapVue);

    const getWrapper = (
        props?: object,
        userInfoState: Partial<UserInfoState> = {},
        taskState: DeepPartial<TaskStoreState> = {}
    ) => {
        const userInfoStoreModule = {
            ...userInfoStore,
            state: {
                ...userInfoStore.state,
                ...userInfoState,
            },
        };

        const taskStoreModule = {
            ...taskStore,
            state: {
                ...taskStore.state,
                ...taskState,
            },
            actions: {
                [TaskActions.FETCH_TASKS]: mockedFetchTasks,
            },
        };

        return shallowMount(ContactSummaryTable, {
            store: new Vuex.Store({
                modules: {
                    userInfo: userInfoStoreModule,
                    task: taskStoreModule,
                },
            }),
            localVue,
            i18n,
            propsData: props,
            attachTo: createContainer(), // supresses [BootstrapVue warn]: tooltip - The provided target is no valid HTML element.
            mocks: {
                $filters: {
                    dateFormatLong: dateFormatLongMock,
                    categoryFormat: categoryFormatFullMock,
                },
            },
        });
    };

    const mockGetTasks = (mockTaskArray?: Partial<Task>[]) => {
        const tasks: Array<Partial<Task>> = [];
        mockTaskArray?.forEach((mockTask) => {
            const caseUuid = _.uniqueId();
            tasks.push({
                uuid: 'task-uuid',
                accessible: true,
                caseUuid,
                category: '2b',
                taskContext: `taskContext ${caseUuid}`,
                ...mockTask,
            });
        });
        if (tasks.length === 0)
            tasks.push({
                uuid: 'task-uuid',
                accessible: true,
                caseUuid: '16700D91-A7F4-487E-9C0C-1F82F6C2E41F',
                category: '2b',
                taskContext: `taskContext 16700D91-A7F4-487E-9C0C-1F82F6C2E41F'`,
            });
        return tasks;
    };

    it('should load new contacts after uuid update', async () => {
        const taskState: DeepPartial<TaskStoreState> = {
            tasks: [taskEnabled],
        };

        // GIVEN ContactSummaryTable is loaded with a uuid
        wrapper = getWrapper({ caseUuid: 'case-uuid' }, {}, taskState);

        // AND task is shown
        await flushCallStack();

        expect(wrapper.find('.task-subtitle').element.innerHTML).toBe(taskEnabled.taskContext);

        // WHEN the uuid of the ContactSummaryTable prop updates
        mockedFetchTasks.mockReset();
        await wrapper.setProps({ caseUuid: 'different-uuid' });
        await flushCallStack();

        // THEN get tasks is called
        expect(mockedFetchTasks).toHaveBeenCalledTimes(1);
    });

    it('should open ContactEditingModal when a task row is pressed', async () => {
        const taskState: DeepPartial<TaskStoreState> = {
            tasks: [taskEnabled],
        };

        // GIVEN the table shows a case with a task
        wrapper = getWrapper({ caseUuid: 'case-uuid' }, {}, taskState);
        // AND the modal is not visible
        await wrapper.vm.$nextTick();
        expect(wrapper.findComponent({ name: 'ContactEditingModal' }).exists()).toBe(false);

        // WHEN the row of the task is pressed
        wrapper.vm.editContact(taskEnabled);

        // AND the modal of the task is opened
        await wrapper.vm.$nextTick();
        expect(wrapper.findComponent({ name: 'ContactEditingModal' }).exists()).toBe(true);
    });

    it('should style accessible tasks and inaccessible tasks differently', async () => {
        const taskState: DeepPartial<TaskStoreState> = {
            tasks: [taskEnabled, taskDisabled],
        };

        // GIVEN the component has both accessible and inaccessible tasks
        wrapper = getWrapper({ caseUuid: 'case-uuid' }, {}, taskState);

        // WHEN the table renders these tasks
        await flushCallStack();

        const taskElements = wrapper.findAll('.task');

        // AND accessible tasks are styled differently than inaccessible tasks
        expect(taskElements.at(0).classes()).toContain('task-accessible');
        expect(taskElements.at(1).classes()).toContain('task-inaccessible');
    });

    it('should render derived label if task has one and label if not', async () => {
        const mockTasks = [
            { derivedLabel: 'derivedLabel' },
            { derivedLabel: 'derivedLabel', label: 'label' },
            { label: 'label' },
        ];

        const mockedTasks = mockGetTasks(mockTasks);
        const taskState: DeepPartial<TaskStoreState> = {
            tasks: mockedTasks,
        };

        // GIVEN has tasks with labels and derived labels
        wrapper = getWrapper({ caseUuid: 'case-uuid' }, {}, taskState);

        // WHEN the table renders these tasks
        await flushCallStack();

        const taskElements = wrapper.findAll('.task');

        // AND a tasks with only a derivedLabel shows derived label
        const labelElement = 'btd-stub > span > strong';
        expect(taskElements.at(0).find(labelElement).text()).toBe('derivedLabel');
        // AND a tasks with both a label and derived label shows derived label
        expect(taskElements.at(1).find(labelElement).text()).toBe('derivedLabel');
        // AND a tasks with only a label shows label
        expect(taskElements.at(2).find(labelElement).text()).toBe('label');
    });

    it('should render task progress state differently', async () => {
        const mockTasks = [
            { progress: 'complete' },
            { progress: 'contactable' },
            { progress: 'foo' },
            { progress: 'bar' },
        ];
        const generatedTasks = mockGetTasks(mockTasks);
        const taskState: DeepPartial<TaskStoreState> = {
            tasks: generatedTasks,
        };

        // GIVEN has tasks with different progress
        wrapper = getWrapper({ caseUuid: 'case-uuid' }, {}, taskState);

        // WHEN the table renders these tasks
        await flushCallStack();

        // AND it shows 'Gegevens compleet' on complete tasks
        const taskElements = wrapper.findAll('.task');
        const statusElement = '.td--data-status > .d-flex > div';
        expect(taskElements.at(0).find(statusElement).text()).toBe('Gegevens compleet');
        // AND it shows 'Voldoende gegevens' on contactable tasks
        expect(taskElements.at(1).find(statusElement).text()).toBe('Voldoende gegevens');
        // AND it shows 'Gegevens incompleet' on all others
        expect(taskElements.at(2).find(statusElement).text()).toBe('Gegevens incompleet');
        expect(taskElements.at(3).find(statusElement).text()).toBe('Gegevens incompleet');
    });
});

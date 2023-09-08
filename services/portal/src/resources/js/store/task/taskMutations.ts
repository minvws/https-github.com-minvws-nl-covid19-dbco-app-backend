import type { Task } from '@dbco/portal-api/task.dto';
import type { TaskStoreState } from './taskStore';

export enum TaskMutations {
    SET_TASKS = 'SET_TASKS',
    SET_SELECTED_TASK_UUID = 'SET_SELECTED_TASK_UUID',
    EMPTY_SELECTED_TASK_UUID = 'EMPTY_SELECTED_TASK_UUID',
}

export const taskMutations = {
    [TaskMutations.SET_TASKS](state: TaskStoreState, value: Task[]) {
        state.tasks = value;
    },
    [TaskMutations.EMPTY_SELECTED_TASK_UUID](state: TaskStoreState) {
        state.selectedTaskUuid = undefined;
    },
    [TaskMutations.SET_SELECTED_TASK_UUID](state: TaskStoreState, selectedTask: Task) {
        if (!selectedTask || !selectedTask.uuid || !selectedTask.accessible) return;
        state.selectedTaskUuid = selectedTask.uuid;
    },
};

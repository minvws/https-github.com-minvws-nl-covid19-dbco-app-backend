import { taskApi } from '@dbco/portal-api';
import type { Commit, Dispatch } from 'vuex';
import { TaskMutations } from './taskMutations';
import { SharedActions } from '../actions';
import type { TaskStoreState } from './taskStore';
import type { RootStoreState } from '@/store';
import type { TaskUnionDTO } from '@dbco/schema/unions';

export enum TaskActions {
    UPDATE_TASK_FRAGMENT = 'UPDATE_TASK_FRAGMENT',
    FETCH_TASKS = 'FETCH_TASKS',
}

const fetchTasks = async ({ commit }: { commit: Commit }, caseUuid: string) => {
    const response = await taskApi.getTasks(caseUuid, 'contact', true);
    commit(TaskMutations.SET_TASKS, response.tasks);
};

export const updateTaskFragment = async (
    { dispatch }: { dispatch: Dispatch; state: TaskStoreState; rootState: RootStoreState },
    payload: TaskUnionDTO
) => await dispatch(SharedActions.UPDATE_FORM_VALUE, payload);

export const taskActions = {
    [TaskActions.FETCH_TASKS]: fetchTasks,
    [TaskActions.UPDATE_TASK_FRAGMENT]: updateTaskFragment,
};

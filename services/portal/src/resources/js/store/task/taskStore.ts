import { sharedMutations } from '../mutations';
import { taskMutations } from './taskMutations';
import { sharedActions } from '../actions';
import { StoreType } from '../storeType';
import { taskActions } from './taskActions';
import type { Task } from '@dbco/portal-api/task.dto';
import type { TaskUnionDTO } from '@dbco/schema/unions';
export interface TaskStoreState {
    uuid?: string;
    loaded: boolean;
    errors: AnyObject;
    fragments: Partial<TaskUnionDTO>;
    tasks: Task[];
    selectedTaskUuid?: string;
}

const getDefaultState = (): TaskStoreState => ({
    uuid: undefined,
    loaded: false,
    errors: {},
    fragments: {},
    tasks: [],
    selectedTaskUuid: undefined,
});

export default {
    namespaced: true,
    state: getDefaultState(),
    actions: {
        ...sharedActions(StoreType.TASK),
        ...taskActions,
    },
    mutations: {
        ...sharedMutations(getDefaultState),
        ...taskMutations,
    },
    getters: {
        errors: (state: TaskStoreState) => state.errors,
        forms: (state: TaskStoreState) => state,
        fragments: (state: TaskStoreState) => state.fragments,
        uuid: (state: TaskStoreState) => state.uuid,
        tasks: (state: TaskStoreState) => state.tasks,
        selectedTaskUuid: (state: TaskStoreState) => state.selectedTaskUuid,
        // This getter is being used in actions.updateFormValue (getters.type)
        type: () => 'tasks',
        category: (state: TaskStoreState) => state.fragments?.general?.category,
        dateOfLastExposure: (state: TaskStoreState) => state.fragments?.general?.dateOfLastExposure,
    },
};

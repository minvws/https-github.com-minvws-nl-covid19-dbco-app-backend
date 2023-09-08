import type { RootStoreState } from '@/store';
import { createLocalVue } from '@vue/test-utils';
import Vuex from 'vuex';
import { SharedActions } from '../actions';
import type { IndexStoreState } from '../index/indexStore';
import indexStore from '../index/indexStore';
import type { TaskStoreState } from './taskStore';
import taskStore from './taskStore';

const indexStateInitial = {
    uuid: 'D210269F-483B-4B1F-9309-2EB2C5F1E9C9',
    loaded: false,
    meta: {
        schemaVersion: 1,
        bcoStatus: 'string',
        indexStatus: 'string',
        pseudoBsnGuid: 'string',
        caseId: 'string',
        name: 'string',
    },
    errors: {},
    fragments: {},
    messages: [],
    contexts: [],
    tasks: {},
    calendarData: [],
    calendarViews: {},
};

describe('taskActions.ts', () => {
    const localVue = createLocalVue();
    localVue.use(Vuex);

    const getStore = (taskStoreState: Partial<TaskStoreState>, indexStoreState: IndexStoreState) => {
        const taskStoreModule = {
            // actions: taskActions,
            ...taskStore,
            state: {
                ...taskStore.state,
                ...taskStoreState,
            },
            actions: {
                ...taskStore.actions,
                [SharedActions.UPDATE_FORM_VALUE]: vi.fn(),
            },
        };

        const indexStoreModule = {
            ...indexStore,
            state: {
                ...indexStore.state,
                ...indexStoreState,
            },
        };

        return new Vuex.Store<RootStoreState>({
            modules: {
                task: taskStoreModule,
                index: indexStoreModule,
            },
        });
    };

    it('should dispatch task/UPDATE_FORM_VALUE with payload', async () => {
        const taskState = {
            loaded: false,
            errors: {},
            fragments: {},
        };

        const store = getStore(taskState, indexStateInitial);

        const spyStoreDispatch = vi.spyOn(store, 'dispatch');

        const payload = {};
        await store.dispatch('task/UPDATE_TASK_FRAGMENT', payload);

        expect(spyStoreDispatch).toHaveBeenCalledWith(`task/${SharedActions.UPDATE_FORM_VALUE}`, payload);
    });
});

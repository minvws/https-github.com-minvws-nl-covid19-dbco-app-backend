import { fakerjs, flushCallStack } from '@/utils/test';
import { createLocalVue } from '@vue/test-utils';
import { useTaskTableStore } from './taskTableStore';
import Vuex from 'vuex';
import indexStore from '@/store/index/indexStore';
import { TaskGroupV1 } from '@dbco/enum';
import { useStore } from '@/utils/vuex';
import { StoreType } from '@/store/storeType';
import { SharedActions } from '@/store/actions';
import { createPinia, setActivePinia } from 'pinia';

const localVue = createLocalVue();
localVue.use(Vuex);
setActivePinia(createPinia());

const store = new Vuex.Store({
    modules: {
        index: {
            ...indexStore,
            state: {
                ...indexStore.state,
            },
        },
    },
});

vi.mock('@/utils/vuex', () => ({
    useStore: () => store,
}));

describe('taskTableStore', () => {
    afterEach(() => {
        vi.resetAllMocks();
    });

    it('should fallback when meta is not loaded', () => {
        const store = useTaskTableStore();

        const expectedInitialState = {
            [TaskGroupV1.VALUE_contact]: 0,
            [TaskGroupV1.VALUE_positivesource]: 0,
            [TaskGroupV1.VALUE_symptomaticsource]: 0,
        };

        expect(store.taskCounts).toStrictEqual(expectedInitialState);
    });

    it('should output meta values when tasks not loaded', async () => {
        const vuexStore = useStore();
        const store = useTaskTableStore();

        const expectedTaskCounts = {
            [TaskGroupV1.VALUE_contact]: fakerjs.number.int({ min: 1, max: 100 }),
            [TaskGroupV1.VALUE_positivesource]: fakerjs.number.int({ min: 1, max: 100 }),
            [TaskGroupV1.VALUE_symptomaticsource]: fakerjs.number.int({ min: 1, max: 100 }),
        };

        await vuexStore.dispatch(`${StoreType.INDEX}/${SharedActions.CHANGE}`, {
            path: 'meta',
            values: {
                taskCount: { ...expectedTaskCounts },
            },
        });

        await flushCallStack();

        expect(store.taskCounts).toStrictEqual(expectedTaskCounts);
    });

    it('should output tasks values when tasks loaded', async () => {
        const vuexStore = useStore();
        const store = useTaskTableStore();

        const expectedTasks = fakerjs.custom.typedArray({});

        await vuexStore.dispatch(`${StoreType.INDEX}/${SharedActions.CHANGE}`, {
            path: 'tasks',
            values: {
                [TaskGroupV1.VALUE_contact]: [...expectedTasks],
                [TaskGroupV1.VALUE_positivesource]: [...expectedTasks],
                [TaskGroupV1.VALUE_symptomaticsource]: [...expectedTasks],
            },
        });

        await flushCallStack();

        expect(store.taskCounts[TaskGroupV1.VALUE_contact]).toBe(expectedTasks.length);
        expect(store.taskCounts[TaskGroupV1.VALUE_positivesource]).toBe(expectedTasks.length);
        expect(store.taskCounts[TaskGroupV1.VALUE_symptomaticsource]).toBe(expectedTasks.length);
    });
});

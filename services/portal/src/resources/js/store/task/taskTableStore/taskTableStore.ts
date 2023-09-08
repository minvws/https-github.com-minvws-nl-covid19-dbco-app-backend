import { useStore } from '@/utils/vuex';
import { TaskGroupV1 } from '@dbco/enum';
import { defineStore } from 'pinia';
import { computed } from 'vue';
import type { IndexStoreState } from '@/store/index/indexStore';
import { StoreType } from '@/store/storeType';

export const useTaskTableStore = defineStore('taskTable', () => {
    const store = useStore();
    const tasks = computed(() => store.getters[`${StoreType.INDEX}/tasks`] as IndexStoreState['tasks']);
    const meta = computed(() => store.getters[`${StoreType.INDEX}/meta`]);

    const defaultTaskCount = {
        [TaskGroupV1.VALUE_contact]: 0,
        [TaskGroupV1.VALUE_positivesource]: 0,
        [TaskGroupV1.VALUE_symptomaticsource]: 0,
    };

    const taskCounts = computed<typeof defaultTaskCount>(() => {
        const initialTaskCounts = (meta.value?.taskCount || defaultTaskCount) as typeof defaultTaskCount;

        return {
            contact: tasks.value.contact?.length || initialTaskCounts.contact,
            positivesource: tasks.value.positivesource?.length || initialTaskCounts.positivesource,
            symptomaticsource: tasks.value.symptomaticsource?.length || initialTaskCounts.symptomaticsource,
        };
    });

    return {
        taskCounts,
    };
});

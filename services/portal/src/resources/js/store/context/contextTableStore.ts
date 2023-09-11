import { ContextGroup } from '@/components/form/ts/formTypes';
import { infectiousDates, sourceDates } from '@/utils/case';
import { areDatesInContextGroup, classifyDates } from '@/utils/context';
import { parseDate } from '@/utils/date';
import { useStore } from '@/utils/vuex';
import type { Context } from '@dbco/portal-api/context.dto';
import type { CovidCaseUnionDTO } from '@dbco/schema/unions';
import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import { StoreType } from '../storeType';
import type { Range } from '@/utils/caseDateRanges';

const filterOnGroup = (
    group: ContextGroup,
    contexts: Context[],
    sourcePeriod: Range | null,
    contagiousPeriod: Range | null
) => {
    return contexts.filter((context) => {
        if (!context.moments?.length) return true;

        const dates = context.moments.map((dateString) => parseDate(dateString, 'yyyy-MM-dd'));
        return areDatesInContextGroup(classifyDates(dates, sourcePeriod, contagiousPeriod), group);
    });
};

export const useContextTableStore = defineStore('contextTableStore', () => {
    // Mutated from the outide.. bit nasty
    const initalLoadComplete = ref(false);
    const store = useStore();

    const fragments = computed(() => store.getters[`${StoreType.INDEX}/fragments`] as CovidCaseUnionDTO);
    const contexts = computed(() => store.getters[`${StoreType.INDEX}/contexts`] as Context[]);
    const meta = computed(() => store.getters[`${StoreType.INDEX}/meta`]);

    const sourcePeriod = computed(() => sourceDates(fragments.value));
    const contagiousPeriod = computed(() => infectiousDates(fragments.value));

    const contagiousContexts = computed(() =>
        filterOnGroup(ContextGroup.Contagious, contexts.value, sourcePeriod.value, contagiousPeriod.value)
    );
    const sourceContexts = computed(() =>
        filterOnGroup(ContextGroup.Source, contexts.value, sourcePeriod.value, contagiousPeriod.value)
    );

    const contagiousTableLength = computed(() => contagiousContexts.value.length);
    const metaContagiousCount = computed(() => meta.value.contextContagiousCount);

    const contagiousContextCount = computed(() =>
        initalLoadComplete.value ? contagiousTableLength.value : metaContagiousCount.value
    );

    return {
        allContexts: contexts,
        sourceContexts,
        contagiousContextCount,
        contagiousContexts,
        initalLoadComplete,
        contagiousTableLength,
        metaContagiousCount,
    };
});

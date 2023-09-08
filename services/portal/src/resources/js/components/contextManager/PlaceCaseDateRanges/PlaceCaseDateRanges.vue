<template>
    <div v-if="!placeCase.moments.length">-</div>
    <div
        class="date-ranges"
        v-else-if="
            !canDetermineInfectiousPeriod(
                placeCase.symptoms.hasSymptoms,
                placeCase.dateOfSymptomOnset,
                placeCase.dateOfTest
            )
        "
    >
        <p>
            <Icon name="question-mark" class="tw-mr-1 tw-w-[10px] tw-shrink-0" role="img" title="unknown dates" />{{
                getSummaryTextForDates(placeCase.moments.map((point) => point.startDate))
            }}
        </p>
    </div>
    <div v-else class="date-ranges">
        <p v-for="pointGroup in pointGroups">
            <Icon
                :name="pointGroup[0].icon ?? 'diamond-grey'"
                class="tw-mr-1 tw-w-2 tw-shrink-0"
                role="img"
                :title="pointGroup[0].label"
            />
            {{ getSummaryTextForDates(pointGroup.map((point) => point.startDate)) }}
        </p>
    </div>
</template>

<script lang="ts">
import type { PropType } from 'vue';
import { computed, defineComponent } from 'vue';
import type { PlaceCasesResponse } from '@dbco/portal-api/place.dto';
import { getSummaryTextForDates } from '@/utils/date';
import { canDetermineInfectiousPeriod } from '@/utils/case';
import { Icon } from '@dbco/ui-library';
import type { CalendarDateRange } from '@dbco/portal-api/case.dto';

export default defineComponent({
    props: {
        placeCase: { type: Object as PropType<PlaceCasesResponse>, required: true },
    },
    components: {
        Icon,
    },
    setup({ placeCase }) {
        const pointGroups = computed<{ [key: string]: CalendarDateRange[] }>(() => {
            const groups: { [key: string]: CalendarDateRange[] } = {};
            placeCase.moments.forEach((moment: CalendarDateRange) => {
                if (!moment.icon) return;
                if (!groups.hasOwnProperty(moment.icon)) groups[moment.icon] = [];

                groups[moment.icon].push(moment);
            });

            return groups;
        });

        return {
            canDetermineInfectiousPeriod,
            getSummaryTextForDates,
            pointGroups,
        };
    },
});
</script>

<style lang="scss" scoped>
@import './resources/scss/_variables.scss';

.date-ranges {
    p {
        display: flex;
        align-items: center;
        margin: 0;
    }
}
</style>

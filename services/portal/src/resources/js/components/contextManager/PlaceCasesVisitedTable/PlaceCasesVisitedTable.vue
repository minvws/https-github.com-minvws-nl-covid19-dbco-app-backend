<template>
    <div id="place-cases-visited-table" class="table-responsive-lg">
        <div class="tw-flex tw-flex-initial tw-justify-between tw-flex-col lg:tw-flex-row">
            <Heading as="h3" size="sm" class="tw-my-4">
                {{ $filters.dateFnsFormat(visibleDays[0], 'd MMM') }} -
                {{ $filters.dateFnsFormat(visibleDays[visibleDays.length - 1], 'd MMM yyyy') }}
            </Heading>
            <div class="tw-flex tw-flex-initial tw-my-4 tw-flex-col lg:tw-flex-row lg:tw-space-x-4">
                <div>
                    <Icon name="circle-blue" class="tw-mr-1 tw-w-2 tw-shrink-0" role="img" title="source dates" />
                    Binnen de bronperiode
                </div>
                <div>
                    <Icon name="range-overlap" class="tw-mr-1 tw-w-2 tw-shrink-0" role="img" title="overlap date" />
                    Op de overgangsdag (bron- naar besmettelijk)
                </div>
                <div>
                    <Icon name="square-red" class="tw-mr-1 tw-w-2 tw-shrink-0" role="img" title="infectious dates" />
                    Binnen de besmettelijke periode
                </div>
                <div>
                    <span
                        name="infection-date"
                        class="tw-inline-block tw-mr-1 tw-w-2 tw-h-2 tw-w-2 tw-rounded-[2px] tw-rotate-45 tw-bg-gray-500"
                    />
                    Periode onbekend
                </div>
            </div>
        </div>
        <table class="table tw-border table-rounded table-hover table--clickable table-ggd table--align-start">
            <thead>
                <tr>
                    <th scope="col" class="tw-uppercase">
                        {{ t('components.placeCasesTable.headers.index') }}
                    </th>
                    <th scope="col" class="tw-uppercase">
                        {{ t('components.placeCasesTable.headers.relationContext') }}
                    </th>
                    <th
                        scope="col"
                        class="dates tw-w-9 tw-text-center"
                        v-for="(day, index) in visibleDays"
                        :key="`day_${index}`"
                    >
                        <span class="tw-inline-block tw-max-w-min tw-text-center">
                            {{ dateHeader(day, index) }}
                        </span>
                    </th>
                </tr>
            </thead>
            <tbody class="place-cases-table-content">
                <tr
                    v-for="placeCase in cases"
                    :key="placeCase.uuid"
                    class="custom-link"
                    @click="rowClicked(placeCase.uuid, placeCase.token)"
                >
                    <td scope="row">
                        {{ placeCase.caseId }}
                        <TooltipButton
                            v-if="showExclamationBubble(placeCase.causeForConcern)"
                            position="right"
                            content="Index maakt zich zorgen over de situatie op locatie"
                            icon="exclamation-mark-speech-bubble"
                            class="tw-w-[14px] tw-ml-1 tw-text-red-600"
                        />
                        <TooltipButton
                            v-if="showTombstone(placeCase.isDeceased)"
                            content="Index is overleden"
                            icon="tombstone"
                            class="tw-w-[14px] tw-ml-1"
                        />
                        <p class="name">{{ formattedName(placeCase) }}</p>
                    </td>
                    <td class="relation-context">{{ contextRelationshipV1Options[placeCase.relationContext] }}</td>
                    <td
                        scope="row"
                        v-for="(day, index) in visibleDays"
                        :key="`day_${day.getDay() + index}`"
                        :class="['tw-align-middle', 'tw-text-center', { 'tw-bg-gray-50': index % 2 === 0 }]"
                    >
                        <Icon
                            v-if="inRange(day, placeCase)"
                            :name="rangeIcon(day, placeCase)"
                            class="tw-inline-block tw-h-4 tw-w-4"
                            role="img"
                            :id="`range-icon-${$filters.dateFnsFormat(day, 'd-MM')}`"
                            :title="rangeIcon(day, placeCase)"
                            data-testid="infection-date"
                        />
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="mt-3 mb-3">
            <InfiniteLoading :identifier="placeCasesTable.infiniteId" @infinite="onListsInfinite" spinner="spiral">
                <div slot="spinner tw-pt-3 tw-pb-8">
                    <Spinner />
                    <span class="infinite-loader">{{ t('components.placeCasesTable.hints.load_more') }}</span>
                </div>
                <div slot="no-more">
                    {{ t('components.placeCasesTable.hints.all_index_cases_loaded') }}
                </div>
                <div class="tw-bg-white tw-text-center tw-pt-3 tw-pb-8" slot="no-results">
                    {{ t('components.placeCasesTable.hints.no_index_cases') }}
                </div>
            </InfiniteLoading>
        </div>
    </div>
</template>

<script lang="ts">
import type { PropType } from 'vue';
import { usePlaceCasesStore } from '@/store/cluster/clusterStore';
import { useI18n } from 'vue-i18n-composable';
import { computed, defineComponent, unref } from 'vue';
import type { StateChanger } from 'vue-infinite-loading';
import InfiniteLoading from 'vue-infinite-loading';
import type { PlaceCasesResponse } from '@dbco/portal-api/place.dto';
import { eachDayOfInterval, isFirstDayOfMonth, isSameDay } from 'date-fns';
import { useFilters } from '@/filters/useFilters';
import { Spinner, Heading, Icon, TooltipButton } from '@dbco/ui-library';
import type { IconName } from '@dbco/ui-library';
import { formattedName, rowClicked, showTombstone, showExclamationBubble } from '@/utils/context';
import { contextRelationshipV1Options } from '@dbco/enum';

export default defineComponent({
    props: {
        placeUuid: { type: String as PropType<string>, required: true },
    },
    components: { InfiniteLoading, Spinner, Heading, Icon, TooltipButton },
    setup(props) {
        const store = usePlaceCasesStore();
        const cases = computed(() => store.cases);
        const { t } = useI18n();
        const { dateFnsFormat } = useFilters();
        const placeCasesTable = computed(() => store.table);

        const onListsInfinite = async ($state: StateChanger) => {
            const table = unref(placeCasesTable);
            await store.fetchCases(props.placeUuid);

            if (cases.value.length) $state?.loaded();

            if (table.lastPage !== table.page) {
                store.incrementTablePage();
            } else {
                $state?.complete();
            }
        };

        const visibleDays = computed(() => {
            const endDate = new Date(); // today
            const startDate = new Date(new Date().setDate(endDate.getDate() - 30));

            return eachDayOfInterval({ start: startDate, end: endDate });
        });

        const dateHeader = (day: Date, index: number) => {
            if (isFirstDayOfMonth(day) || index === 0) {
                return dateFnsFormat(day, 'd MMM');
            } else {
                return dateFnsFormat(day, 'd');
            }
        };

        const inRange = (day: Date, placeCase: PlaceCasesResponse): boolean => {
            return placeCase.moments.some((moment) => {
                return isSameDay(new Date(moment.startDate), day);
            });
        };

        const rangeIcon = (day: Date, placeCase: PlaceCasesResponse): IconName => {
            const point = placeCase.moments.find((moment) => isSameDay(new Date(moment.startDate), day));
            if (!point) return 'check-mark-circle';

            return point.icon as IconName;
        };

        return {
            t,
            rowClicked,
            onListsInfinite,
            showExclamationBubble,
            cases,
            placeCasesTable,
            formattedName,
            visibleDays,
            dateHeader,
            inRange,
            rangeIcon,
            contextRelationshipV1Options,
            showTombstone,
        };
    },
});
</script>

<style lang="scss" scoped>
@import './resources/scss/_variables.scss';

#place-cases-visited-table table th {
    vertical-align: top;
}
.dates {
    padding: 0.5rem 0;
}
.relation-context {
    vertical-align: top;
}
.name {
    color: $dark-grey;
    font-weight: 400;
    margin: 0;
}
</style>

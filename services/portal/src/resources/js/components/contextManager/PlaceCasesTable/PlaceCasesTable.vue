<template>
    <div id="place-cases-table">
        <table class="table tw-border table-rounded table-hover table--clickable table-ggd table--align-start">
            <thead>
                <tr>
                    <th scope="col">{{ t('components.placeCasesTable.headers.identifier') }}</th>
                    <th scope="col">{{ t('components.placeCasesTable.headers.age') }}</th>
                    <th scope="col">{{ t('components.placeCasesTable.headers.ezdDate') }}</th>
                    <th scope="col">
                        {{ t('components.placeCasesTable.headers.vaccination') }}
                        <TooltipButton icon="question-mark-tooltip">
                            {{ $t('components.placeCasesTable.tooltips.vaccination') }}
                        </TooltipButton>
                    </th>
                    <th scope="col">
                        {{ t('components.placeCasesTable.headers.dateVisited') }}
                        <TooltipButton icon="question-mark-tooltip">
                            <p class="tw-m-0">
                                <Icon
                                    name="circle-blue"
                                    class="tw-mr-1 tw-w-2 tw-shrink-0"
                                    role="img"
                                    title="source dates"
                                />Binnen de bronperiode
                            </p>
                            <p class="tw-m-0">
                                <Icon
                                    name="square-red"
                                    class="tw-mr-1 tw-w-2 tw-shrink-0"
                                    role="img"
                                    title="infectious dates"
                                />Binnen de besmettelijke periode
                            </p>
                        </TooltipButton>
                    </th>
                    <th scope="col">{{ t('components.placeCasesTable.headers.sections') }}</th>
                    <th scope="col">{{ t('components.placeCasesTable.headers.relationContext') }}</th>
                </tr>
            </thead>
            <tbody>
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
                    <td>{{ renderAge(placeCase.dateOfBirth) }}</td>
                    <td>
                        {{
                            renderDateOfSymptomOnset(
                                placeCase.dateOfSymptomOnset,
                                placeCase.dateOfTest,
                                placeCase.createdAt
                            )
                        }}
                    </td>
                    <td>
                        {{ renderVaccinations(placeCase.vaccinationCount, placeCase.mostRecentVaccinationDate) }}
                    </td>
                    <td><PlaceCaseDateRanges :placeCase="placeCase" /></td>
                    <td v-if="placeCase.sections && placeCase.sections.length" class="tw-break-all">
                        <PlaceCaseSections :sections="placeCase.sections" />
                    </td>
                    <td v-else>-</td>
                    <td>{{ contextRelationshipV1Options[placeCase.relationContext] }}</td>
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
                    {{ t('components.placeCasesTable.hints.all_cases_loaded', { date: lastCaseDate }) }}
                </div>
                <div class="tw-bg-white tw-text-center tw-pt-3 tw-pb-8" slot="no-results">
                    {{ t('components.placeCasesTable.hints.no_cases') }}
                </div>
            </InfiniteLoading>
        </div>
    </div>
</template>

<script lang="ts">
import { usePlaceCasesStore } from '@/store/cluster/clusterStore';
import type { PropType } from 'vue';
import { computed, defineComponent, unref } from 'vue';
import { useI18n } from 'vue-i18n-composable';
import type { StateChanger } from 'vue-infinite-loading';
import InfiniteLoading from 'vue-infinite-loading';
import { calculateAge, formatDate, parseDate } from '@/utils/date';
import { Spinner, Icon, TooltipButton } from '@dbco/ui-library';
import { formattedName, rowClicked, showTombstone, showExclamationBubble } from '@/utils/context';
import PlaceCaseDateRanges from '@/components/contextManager/PlaceCaseDateRanges/PlaceCaseDateRanges.vue';
import PlaceCaseSections from '@/components/contextManager/PlaceCaseSections/PlaceCaseSections.vue';
import { contextRelationshipV1Options } from '@dbco/enum';

export default defineComponent({
    props: {
        placeUuid: { type: String as PropType<string>, required: true },
    },
    components: {
        InfiniteLoading,
        PlaceCaseSections,
        PlaceCaseDateRanges,
        Spinner,
        Icon,
        TooltipButton,
    },
    setup(props) {
        const store = usePlaceCasesStore();
        const cases = computed(() => store.cases);
        const placeCasesTable = computed(() => store.table);
        const lastCaseDate = computed(() => {
            /* We currently rely on the default sorting for this date. Last case in the table is now the oldest.
             ** If we introduce other sorting options to this table,
             ** we would need to receive the oldest date from the BE (preferred).
             ** Or calculate it in the FE. */
            const lastCase = cases.value[cases.value.length - 1];
            return lastCase
                ? renderDateOfSymptomOnset(lastCase.dateOfSymptomOnset, lastCase.dateOfTest, lastCase.createdAt)
                : '';
        });

        const { t } = useI18n();

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

        const renderDateOfSymptomOnset = (
            dateOfSymptomOnset: string | null,
            dateOfTest: string | null,
            createdAt: string
        ) => {
            if (dateOfSymptomOnset) {
                return formatDate(parseDate(dateOfSymptomOnset, 'yyyy-MM-dd'), 'dd MMM yyyy');
            } else if (dateOfTest) {
                return formatDate(parseDate(dateOfTest, 'yyyy-MM-dd'), 'dd MMM yyyy');
            }
            return formatDate(parseDate(createdAt), 'dd MMM yyyy');
        };

        const renderVaccinations = (count: string | number | null, date: string | null) => {
            if (!count && !date) {
                return '-';
            }

            const vaccinationCount = count ? `${count}x` : '-';
            const vaccinationDate = date ? formatDate(parseDate(date, 'yyyy-MM-dd'), 'dd MMM yyyy') : '-';

            return `${vaccinationCount} (${vaccinationDate})`;
        };

        const renderAge = (dateOfBirth: string) => (dateOfBirth ? calculateAge(new Date(dateOfBirth)) : '-');

        return {
            cases,
            formattedName,
            lastCaseDate,
            onListsInfinite,
            placeCasesTable,
            renderAge,
            renderDateOfSymptomOnset,
            rowClicked,
            renderVaccinations,
            showExclamationBubble,
            showTombstone,
            t,
            contextRelationshipV1Options,
        };
    },
});
</script>

<style lang="scss" scoped>
@import './resources/scss/_variables.scss';

#place-cases-table tbody td {
    vertical-align: top;
}
.name {
    color: $dark-grey;
    font-weight: 400;
    margin: 0;
}
</style>

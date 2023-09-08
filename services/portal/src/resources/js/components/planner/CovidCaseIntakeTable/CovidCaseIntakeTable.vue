<template>
    <div>
        <DbcoFilterGroup v-if="!loading" @filter="(filterOn) => updateFilter(filterOn)">
            <DbcoFilter
                type="caseLabels"
                label="Met:"
                :searchable="true"
                search-placeholder="Zoek label"
                :selected="selectedFilter.caseLabels"
                :options="[
                    { value: null, label: 'Alle labels' },
                    ...caseLabels.map((c) => {
                        return { value: c.uuid, label: c.label };
                    }),
                ]"
            ></DbcoFilter>
        </DbcoFilterGroup>
        <div v-show="items.length !== 0">
            <BTable
                class="table-wrapper bg-white table-rounded"
                :items="items"
                :fields="fields"
                @sort-changed="sortTable"
                thead-tr-class="heading-row"
                no-local-sorting
            >
                <template #cell(cat1Count)="data">
                    {{ data.item.cat1Count > 0 ? data.item.cat1Count : '-' }}
                </template>
                <template #cell(estimatedCat2Count)="data">
                    {{ data.item.estimatedCat2Count > 0 ? data.item.estimatedCat2Count : '-' }}
                </template>
                <template #cell(dateOfBirth)="data">
                    {{ age(data.item.dateOfBirth) }}
                    <i
                        v-if="needsAttention(data.item.dateOfBirth)"
                        class="icon icon--error-warning"
                        v-b-tooltip.hover
                        title="Deze case heeft extra aandacht nodig"
                    />
                </template>
                <template #cell(dateOfSymptomOnset)="data">
                    {{ $filters.dateFormatDeltaTime(data.item.dateOfSymptomOnset, 'yyyy-MM-dd') }}
                </template>
                <template #cell(labels)="data">
                    {{ caseLabelsString(data.item.labels) }}
                </template>
                <template #cell(createdAt)="data">
                    {{ formatDate(parseDate(data.item.createdAt), 'd MMMM yyyy HH:mm') }}
                </template>
            </BTable>
        </div>
        <div
            v-if="items.length === 0 && !loading"
            class="empty-placeholder d-flex justify-content-center align-items-center bg-white text-center pt-5 pb-5"
        >
            Er zijn geen cases.
        </div>
        <div class="mb-3">
            <InfiniteLoading :identifier="infiniteId" @infinite="infiniteHandler" spinner="spiral">
                <div slot="spinner">
                    <Spinner />
                    <span class="infinite-loader">Meer cases laden</span>
                </div>
                <div slot="no-more"></div>
                <div slot="no-results"></div>
            </InfiniteLoading>
        </div>
    </div>
</template>

<script lang="ts">
import { caseListApi } from '@dbco/portal-api';
import type { CaseLabel, IntakeCase } from '@dbco/portal-api/caseList.dto';
import DbcoFilter from '@/components/DbcoFilterGroup/DbcoFilter/DbcoFilter.vue';
import DbcoFilterGroup from '@/components/DbcoFilterGroup/DbcoFilterGroup.vue';
import CovidCaseStatusBadge from '@/components/planner/CovidCaseStatusBadge/CovidCaseStatusBadge.vue';
import { usePlanner } from '@/store/planner/plannerStore';
import { calculateAge, formatDate, formatFromNow, parseDate } from '@/utils/date';
import type { CancelTokenSource } from 'axios';
import axios from 'axios';
import type { BvTableCtxObject, BvTableField } from 'bootstrap-vue';
import { mapState } from 'pinia';
import { defineComponent } from 'vue';
import type { StateChanger } from 'vue-infinite-loading';
import InfiniteLoading from 'vue-infinite-loading';
import { ListSortOptions } from '@dbco/portal-api/client/caseList.api';
import { Spinner } from '@dbco/ui-library';

interface Data {
    loading: boolean;
    cancelToken: CancelTokenSource | null;
    page: number;
    items: IntakeCase[];
    fields: (BvTableField & { key: string })[];
    infiniteId: number;
    selectedFilter: Record<string, string>;
    sortBy: ListSortOptions | undefined;
    sortDirection: 'asc' | 'desc' | undefined;
}

export default defineComponent({
    name: 'CovidCaseIntakeTable',
    components: {
        CovidCaseStatusBadge,
        InfiniteLoading,
        DbcoFilterGroup,
        DbcoFilter,
        Spinner,
    },
    data() {
        return {
            loading: false,
            cancelToken: null,
            page: 1,
            items: [],
            fields: [
                { key: 'identifier', label: 'Monsternummer', tdClass: 'text-body' },
                { key: 'cat1Count', label: 'Huisgenoten', sortable: true },
                { key: 'estimatedCat2Count', label: 'Contacten', sortable: true },
                { key: 'dateOfBirth', label: 'Leeftijd' },
                { key: 'dateOfSymptomOnset', label: 'EZD', sortable: true },
                { key: 'labels', label: 'Label' },
                { key: 'createdAt', label: 'Moment van invullen' },
            ],
            infiniteId: Date.now(),
            selectedFilter: {},
            sortBy: undefined,
            sortDirection: undefined,
        } as Data;
    },
    computed: {
        ...mapState(usePlanner, ['caseLabels']),
        triggerResetFields() {
            return [
                // On change of sort
                this.sortBy,
                this.sortDirection,
            ];
        },
    },
    watch: {
        triggerResetFields(newVal, oldVal) {
            if (newVal === oldVal) return;

            // Check if there are any previous pending requests
            if (this.cancelToken) {
                this.cancelToken.cancel('Operation canceled due to new request.');
            }

            this.resetTable();
        },
    },
    destroyed() {
        if (this.cancelToken) {
            this.cancelToken.cancel('Operation canceled due to unmount.');
        }
    },
    methods: {
        caseLabelsString(labels: CaseLabel[]) {
            return labels.map((caseLabel) => caseLabel.label).join(', ');
        },
        parseDate,
        formatDate,
        formatFromNow,
        age(dateOfBirth: string | null) {
            if (!dateOfBirth) return null;

            return calculateAge(new Date(dateOfBirth));
        },
        needsAttention(dateOfBirth: string | null) {
            const age = this.age(dateOfBirth);
            if (!age) return false;

            return age >= 70 || age <= 16;
        },
        resetTable() {
            this.page = 1;
            this.items = [];

            // Reset vue-infinite-loading component
            this.infiniteId = Date.now();
        },
        async infiniteHandler($state: StateChanger) {
            this.loading = true;
            this.cancelToken = axios.CancelToken.source();

            const data = await caseListApi.getIntakeCases(
                this.page,
                undefined,
                this.selectedFilter,
                this.sortBy,
                this.sortDirection,
                this.cancelToken.token
            );

            if (data.data.length) {
                this.items.push(...data.data);

                if (data.lastPage === this.page) {
                    $state.complete();
                } else {
                    $state.loaded();
                    this.page += 1;
                }
            } else {
                $state.complete();
            }

            this.loading = false;
        },
        updateFilter(selectedFilterOption: { value: string; type: string }) {
            this.selectedFilter[selectedFilterOption.type] = selectedFilterOption.value;
            this.resetTable();
        },
        sortTable(e: BvTableCtxObject) {
            const sortOptions: string[] = Object.values(ListSortOptions);
            this.sortDirection = this.sortBy !== e.sortBy ? 'asc' : this.sortDirection === 'asc' ? 'desc' : 'asc';
            this.sortBy = e.sortBy && sortOptions.includes(e.sortBy) ? (e.sortBy as ListSortOptions) : undefined;
            this.resetTable();
        },
    },
});
</script>
<style lang="scss" scoped>
@import './resources/scss/_variables.scss';

.status {
    display: inline-block;
    width: 10px;
    height: 10px;
    @apply tw-rounded-full;
}

.empty-placeholder {
    min-height: 400px;
}

.form-checkbox-wrapper {
    display: flex;
    align-items: center;
}

::v-deep {
    .table {
        margin-bottom: 130px;
        border: 1px solid $lightest-grey;
        border-top: none;
        border-bottom-left-radius: 4px;
        border-bottom-right-radius: 4px;

        .heading-row {
            & > [aria-sort='none'] {
                background-image: none;
            }

            & > [aria-sort='ascending'] {
                background-image: url('@images/arrow-up.svg');
            }

            & > [aria-sort='descending'] {
                background-image: url('@images/arrow-down.svg');
            }
        }

        thead {
            th {
                color: $lighter-grey;
                text-transform: uppercase;
            }
        }

        tbody {
            td {
                position: relative;
                color: $light-grey;
                vertical-align: middle;
            }
        }
    }
}
</style>

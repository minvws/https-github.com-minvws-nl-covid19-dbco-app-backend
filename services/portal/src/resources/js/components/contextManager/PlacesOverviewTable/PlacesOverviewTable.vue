<template>
    <div>
        <div class="mt-2">
            <DbcoFilterGroup @filter="updateFilters">
                <DbcoFilter
                    v-if="shouldEnableCategoryFilter"
                    type="category"
                    :label="$tc('components.placesOverviewTable.filters.category.label')"
                    :selected="selectedFilters.category"
                    :searchable="!listType"
                    :options="categoryFilterOptions"
                ></DbcoFilter>
                <DbcoFilter
                    type="isVerified"
                    :label="$tc('components.placesOverviewTable.filters.isVerified.label')"
                    :searchable="false"
                    :selected="selectedFilters.isVerified"
                    :options="verifiedFilterOptions"
                ></DbcoFilter>
            </DbcoFilterGroup>
            <BTableSimple class="table table-ggd tw-shadow-sm with-sort">
                <colgroup>
                    <!-- This column can be set to a fixed with -->
                    <col v-if="isSelectEnabled" width="2%" />
                    <col class="w-20" />
                    <col class="w-15" />
                    <col class="w-15" />
                    <col class="w-10" />
                    <col class="w-15" />
                    <col class="w-10" />
                </colgroup>
                <BThead>
                    <BTr>
                        <BTh v-if="isSelectEnabled" scope="col" class="checkbox">
                            <div class="table-checkbox-wrapper">
                                <BFormCheckbox
                                    v-model="allSelected"
                                    :indeterminate="indeterminate"
                                    @change="toggleAll"
                                />
                            </div>
                        </BTh>
                        <BTh scope="col">{{ $t('components.placesOverviewTable.headers.description') }}</BTh>
                        <BTh scope="col">{{ $t('components.placesOverviewTable.headers.category') }}</BTh>
                        <BTh
                            scope="col"
                            :aria-sort="ariaSort(sortOptions.INDEX_COUNT)"
                            @click="onSort(sortOptions.INDEX_COUNT)"
                        >
                            {{ $t('components.placesOverviewTable.headers.indexCount') }}
                            <i
                                class="icon icon--questionmark"
                                v-b-tooltip.hover
                                :title="`${$t('components.placesOverviewTable.tooltips.indexCount')}`"
                            />
                        </BTh>
                        <BTh
                            scope="col"
                            :aria-sort="ariaSort(sortOptions.INDEX_COUNT_SINCE_RESET)"
                            @click="onSort(sortOptions.INDEX_COUNT_SINCE_RESET)"
                        >
                            {{ $t('components.placesOverviewTable.headers.indexCountSinceReset') }}
                            <i
                                class="icon icon--questionmark"
                                v-b-tooltip.hover
                                :title="`${$t('components.placesOverviewTable.tooltips.indexCountSinceReset')}`"
                            />
                        </BTh>
                        <BTh
                            scope="col"
                            :aria-sort="ariaSort(sortOptions.LAST_INDEX_PRESENCE)"
                            @click="onSort(sortOptions.LAST_INDEX_PRESENCE)"
                        >
                            {{ $t('components.placesOverviewTable.headers.lastIndexPresence') }}
                            <i
                                class="icon icon--questionmark"
                                v-b-tooltip.hover
                                :title="`${$t('components.placesOverviewTable.tooltips.lastIndexPresence')}`"
                        /></BTh>
                        <BTh scope="col">{{ $t('components.placesOverviewTable.headers.lastCheck') }}</BTh>
                        <BTh scope="col">{{ $t('components.placesOverviewTable.headers.actions') }}</BTh>
                    </BTr>
                </BThead>
                <BTbody class="tw-text-gray-600">
                    <BTr
                        v-for="place in places"
                        :key="place.uuid"
                        data-testid="place-table-row"
                        @click="rowClicked(place.uuid, $event)"
                    >
                        <BTd v-if="isSelectEnabled" class="checkbox">
                            <div class="table-checkbox-wrapper">
                                <BFormCheckbox
                                    :id="place.uuid"
                                    v-model="selected"
                                    v-bind="$as.any({ value: place.uuid })"
                                />
                            </div>
                        </BTd>
                        <BTd>
                            <div class="d-flex flex-column align-items-start py-2">
                                <div class="tw-flex">
                                    <strong class="tw-font-medium tw-text-gray-950">{{ place.label }} </strong>
                                    <Icon
                                        v-if="place.isVerified"
                                        data-testid="verified-label"
                                        name="verified"
                                        alt="verified"
                                        v-b-tooltip.hover
                                        :title="$tc('components.placesOverviewTable.tooltips.verified')"
                                        role="img"
                                        class="tw-ml-1 tw-h-5 tw-inline-block"
                                    />
                                    <button
                                        aria-label="situation-number-icon"
                                        v-if="place.situationNumbers && place.situationNumbers.length"
                                        id="situations-button"
                                        data-testid="situations-button"
                                        type="button"
                                        :disabled="!hasPermission(PermissionV1.VALUE_placeEdit)"
                                        @click="editPlace(place)"
                                    >
                                        <i
                                            class="icon icon--situation"
                                            v-b-tooltip.hover
                                            :title="`${$t('components.placesOverviewTable.tooltips.situations')}`"
                                        />
                                    </button>
                                </div>
                                <span>{{ place.addressLabel }}</span>
                            </div>
                        </BTd>
                        <BTd>
                            <div class="tw-flex tw-items-center">
                                <i
                                    :class="[
                                        'icon',
                                        'icon--xl',
                                        'icon--m0',
                                        'm-0',
                                        'mr-3',
                                        $filters.placeCategoryImageClass(place.category),
                                    ]"
                                />
                                {{ place.categoryLabel }}
                            </div>
                        </BTd>
                        <BTd aria-label="place-index-count">{{ place.indexCount }}</BTd>
                        <BTd aria-label="place-index-count-since-reset">
                            <div
                                v-if="place.indexCountSinceReset !== null"
                                :class="{ cluster: place.indexCountSinceReset > 0 }"
                            >
                                <i v-if="place.indexCountSinceReset > 0" class="icon icon--m0 icon--caret-up"></i
                                >{{ place.indexCountSinceReset }}
                            </div>
                            <span v-else>-</span>
                        </BTd>
                        <BTd aria-label="place-index-last-presence">{{
                            formattedLastIndexPresence(place.lastIndexPresence)
                        }}</BTd>
                        <BTd aria-label="place-index-count-reset-at">{{
                            formattedUpdatedAtDate(place.indexCountResetAt)
                        }}</BTd>
                        <BTd>
                            <div class="actions">
                                <ResetIndexCount
                                    :indexCountSinceReset="place.indexCountSinceReset || 0"
                                    :placeUuid="place.uuid"
                                    @reset="clearClusterInTable(place)"
                                />
                                <BDropdown
                                    right
                                    no-caret
                                    id="place-actions-dropdown"
                                    :text="`${$t('components.placesOverviewTable.actions.main')}`"
                                    variant="link"
                                    class="pl-0"
                                >
                                    <template #button-content>
                                        <div
                                            aria-label="context-actions"
                                            class="hover:tw-bg-gray-50 tw-w-8 tw-h-8 tw-flex tw-items-center tw-justify-center tw-rounded"
                                        >
                                            <i class="icon--options icon--center tw-h-5 tw-w-5 tw-inline-block" />
                                        </div>
                                    </template>
                                    <BDropdownItem
                                        v-if="hasPermission(PermissionV1.VALUE_placeEdit)"
                                        @click="editPlace(place)"
                                        data-testid="edit-place"
                                    >
                                        {{ $t('components.placesOverviewTable.actions.edit') }}
                                    </BDropdownItem>
                                    <BDropdownItem
                                        v-if="hasPermission(PermissionV1.VALUE_placeMerge)"
                                        @click="mergePlace(place)"
                                        data-testid="merge-place"
                                    >
                                        {{ $t('components.placesOverviewTable.actions.merge') }}
                                    </BDropdownItem>
                                    <BDropdownItem
                                        v-if="hasPermission(PermissionV1.VALUE_placeVerify)"
                                        @click="verifyPlace(place)"
                                        data-testid="verify-place"
                                    >
                                        {{
                                            place.isVerified
                                                ? $t('components.placesOverviewTable.actions.unverify')
                                                : $t('components.placesOverviewTable.actions.verify')
                                        }}
                                    </BDropdownItem>
                                </BDropdown>
                            </div>
                        </BTd>
                    </BTr>
                </BTbody>
            </BTableSimple>
        </div>
        <div v-if="places.length === 0 && !loading" class="bg-white text-center pt-5 pb-5">
            <template v-if="!query">{{ $t('components.placesOverviewTable.hints.no_places') }}</template>
            <template>{{ translatedHintForNoResults }}</template>
        </div>
        <div class="mb-3">
            <InfiniteLoading
                ref="infiniteLoading"
                :identifier="table.infiniteId"
                @infinite="infiniteHandler"
                spinner="spiral"
            >
                <div slot="spinner">
                    <Spinner />
                    <span class="infinite-loader">{{ $t('components.placesOverviewTable.hints.load_more') }}</span>
                </div>
                <div slot="no-more"></div>
                <div slot="no-results"></div>
            </InfiniteLoading>
        </div>
        <PlacesEditModal v-if="editingPlace" @saved="resetTable" @hide="editingPlace = false" />
    </div>
</template>

<script lang="ts">
import { mapGetters } from 'vuex';
import InfiniteLoading from 'vue-infinite-loading';
import { placeApi } from '@dbco/portal-api';
import { defineComponent } from 'vue';
import type { PlaceDTO, PlaceTable } from '@dbco/portal-api/place.dto';
import { PlaceSortOptions } from '@dbco/portal-api/place.dto';
import type { PaginatedRequestOptions } from '@dbco/portal-api/pagination';
import type { ContextListViewV1, ContextCategoryGroupV1 } from '@dbco/enum';
import { PermissionV1, contextCategoryGroupV1Options } from '@dbco/enum';
import _ from 'lodash';
import PlacesEditModal from '../PlacesEdit/PlacesEditModal/PlacesEditModal.vue';
import ResetIndexCount from '@/components/contextManager/ResetIndexCount/ResetIndexCount.vue';
import { formatDate, parseDate, formatFromNow } from '@/utils/date';
import DbcoFilterGroup from '@/components/DbcoFilterGroup/DbcoFilterGroup.vue';
import type { FilterOption } from '@/components/DbcoFilterGroup/DbcoFilter/DbcoFilter.vue';
import DbcoFilter from '@/components/DbcoFilterGroup/DbcoFilter/DbcoFilter.vue';
import { Icon, Spinner } from '@dbco/ui-library';
import { VerifiedFilter } from '@dbco/portal-api/client/place.api';

const categoriesForListView = (listView: ContextListViewV1 | undefined): typeof contextCategoryGroupV1Options =>
    listView ? contextCategoryGroupV1Options.filter(({ view }) => view === listView) : contextCategoryGroupV1Options;

const defaultFilter = () =>
    ({
        isVerified: VerifiedFilter.All,
        category: 'all',
    }) as Filters;

interface Filters {
    isVerified: VerifiedFilter;
    category: ContextCategoryGroupV1 | 'all';
}
export default defineComponent({
    components: {
        InfiniteLoading,
        PlacesEditModal,
        ResetIndexCount,
        DbcoFilterGroup,
        DbcoFilter,
        Icon,
        Spinner,
    },
    props: {
        query: {
            type: String,
            required: false,
        },
        listType: {
            type: String as () => ContextListViewV1 | undefined,
            required: false,
        },
    },
    data() {
        return {
            PermissionV1,
            places: [] as PlaceDTO[],
            selected: [] as string[],
            selected2: [] as string[],
            sortOptions: PlaceSortOptions,
            table: {
                infiniteId: Date.now(),
                page: 1,
                perPage: 30,
            } as PlaceTable,
            editingPlace: false,
            loading: false,
            allSelected: false,
            indeterminate: false,
            selectedFilters: defaultFilter(),
            verifiedFilterOptions: [
                { value: VerifiedFilter.All, label: this.$t('components.placesOverviewTable.filters.isVerified.all') },
                {
                    value: VerifiedFilter.Verified,
                    label: this.$t('components.placesOverviewTable.filters.isVerified.verified'),
                },
                {
                    value: VerifiedFilter.Unverified,
                    label: this.$t('components.placesOverviewTable.filters.isVerified.unverified'),
                },
            ] as FilterOption[],
        };
    },
    computed: {
        ...mapGetters({
            hasPermission: 'userInfo/hasPermission',
        }),
        isSelectEnabled() {
            return (
                this.hasPermission(PermissionV1.VALUE_placeEdit) ||
                this.hasPermission(PermissionV1.VALUE_placeMerge) ||
                this.hasPermission(PermissionV1.VALUE_placeMerge) ||
                this.hasPermission(PermissionV1.VALUE_placeVerify)
            );
        },
        shouldEnableCategoryFilter() {
            return categoriesForListView(this.listType).length > 1;
        },
        categoryFilterOptions() {
            return [
                { value: 'all', label: this.$t('components.placesOverviewTable.filters.category.all') },
                ...categoriesForListView(this.listType),
            ] as FilterOption[];
        },
        translatedHintForNoResults() {
            const query = this.query || this.$t('components.placesOverviewTable.hints.search_term_fallback').toString();
            return this.$t('components.placesOverviewTable.hints.no_results', { query }).toString();
        },
    },
    watch: {
        selected(newValue) {
            this.setIndeterminate(newValue);
            this.$emit(
                'selectedPlacesUpdated',
                this.places.filter(({ uuid }) => this.selected.includes(uuid))
            );
        },
        query: {
            handler(updatedQuery) {
                this.selectedFilters = defaultFilter();

                if (updatedQuery.length > 2 || updatedQuery.length === 0) {
                    this.table.page = 1;
                    this.places = [];
                    this.debouncedInfiniteHandler();
                }
            },
        },
        async listType() {
            this.selectedFilters = defaultFilter();
            // Listtype changes when the route changes. Request new data for new listtype.
            this.resetTable();
            await this.infiniteHandler();
        },
    },
    methods: {
        setIndeterminate(newValue: string[]) {
            this.allSelected = newValue.length === this.places.length && newValue.length > 0;
            this.indeterminate = newValue.length > 0 && !this.allSelected;
        },
        toggleAll(checked: boolean) {
            this.selected = checked ? this.places.slice().map((place) => place.uuid) : [];
        },
        rowClicked(placeUuid: string, event: Event) {
            const el = event.target as HTMLElement;
            switch (el.tagName) {
                case 'A':
                case 'BUTTON':
                case 'I':
                case 'INPUT':
                case 'LABEL':
                    break;
                default:
                    window.location.assign(`/editplace/${placeUuid}`);
            }
        },
        resetTable() {
            this.table.page = 1;
            this.places = [];
            this.selected = [];
            this.editingPlace = false;

            // Reset vue-infinite-loading component
            this.table.infiniteId = Date.now();
        },
        clearClusterInTable(place: PlaceDTO) {
            const p = {
                ...place,
                indexCountSinceReset: 0,
                indexCountResetAt: new Date().toISOString(),
            };
            const indexInTable = this.places.findIndex((i) => i.uuid === p.uuid);
            this.places.splice(indexInTable, 1, p);
        },
        editPlace(place: PlaceDTO) {
            this.$store.commit('place/SET_PLACE', place);
            this.editingPlace = true;
        },
        emptySelected() {
            // Do not change this methods name/purpose, other components are calling it directly
            this.selected = [];
        },
        ariaSort(sort: PlaceSortOptions) {
            if (this.table.sort != sort || !this.table.order) return 'none';
            return this.table.order === 'asc' ? 'ascending' : 'descending';
        },
        onSort(sort: PlaceSortOptions) {
            let order: PaginatedRequestOptions['order'] =
                this.table.sort === sort && this.table.order === 'desc' ? 'asc' : 'desc';
            this.table = { ...this.table, ...{ order, sort } };
            this.resetTable();
        },
        async verifyPlace(place: PlaceDTO) {
            const p = {
                ...place,
            };
            await this.$store.dispatch('place/TOGGLE_VERIFICATION', p);
            const indexInTable = this.places.findIndex((i) => i.uuid === p.uuid);
            this.places.splice(indexInTable, 1, p);
        },
        // eslint-disable-next-line @typescript-eslint/no-explicit-any
        debouncedInfiniteHandler: _.debounce(function (this: any) {
            this.$refs.infiniteLoading.stateChanger.reset();
        }, 300),
        async infiniteHandler() {
            if (this.loading) return;

            this.loading = true;
            const { isVerified, category } = this.selectedFilters;

            const { sort, order, perPage } = this.table;

            const response = await placeApi.getPlacesByListType(
                this.table.page,
                this.listType,
                this.query && this.query?.length > 2 ? this.query : undefined,
                isVerified,
                category,
                sort,
                order,
                perPage
            );

            if (response.data.length) {
                this.places.push(...response.data);

                if (response.lastPage === this.table.page) {
                    (this.$refs.infiniteLoading as InfiniteLoading).stateChanger.complete();
                } else {
                    (this.$refs.infiniteLoading as InfiniteLoading).stateChanger.loaded();
                    this.table.page += 1;
                }
            } else {
                (this.$refs.infiniteLoading as InfiniteLoading).stateChanger.complete();
            }

            this.loading = false;
        },
        mergePlace(place: PlaceDTO) {
            this.$emit('merge', place);
        },
        formattedUpdatedAtDate(date: any) {
            if (!date) return '-';
            const localizedString = formatFromNow(parseDate(date));
            return this.$t('utils.date.ago', { timeDifference: localizedString }).toString();
        },
        formattedLastIndexPresence(date: any) {
            if (!date) return '-';
            return formatDate(parseDate(date, 'yyyy-MM-dd'), 'dd MMMM yyyy');
        },
        updateFilters<T extends keyof Filters>(updatedFilter: { type: T; value: Filters[T] }) {
            this.selectedFilters[updatedFilter.type] = updatedFilter.value;
            this.resetTable();
        },
    },
});
</script>

<style lang="scss" scoped>
@import './resources/scss/_variables.scss';
.actions {
    display: flex;
    width: max-content;
    gap: 0.875rem;
}

.cluster {
    display: flex;
    align-items: center;
    color: $bco-red;
    i {
        background-color: $bco-red;
        margin-right: 3px;
        width: 0.625em;
    }
}

.checkbox {
    position: relative;
}

.table-ggd tbody tr {
    cursor: pointer;

    &:hover {
        color: $black;
        background-color: $table-hover-bg;
    }

    td:first-child {
        padding: 0 $padding-sm;
    }
}

#situations-button {
    appearance: none;
    outline: none;
    border: none;
    background-color: transparent;
    padding: 0;
    padding-top: 2px;
    line-height: 1;
    margin-left: 2px;
    .icon {
        margin: 0;
    }
}
</style>

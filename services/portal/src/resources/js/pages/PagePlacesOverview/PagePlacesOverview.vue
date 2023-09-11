<template>
    <div class="container-xl pt-5">
        <div class="d-flex align-items-baseline justify-content-between">
            <h2 class="font-weight-normal pb-0 mb-4">{{ $as.defined(organisation).name }} / <b>Contexten</b></h2>
            <BButton variant="primary" @click="creating = true" data-testid="contextAanmaken"
                >&#65291; Context aanmaken</BButton
            >
        </div>
        <div class="form-chapter">
            <label for="place-search-query"
                ><strong>Zoek een context binnen je GGD-regio (in het portaal)</strong>
            </label>
            <b-input-group class="search-field mt-0">
                <template #append>
                    <SearchIcon />
                </template>
                <b-form-input
                    data-testid="place-search-query"
                    id="place-search-query"
                    v-model="query"
                    type="text"
                    size="lg"
                    placeholder="Vul naam, postcode, adres of vluchtnummer in"
                ></b-form-input>
            </b-input-group>
        </div>
        <TabList>
            <RouterTab to="/places">Alle</RouterTab>
            <RouterTab v-for="(value, key) in listViewOptions" :key="key" :to="`/places/${key}`">{{ value }}</RouterTab>
        </TabList>
        <div class="tw-mt-8">
            <div class="table-notification">Deze gegevens worden om de paar uur bijgewerkt</div>
            <router-view
                ref="placesOverviewTable"
                :query="query"
                @selectedPlacesUpdated="updateSelectedPlaces"
                @merge="mergePlaces"
                :selected="selected"
            />
        </div>
        <BulkActionBar
            @hide="emptySelected"
            v-if="selected.length > 0"
            :text="selected.length > 1 ? `${selected.length} contexten` : `1 context`"
        >
            <BButton
                v-if="hasPermission(PermissionV1.VALUE_placeVerify)"
                variant="outline-light"
                size="sm"
                class="btn--text-small"
                @click="verifyPlaces"
                data-testid="verify-places"
            >
                Verifiëren
            </BButton>
            <BButton
                v-if="hasPermission(PermissionV1.VALUE_placeMerge)"
                variant="outline-light"
                size="sm"
                class="btn--text-small"
                @click="mergePlaces()"
                data-testid="merge-places"
                >Samenvoegen</BButton
            >
        </BulkActionBar>
        <PlacesMergeModal
            v-if="placesToMerge.length > 0"
            :places="placesToMerge"
            :lockedTargetUuid="lockedMergeTargetUuid"
            @cancel="placesToMerge = []"
            @success="mergeSuccess"
        />
        <PlaceSelectModal
            v-if="creating"
            @hide="creating = false"
            @creationCompleted="onCreationCompleted"
            @placeCreated="onPlaceCreated"
        />
    </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { placeApi } from '@dbco/portal-api';
import type { PlaceDTO } from '@dbco/portal-api/place.dto';
import PlacesMergeModal from '@/components/contextManager/PlacesMergeModal/PlacesMergeModal.vue';
import PlacesOverviewTable from '@/components/contextManager/PlacesOverviewTable/PlacesOverviewTable.vue';
import PlaceSelectModal from '@/components/modals/PlaceSelectModal/PlaceSelectModal.vue';
import BulkActionBar from '@/components/utils/BulkActionBar/BulkActionBar.vue';
import { PermissionV1, ContextListViewV1, contextListViewV1Options } from '@dbco/enum';
import { mapRootGetters } from '@/utils/vuex';
import { RouterTab, TabList } from '@dbco/ui-library';
import SearchIcon from '@icons/search.svg?vue';

interface Data {
    creating: boolean;
    lockedMergeTargetUuid?: string;
    PermissionV1: typeof PermissionV1;
    placesToMerge: PlaceDTO[];
    query?: string;
    selected: PlaceDTO[];
    ContextListViewV1: typeof ContextListViewV1;
    listViewOptions: typeof contextListViewV1Options;
}

type PlacesOverviewTableMethods = NonNullable<(typeof PlacesOverviewTable)['methods']>;

export default defineComponent({
    components: {
        BulkActionBar,
        PlaceSelectModal,
        PlacesMergeModal,
        PlacesOverviewTable,
        TabList,
        RouterTab,
        SearchIcon,
    },
    data() {
        return {
            ContextListViewV1,
            creating: false,
            lockedMergeTargetUuid: undefined,
            PermissionV1,
            placesToMerge: [],
            query: undefined,
            selected: [],
            listViewOptions: contextListViewV1Options,
        } as Data;
    },
    computed: {
        ...mapRootGetters({
            hasPermission: 'userInfo/hasPermission',
            organisation: 'userInfo/organisation',
        }),
        placesOverviewTable() {
            return (this as any).$refs.placesOverviewTable as PlacesOverviewTableMethods;
        },
    },
    async created() {
        // Fetch all organisations and store for use when editing and/or creating places.
        // We do this once in the parent component to avoid redundant fetching.
        await this.$store.dispatch('organisation/FETCH_ALL');
    },
    methods: {
        updateSelectedPlaces(selected: PlaceDTO) {
            this.selected = selected as any;
        },
        emptySelected() {
            // Empty selected in table components
            (this.$refs.placesOverviewTable as any as PlacesOverviewTableMethods).emptySelected();
            this.selected = [];
        },
        mergePlaces(lockedMergeTarget?: PlaceDTO) {
            if (!lockedMergeTarget) {
                this.placesToMerge = this.selected;
                return;
            }

            this.lockedMergeTargetUuid = lockedMergeTarget.uuid;
            this.placesToMerge = [lockedMergeTarget];
        },
        mergeSuccess() {
            this.placesOverviewTable.resetTable();
        },
        onCreationCompleted() {
            this.creating = false;
            this.placesOverviewTable.resetTable();
        },
        onPlaceCreated() {
            this.placesOverviewTable.resetTable();
        },
        verifyPlaces() {
            this.$modal.show({
                title: 'Weet je zeker dat je deze contexten wilt verifïeren?',
                text: 'Zorg dat de gegevens van de contexten overeenkomen met wat er in HPZone staat. Geverifieerde contexten worden eerder als resultaat getoond bij zoekopdrachten.',
                okTitle: 'Verifïeren',
                onConfirm: async () => {
                    await placeApi.verifyMulti(this.selected.map(({ uuid }) => uuid));
                    this.placesOverviewTable.resetTable();
                },
            });
        },
    },
});
</script>

<style lang="scss" scoped>
@import './resources/scss/_variables.scss';
.table-notification {
    margin-bottom: 1rem;
    text-align: right;
    color: $lighter-grey;
}
.search-field {
    .form-control {
        // !important needed to override bootstrap class
        border-top-right-radius: 0.25rem !important;
        border-bottom-right-radius: 0.25rem !important;
        font-size: 0.875rem;
    }
    .input-group-append {
        position: absolute;
        right: 0;
        height: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 20;

        svg {
            margin-right: 0.8rem;
            width: 1rem;
            height: 1rem;
        }
    }
}
</style>

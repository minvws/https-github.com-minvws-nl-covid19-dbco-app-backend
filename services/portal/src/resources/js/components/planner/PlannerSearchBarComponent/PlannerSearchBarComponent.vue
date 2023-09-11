<template>
    <div class="search-bar-wrapper">
        <BInputGroup>
            <BFormInput v-model="search" :formatter="numberFormat" placeholder="Zoek op dossiernummer" debounce="500" />
            <BInputGroupPrepend is-text>
                <i class="icon icon--search icon--md icon--center icon--m0" />
            </BInputGroupPrepend>
            <BInputGroupAppend is-text v-if="search" @click="search = null">
                <i class="icon icon--close icon--md icon--center icon--m0" />
            </BInputGroupAppend>
        </BInputGroup>
        <div class="search-results-wrapper" v-if="searched">
            <button type="button" class="search-result" v-if="result" @click="openModal">
                <div class="d-flex align-items-center">
                    <i class="icon icon--case icon--lg mr-2" />
                    <span data-testid="searchResultCaseId" class="font-weight-bold mr-2">{{
                        result.organisation && !result.organisation.isCurrent
                            ? `${result.organisation.abbreviation}-${result.caseId}`
                            : result.caseId
                    }}</span>
                    <CovidCaseStatusBadge
                        :bcoStatus="result.bcoStatus"
                        :statusIndexContactTracing="result.statusIndexContactTracing"
                    />
                </div>
                <span class="text-muted">
                    in: <strong data-testid="searchResultInTable">{{ views[result.plannerView] }}</strong>
                </span>
            </button>
            <div v-else class="search-no-result">
                <span class="icon-wrapper mr-2">
                    <i class="icon icon--search icon--lg" />
                </span>
                Geen resultaten gevonden
            </div>
        </div>
        <CovidCaseDetailModal
            v-if="selectedCase"
            ref="case-detail-modal"
            :selectedCase="selectedCase"
            :assigneeTitle="getAssigneeTitle(selectedCase)"
        />
    </div>
</template>
<script lang="ts">
import type { BModal } from 'bootstrap-vue';
import { caseApi } from '@dbco/portal-api';
import { defineComponent } from 'vue';
import CovidCaseDetailModal from '@/components/planner/CovidCaseDetailModal/CovidCaseDetailModal.vue';
import CovidCaseStatusBadge from '@/components/planner/CovidCaseStatusBadge/CovidCaseStatusBadge.vue';
import type { PlannerCaseListItem } from '@dbco/portal-api/caseList.dto';
import { PlannerView } from '@dbco/portal-api/caseList.dto';
import { mapWritableState } from 'pinia';
import { usePlanner } from '@/store/planner/plannerStore';

type ViewNames = Record<PlannerView, string>;

interface Data {
    result: PlannerCaseListItem | undefined;
    search: string | null;
    searched: boolean;
    views: ViewNames;
}

export default defineComponent({
    name: 'PlannerSearchBarComponent',
    components: {
        CovidCaseStatusBadge,
        CovidCaseDetailModal,
    },
    data() {
        return {
            search: '',
            searched: false,
            result: undefined,
            views: {
                unknown: 'Niet bekend',
                [PlannerView.UNASSIGNED]: 'Te verdelen',
                [PlannerView.QUEUED]: 'Wachtrij',
                [PlannerView.OUTSOURCED]: 'Uitbesteed',
                [PlannerView.ASSIGNED]: 'Toegewezen',
                [PlannerView.COMPLETED]: 'Te controleren',
                [PlannerView.ARCHIVED]: 'Recent gesloten',
            } as ViewNames,
        } as Data;
    },
    watch: {
        search: function () {
            void this.fetchSearchResult();
        },
    },
    computed: {
        ...mapWritableState(usePlanner, ['selectedCase']),
    },
    methods: {
        async fetchSearchResult() {
            if (!this.search || (this.search && this.search.length < 7)) {
                this.searched = false;
                this.result = undefined;
            } else {
                try {
                    const data = await caseApi.plannerSearch(this.search);
                    this.result = data;
                } catch (error) {
                    this.result = undefined;
                }
                this.searched = true;
            }
        },
        numberFormat(value: string) {
            return value ? value.slice(0, 16) : value;
        },
        openModal() {
            this.selectedCase = this.result;
            this.$nextTick(() => {
                (this.$refs['case-detail-modal'] as BModal).show();
            });
        },
        getAssigneeTitle(item: PlannerCaseListItem | null = null) {
            // If this case does not belong to an organisation or belongs to the currently active organisation
            // Get its list or user as title
            if (item && (!item.assignedOrganisation || item.assignedOrganisation.isCurrent)) {
                return this.getAssigneeListOrUser(item);
            }

            // If an organisation is assigned, get its title
            if (item?.assignedOrganisation) {
                return this.getAssigneeOrganisation(item);
            }

            return 'Toewijzen';
        },
        getAssigneeListOrUser(item: PlannerCaseListItem) {
            // If the case has been assigned to a list, show the listname
            if (item.assignedCaseList) {
                let title = item.assignedCaseList.name;

                if (item.assignedUser) {
                    title += ` (${item.assignedUser.name})`;
                }

                return title;
            }

            // If the case is assigned to a user, show the username
            if (item.assignedUser) {
                return item.assignedUser.name;
            }

            return 'Toewijzen';
        },
        getAssigneeOrganisation(item: PlannerCaseListItem) {
            if (item.assignedOrganisation) {
                let title = item.assignedOrganisation.name;

                if (item.assignedCaseList?.isQueue) {
                    // If the case is in the queue list
                    title += ' (Wachtrij)';
                } else if (item.assignedUser) {
                    // Is an user is assigned
                    title += ' (bij BCO-er)';
                }

                return title;
            }

            return '';
        },
    },
});
</script>
<style lang="scss" scoped>
@import './resources/scss/_variables.scss';

.search-bar-wrapper {
    min-width: 15rem;
    position: relative;
}

.search-results-wrapper {
    background: white;
    border-radius: $border-radius-small;
    box-shadow:
        0px 1px 2px rgba(0, 0, 0, 0.16),
        0px 2px 6px rgba(0, 0, 0, 0.1),
        0px 32px 64px rgba(0, 0, 0, 0.06);
    display: flex;
    font-size: 0.875rem;
    margin-top: 0.5rem;
    position: absolute;
    right: 0;
    width: 30rem;

    .search-result {
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;
        padding: 1.25rem;
        cursor: pointer;
        background: none;
        border: none;

        &:hover {
            background: $bco-grey;
        }
    }

    .search-no-result {
        display: flex;
        justify-content: center;
        align-items: center;
        width: 100%;
        padding: 1rem;
        font-weight: 500;

        .icon-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 2.25rem;
            height: 2.25rem;
            background: $bco-grey;
            @apply tw-rounded-full;

            .icon {
                background-color: $dark-grey;
            }
        }
    }
}

::v-deep {
    .input-group-prepend {
        order: 0;
        background-color: $white;
        border: 1px solid $lightest-grey-on-black;
        border-radius: $border-radius-small 0 0 $border-radius-small;
        border-right: none;

        .input-group-text {
            background-color: transparent;
            border: 0;

            .icon {
                background-color: $bco-blue-gray;
            }
        }
    }

    .form-control {
        order: 1;
        background-color: $white;
        border: 1px solid $lightest-grey-on-black;
        border-left: none;
        border-radius: 0 $border-radius-small $border-radius-small 0 !important;
        box-shadow: none;
        font-size: 0.875rem;
        height: 38px;
        outline: none;
        padding: 0.5rem 2rem 0.5rem 0;
        transition: none;

        /* Chrome, Safari, Edge, Opera */
        &::-webkit-outer-spin-button,
        &::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        /* Firefox */
        &[type='number'] {
            -moz-appearance: textfield;
        }

        &:focus,
        &:focus ~ .input-group-prepend {
            border-color: $bco-purple;
            border-width: 2px;
        }

        &:focus ~ .input-group-prepend > .input-group-text > .icon,
        &:not(:placeholder-shown) ~ .input-group-prepend > .input-group-text > .icon {
            background-color: $bco-purple;
        }
    }
    .input-group-append {
        height: 100%;
        position: absolute;
        right: 0;
        z-index: 3;

        .input-group-text {
            background-color: transparent;
            border: 0;

            .icon {
                cursor: pointer;
            }
        }
    }
}
</style>

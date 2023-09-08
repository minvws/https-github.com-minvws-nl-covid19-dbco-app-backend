<template>
    <div v-if="selectedCase && selectedCase.uuid">
        <BModal
            :hide-footer="!userCanEdit || !selectedCase.assignedUser || !selectedCase.assignedUser.isCurrent"
            :ok-title="$t('components.covidCaseDetailModal.actions.view')"
            ok-only
            ok-variant="outline-primary"
            @ok="onConfirm"
            ref="modal"
            size="lg"
            data-testid="covid-case-detail-modal-footer"
        >
            <template #modal-header="{ close }">
                <h5 class="modal-title">
                    {{ selectedCase.caseId }}
                    <CovidCaseStatusBadge
                        :bcoStatus="selectedCase.bcoStatus"
                        :statusIndexContactTracing="selectedCase.statusIndexContactTracing"
                    />
                    <DbcoPhaseDropdown
                        toggleClass="badge text-primary border"
                        :bcoPhase="selectedCase.bcoPhase"
                        :cases="[selectedCase.uuid]"
                        @phaseChanged="emitUpdate"
                    />
                </h5>
                <div class="modal-options-menu">
                    <ActionDropdown
                        :item="selectedCase"
                        @changeOrganisation="openOrganisationEditModal"
                        @close="openArchiveModal"
                        @reopen="openReopenModal"
                        @edit="openCaseModal"
                        @delete="openDeleteModal"
                    />
                </div>
                <button
                    @click="close()"
                    type="button"
                    :aria-label="`${$t('components.covidCaseDetailModal.actions.close')}`"
                    class="close"
                >
                    &#215;
                </button>
            </template>
            <BTabs v-model="activeTab" nav-class="case-detail-tabs">
                <BTab :title="$t('components.covidCaseDetailModal.tabs.default')">
                    <CovidCaseDetails :assigneeTitle="assigneeTitle" />
                </BTab>
                <BTab :title="$t('components.covidCaseHistory.tabs.default')" lazy>
                    <CovidCaseHistory :selectedCaseUuid="selectedCase.uuid" :plannerTimeline="true" />
                </BTab>
                <BTab :title="$t('components.covidCaseHistory.tabs.osiris')" lazy>
                    <CovidCaseOsirisLog :caseOsirisNumber="selectedCase.osirisNumber" :caseUuid="selectedCase.uuid" />
                </BTab>
            </BTabs>
        </BModal>
        <CovidCaseArchiveModal ref="caseArchiveModal" :cases="[selectedCase.uuid]" @archiveDone="onArchive" />
        <CovidCaseOrganisationEditModal ref="caseOrganisationEditModal" @changed="onOrganisationChange" />
        <CovidCaseReopenModal
            ref="caseReopenModal"
            :assigneeTitle="assigneeTitle"
            :cases="[selectedCase]"
            @reopenDone="onReopen"
        />
        <FormCase ref="caseForm" :selectedCase="selectedCase" @created="emitUpdate" @deleted="emitUpdate" />
    </div>
</template>

<script lang="ts">
import { caseApi } from '@dbco/portal-api';
import { mapMutations } from 'vuex';
import type { PropType } from 'vue';
import { defineComponent } from 'vue';
import type { Organisation } from '@/components/form/ts/formTypes';
import ActionDropdown from '@/components/planner/ActionDropdown/ActionDropdown.vue';
import CovidCaseArchiveModal from '@/components/modals/CovidCaseArchiveModal/CovidCaseArchiveModal.vue';
import CovidCaseDetails from '@/components/planner/CovidCaseDetails/CovidCaseDetails.vue';
import CovidCaseOrganisationEditModal from '@/components/modals/CovidCaseOrganisationEditModal/CovidCaseOrganisationEditModal.vue';
import CovidCaseOsirisLog from '@/components/utils/CovidCaseOsirisLog/CovidCaseOsirisLog.vue';
import CovidCaseReopenModal from '@/components/modals/CovidCaseReopenModal/CovidCaseReopenModal.vue';
import CovidCaseHistory from '@/components/utils/CovidCaseHistory/CovidCaseHistory.vue';
import CovidCaseStatusBadge from '@/components/planner/CovidCaseStatusBadge/CovidCaseStatusBadge.vue';
import DbcoPhaseDropdown from '@/components/utils/DbcoPhaseDropdown/DbcoPhaseDropdown.vue';
import FormCase from '@/components/form/FormCase/FormCase.vue';
import { StoreType } from '@/store/storeType';
import { OrganisationMutations } from '@/store/organisation/organisationMutations/organisationMutations';
import { usePlanner } from '@/store/planner/plannerStore';
import { mapActions } from 'pinia';
import { userCanEdit } from '@/utils/interfaceState';
import type { BModal } from 'bootstrap-vue';
import type { PlannerCaseListItem } from '@dbco/portal-api/caseList.dto';

export enum CovidCaseTab {
    Details,
    History,
}

export default defineComponent({
    name: 'CovidCaseDetailModal',
    components: {
        ActionDropdown,
        CovidCaseArchiveModal,
        CovidCaseDetails,
        CovidCaseHistory,
        CovidCaseOrganisationEditModal,
        CovidCaseOsirisLog,
        CovidCaseReopenModal,
        CovidCaseStatusBadge,
        DbcoPhaseDropdown,
        FormCase,
    },
    props: {
        assigneeTitle: {
            type: String,
            required: false,
        },
        selectedCase: {
            type: Object as PropType<PlannerCaseListItem>,
            required: true,
        },
    },
    data() {
        return {
            activeTab: CovidCaseTab.Details,
        };
    },
    computed: {
        userCanEdit,
    },
    methods: {
        ...mapActions(usePlanner, ['updateSelectedBCOStatus']),
        ...mapMutations(StoreType.ORGANISATION, {
            setCurrentOrganisation: OrganisationMutations.SET_CURRENT,
        }),
        emitUpdate() {
            this.$root.$emit('caseUpdated');
        },
        async onActionDone() {
            const data = await caseApi.getStatus(this.selectedCase.uuid);
            if (data?.bcoStatus?.length) {
                this.updateSelectedBCOStatus(data.bcoStatus);
            }
            this.emitUpdate();
        },
        onArchive() {
            this.emitUpdate();
            (this.$refs.caseArchiveModal as BModal).hide();
            (this.$refs.modal as BModal).hide();
        },
        async onReopen() {
            await this.onActionDone();
            (this.$refs.caseReopenModal as BModal).hide();
        },
        onConfirm() {
            if (!this.userCanEdit || !this.selectedCase.assignedUser || !this.selectedCase.assignedUser.isCurrent)
                return;

            window.location.assign(`/editcase/${this.selectedCase.uuid}`);
        },
        onOrganisationChange() {
            this.emitUpdate();
            (this.$refs.modal as BModal).hide();
        },
        openArchiveModal() {
            (this.$refs.caseArchiveModal as BModal).show();
        },
        async openCaseModal() {
            ((await this.$refs.caseForm) as BModal).open();
            (this.$refs.modal as BModal).hide();
        },
        openOrganisationEditModal() {
            this.setCurrentOrganisation(this.selectedCase.organisation as Partial<Organisation>);
            this.$nextTick(() => {
                (this.$refs.caseOrganisationEditModal as BModal).show();
            });
        },
        openReopenModal() {
            (this.$refs.caseReopenModal as BModal).show();
        },
        openDeleteModal() {
            this.$modal.show({
                title: 'Weet je zeker dat je de case wilt verwijderen? Dat kan niet ongedaan worden gemaakt.',
                okTitle: 'Verwijderen',
                okVariant: 'outline-danger',
                onConfirm: async () => {
                    await caseApi.deleteCase(this.selectedCase.uuid);
                    this.emitUpdate();
                    (this.$refs.modal as BModal).hide();
                },
            });
        },
        show(activeTab?: CovidCaseTab) {
            // Do not change this methods name/purpose, other components are calling it directly
            this.activeTab = activeTab ?? CovidCaseTab.Details;
            (this.$refs.modal as BModal).show();
        },
    },
});
</script>

<style lang="scss">
@import './resources/scss/_variables.scss';

table.table.form-container td {
    font-weight: 500;
}

.close {
    padding-left: 0.5rem; // to accomodate adjacent options menu
}

.modal-options-menu {
    position: absolute;
    top: 0;
    right: 3rem;

    .dropdown {
        .btn.dropdown-toggle {
            padding: 1.2rem 0.25rem 0.875rem;

            &:hover > .icon {
                // mimic modal close button hover
                background-color: $black;
                opacity: 0.75;
            }
        }
    }
}

.case-detail-tabs {
    max-width: 100%;
    margin-top: -1rem;
    margin-bottom: 1rem;
}
</style>

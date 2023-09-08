<template>
    <div>
        <div class="container-xl">
            <div class="row">
                <div class="col ml-5 mr-5">
                    <h2 class="mt-4 mb-4 font-weight-normal d-flex align-items-center">
                        {{ organisation && organisation.name }} /&nbsp;
                        <span class="font-weight-bold title text-truncate">{{ list ? list.name : 'Alle cases' }}</span>
                        <ListDropdown
                            :list="list"
                            @createList="showListDialog"
                            @editList="showListDialog"
                            @selected="selectList"
                        />
                        <PlannerSearchBarComponent
                            class="ml-auto"
                            :class="isAddCaseButtonDistributorEnabled ? 'mr-2' : 'mr-0'"
                        />
                        <!-- Start of add button component -->
                        <span v-if="hasPermission(PermissionV1.VALUE_caseCreate) && isAddCaseButtonDistributorEnabled">
                            <button
                                type="button"
                                @click="openNewCaseForm"
                                class="btn btn-primary ml-auto"
                                data-testid="new-case-button"
                            >
                                &#65291; Case aanmaken
                            </button>
                            <FormCase ref="newCaseForm" :list="selectedList" @created="refreshTable" />
                        </span>
                        <!-- End of add button component -->
                    </h2>
                    <div class="mt-2">
                        <BTabs v-model="activeTab" lazy no-key-nav content-class="mt-4">
                            <BTab v-for="(title, key) in filteredTabs" :key="key" class="p-0">
                                <template #title>
                                    {{ title }}
                                    <span v-if="counts" class="font-weight-normal text-muted">{{
                                        getTabCount(key)
                                    }}</span>
                                    <BSkeleton animation="fade" class="count-skeleton" v-else no-aspect></BSkeleton>
                                </template>
                                <CovidCasePlannerTable
                                    v-if="key !== PlannerView.INTAKELIST"
                                    :ref="key"
                                    :list="selectedList"
                                    :filter="key"
                                    @refreshList="updateCounts(list ? list.uuid : null)"
                                />
                                <CovidCaseIntakeTable
                                    v-if="isIntakeMatchCaseEnabled && key === PlannerView.INTAKELIST"
                                    :ref="key"
                                    :list="key"
                                    @refreshList="updateCounts(list ? list.uuid : null)"
                                />
                            </BTab>
                        </BTabs>
                    </div>
                </div>
            </div>
            <BModal
                v-if="editList"
                id="selected-list-modal"
                :title="editList.uuid ? 'Lijst bewerken' : 'Nieuwe lijst maken'"
                cancel-title="Verwijder lijst"
                cancel-variant="outline-danger"
                :ok-only="!editList.uuid"
                :ok-title="editList.uuid ? 'Opslaan' : 'Lijst maken'"
                @cancel="deleteList"
                @ok="submitList"
            >
                <BForm @submit.stop.prevent="submitList">
                    <BFormGroup label="Naam lijst" label-for="editlist_name" class="mb-0">
                        <BFormInput id="editlist_name" v-model="editList.name" type="text" required />
                    </BFormGroup>
                </BForm>
            </BModal>
        </div>
    </div>
</template>

<script lang="ts">
import { caseListApi, userApi } from '@dbco/portal-api';
import type { CaseList, CaseListWithStats } from '@dbco/portal-api/caseList.dto';
import { PlannerView } from '@dbco/portal-api/caseList.dto';
import FormCase from '@/components/form/FormCase/FormCase.vue';
import CovidCaseIntakeTable from '@/components/planner/CovidCaseIntakeTable/CovidCaseIntakeTable.vue';
import CovidCasePlannerTable from '@/components/planner/CovidCasePlannerTable/CovidCasePlannerTable.vue';
import ListDropdown from '@/components/planner/ListDropdown/ListDropdown.vue';
import PlannerSearchBarComponent from '@/components/planner/PlannerSearchBarComponent/PlannerSearchBarComponent.vue';
import env from '@/env';
import { usePlanner } from '@/store/planner/plannerStore';
import { defineComponent } from 'vue';
import { PermissionV1 } from '@dbco/enum';
import showToast from '@/utils/showToast';
import type { CancelTokenSource } from 'axios';
import axios from 'axios';
import type { BvTableField } from 'bootstrap-vue';
import { mapActions, mapState } from 'pinia';
import InfiniteLoading from 'vue-infinite-loading';
import { mapGetters } from 'vuex';

type Lists = List[];

interface Data {
    activeTab: number;
    cancelToken: CancelTokenSource | null;
    editList: Partial<List> | null;
    fields: (BvTableField & { key: string })[];
    list: List | null;
    listPages: Lists[];
    listsPage: number;
    PlannerView: typeof PlannerView;
    PermissionV1: typeof PermissionV1;
}

type AllCasesList = Omit<CaseListWithStats | CaseList, 'uuid'> & {
    uuid: null;
};

type List = CaseListWithStats | CaseList | AllCasesList;

type Tabs = Partial<Record<PlannerView, string>>;

const defaultTabs: Tabs = {
    [PlannerView.INTAKELIST]: 'Intakevragenlijsten',
    [PlannerView.UNASSIGNED]: 'Te verdelen',
    [PlannerView.QUEUED]: 'Wachtrij',
    [PlannerView.OUTSOURCED]: 'Uitbesteed',
    [PlannerView.ASSIGNED]: 'Toegewezen',
    [PlannerView.COMPLETED]: 'Te controleren',
    [PlannerView.ARCHIVED]: 'Recent gesloten',
};

const CovidCaseOverviewPlannerView = defineComponent({
    name: 'CovidCaseOverviewPlannerView',
    components: {
        CovidCaseIntakeTable,
        CovidCasePlannerTable,
        FormCase,
        InfiniteLoading,
        ListDropdown,
        PlannerSearchBarComponent,
    },
    data() {
        return {
            activeTab: env.isIntakeMatchCaseEnabled ? 1 : 0,
            cancelToken: null,
            editList: null,
            fields: [
                {
                    key: 'selected',
                    label: '',
                    thStyle: { width: '24px' },
                },
                {
                    key: 'name',
                    label: 'Naam',
                    tdClass: 'title d-flex justify-content-between align-items-center',
                    thStyle: { width: '240px' },
                },
                {
                    key: 'unassignedCasesCount',
                    label: 'Te verdelen',
                    thClass: 'text-center',
                    thStyle: { width: '120px' },
                    tdClass: 'text-center',
                },
                {
                    key: 'assignedCasesCount',
                    label: 'Toegewezen',
                    thClass: 'text-center',
                    thStyle: { width: '120px' },
                    tdClass: 'text-center',
                },
                {
                    key: 'completedCasesCount',
                    label: 'Te controleren',
                    thClass: 'text-center',
                    thStyle: { width: '120px' },
                    tdClass: 'text-center',
                },
                {
                    key: 'archivedCasesCount',
                    label: 'Gesloten',
                    thClass: 'text-center',
                    thStyle: { width: '120px' },
                    tdClass: 'text-center',
                },
            ],
            list: null,
            listPages: [],
            listsPage: 1,
            PlannerView,
            PermissionV1,
        } as Data;
    },
    async created() {
        this.$root.$on('caseUpdated', this.refreshTable);
        await this.fetchViewFilters();
    },
    async beforeRouteEnter(to, from, next) {
        const uuid = to.params?.list;

        type TypeThis = typeof this;

        try {
            const list = uuid ? await caseListApi.getList(uuid) : null;

            next((vm: any) => {
                (vm as TypeThis).$data.list = list;
                void (vm as TypeThis).fetchCounts(uuid);
            });
        } catch (e) {
            next((vm) => {
                if (axios.isAxiosError(e) && e.response?.status === 404) {
                    showToast('Lijst niet gevonden', 'caselist-not-found', true);
                }
            });
            throw e;
        }
    },
    async beforeRouteUpdate(to, from, next) {
        const uuid = to.params?.list;
        if (uuid === this.list?.uuid) {
            next();
            return;
        }

        this.list = uuid ? await caseListApi.getList(uuid) : null;
        await this.updateCounts(this.list?.uuid);

        next();
    },
    destroyed() {
        this.$root.$off('caseUpdated');
    },
    computed: {
        ...mapGetters({ organisation: 'userInfo/organisation', hasPermission: 'userInfo/hasPermission' }),
        ...mapState(usePlanner, ['allCaseCounts', 'counts']),
        filteredTabs() {
            const tabs = { ...defaultTabs };
            // If a list is chosen, hide outsourced tab
            if (this.list) {
                delete tabs[PlannerView.INTAKELIST];
                delete tabs[PlannerView.QUEUED];
            }

            // If a list is chosen OR the organisation type isn't regional GGG, hide outsourced tab
            if (this.list || this.organisation?.type !== 'regionalGGD') {
                delete tabs[PlannerView.OUTSOURCED];
            }

            if (!this.isIntakeMatchCaseEnabled) {
                delete tabs[PlannerView.INTAKELIST];
            }

            return tabs;
        },
        selectedList() {
            return this.list?.uuid || null;
        },
        tableRef() {
            // It is certain this is a PlannerView
            const tab = Object.keys(this.filteredTabs)[this.activeTab] as PlannerView;

            // Note: VueJS will create an array for refs set within a v-for
            return (this.$refs as any)[tab][0];
        },
        isIntakeMatchCaseEnabled() {
            return env.isIntakeMatchCaseEnabled;
        },
        isAddCaseButtonDistributorEnabled() {
            return env.isAddCaseButtonDistributorEnabled;
        },
    },
    methods: {
        ...mapActions(usePlanner, ['updateCounts', 'fetchCounts', 'fetchViewFilters']),
        showListDialog(list = {}) {
            this.editList = list;
            this.$nextTick(() => this.$bvModal.show('selected-list-modal'));
        },
        async deleteList() {
            if (!this.editList?.uuid) return;

            try {
                await caseListApi.deleteList(this.editList.uuid);

                // If user is viewing this list, change the view to the default list
                if (this.editList?.uuid == this.list?.uuid) this.list = null;

                await this.updateCounts(this.list?.uuid);
                this.$bvModal.hide('selected-list-modal');
                this.editList = null;
            } catch {
                // If list has items show this warning
                this.$modal.show({
                    title: 'Let op: De lijst is niet leeg',
                    text: 'Alle cases worden van deze lijst gehaald. Nog niet verdeelde cases komen bij de werkverdeler terecht. Toegewezen cases blijven toegewezen.',
                    okTitle: 'Verwijder lijst',
                    okVariant: 'outline-danger',
                    onConfirm: async () => {
                        if (!this.editList?.uuid) return;

                        const response = await caseListApi.deleteList(this.editList.uuid, true);
                        if (response.status === 204) {
                            // If user is viewing this list, change the view to the default list
                            if (this.editList?.uuid == this.list?.uuid) {
                                this.list = null;
                            } else {
                                await this.refreshTable();
                            }

                            await this.updateCounts(this.list?.uuid);
                            this.$bvModal.hide('selected-list-modal');
                            this.editList = null;
                        }
                    },
                });
            }
        },
        getTabCount(tab: string) {
            if (!this.counts) return 0;

            return Object.entries(this.counts).find(([key]) => key === tab)?.[1] || 0;
        },
        openNewCaseForm() {
            (this.$refs as any).newCaseForm.open();
        },
        async refreshTable() {
            await this.updateCounts(this.list?.uuid);
            this.tableRef.resetTable();
        },

        async selectList(item: List) {
            await this.$router.push({
                path: item?.uuid ? `/planner/${item.uuid}` : '/planner',
                query: {},
            });
        },
        async submitList() {
            if (this.editList === null || !this.editList.name) return;

            const { uuid } = this.editList?.uuid
                ? await caseListApi.updateList(this.editList.uuid, this.editList.name)
                : await caseListApi.createList(this.editList.name);

            if (uuid) {
                await this.updateCounts(this.list?.uuid);
            }

            this.$bvModal.hide('selected-list-modal');
            this.editList = null;
        },
        async updateIsAvailableForOutsourcing(value: boolean) {
            const currentOrganisation = await userApi.updateOrganisation({ isAvailableForOutsourcing: value });
            void this.$store.dispatch('userInfo/CHANGE', { path: 'organisation', values: currentOrganisation });
        },
    },
});
export default CovidCaseOverviewPlannerView;
</script>

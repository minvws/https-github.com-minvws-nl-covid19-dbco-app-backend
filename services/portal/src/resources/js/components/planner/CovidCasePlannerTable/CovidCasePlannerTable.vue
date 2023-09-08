<template>
    <div>
        <DbcoFilterGroup class="mt-3" @filter="(filterOn) => updateFilter(filterOn)">
            <DbcoFilter
                v-if="filter !== ListFilterOptions.Outsourced && organisations.length"
                type="organisation"
                :label="`${$t('components.covidCasePlannerTable.filters.organisation.label')}`"
                :selected="caseFilters.organisation"
                :searchable="true"
                :search-placeholder="`${$t(
                    'components.covidCasePlannerTable.filters.organisation.search_placeholder'
                )}`"
                :options="[
                    {
                        value: null,
                        label: `${$t('components.covidCasePlannerTable.filters.organisation.default_option_label')}`,
                    },
                    ...organisations.map((organisation) => ({ value: organisation.uuid, label: organisation.name })),
                ]"
            ></DbcoFilter>
            <DbcoFilter
                v-if="filter !== ListFilterOptions.Archived && caseLabels.length"
                type="label"
                :label="`${$t('components.covidCasePlannerTable.filters.label.label')}`"
                :selected="caseFilters.label"
                :searchable="true"
                :search-placeholder="`${$t('components.covidCasePlannerTable.filters.label.search_placeholder')}`"
                :options="[
                    {
                        value: null,
                        label: `${$t('components.covidCasePlannerTable.filters.label.default_option_label')}`,
                    },
                    ...caseLabels.map((caseLabel) => ({ value: caseLabel.uuid, label: caseLabel.label })),
                ]"
            ></DbcoFilter>
            <DbcoFilter
                v-if="filter === ListFilterOptions.Assigned && userAssignmentOptions.length"
                type="userAssignment"
                :label="`${$t('components.covidCasePlannerTable.filters.userAssignment.label')}`"
                :selected="caseFilters.userAssignment"
                :searchable="true"
                :search-placeholder="`${$t(
                    'components.covidCasePlannerTable.filters.userAssignment.search_placeholder'
                )}`"
                :options="[
                    {
                        value: null,
                        label: `${$t('components.covidCasePlannerTable.filters.userAssignment.default_option_label')}`,
                    },
                    ...userAssignmentOptions.map((assignmentOption) =>
                        $as.any({
                            value: $as.defined(assignmentOption.assignment).assignedUserUuid,
                            label: assignmentOption.label,
                        })
                    ),
                ]"
            ></DbcoFilter>
            <DbcoFilter
                type="statusIndexContactTracing"
                :label="`${$t('components.covidCasePlannerTable.filters.statusIndexContactTracing.label')}`"
                :selected="caseFilters.statusIndexContactTracing"
                :searchable="true"
                :search-placeholder="`${$t(
                    'components.covidCasePlannerTable.filters.statusIndexContactTracing.search_placeholder'
                )}`"
                :options="[
                    {
                        value: null,
                        label: `${$t(
                            'components.covidCasePlannerTable.filters.statusIndexContactTracing.default_option_label'
                        )}`,
                    },
                    ...caseStatuses.map((status) => ({ value: status.value, label: status.label })),
                ]"
            ></DbcoFilter>

            <DbcoAgeFilter
                v-if="isCovidCaseAgeFilterEnabled && filter === ListFilterOptions.Unassigned"
                @selected="(filterOn) => updateAgeFilter(filterOn)"
                :ageLabel="updateAgeLabel()"
            />

            <DbcoFilter
                v-if="filter === ListFilterOptions.Unassigned"
                type="testResultSource"
                :label="`${$t('components.covidCasePlannerTable.filters.testResultSource.label')}`"
                :selected="caseFilters.testResultSource"
                :searchable="true"
                :search-placeholder="`${$t(
                    'components.covidCasePlannerTable.filters.testResultSource.search_placeholder'
                )}`"
                :options="[
                    {
                        value: null,
                        label: `${$t(
                            'components.covidCasePlannerTable.filters.testResultSource.default_option_label'
                        )}`,
                    },
                    ...Object.entries(testResultSources)
                        .filter((source) => source[1] !== testResultSources.publicWebPortal)
                        .map((source) => ({ value: source[0], label: source[1] })),
                ]"
            ></DbcoFilter>
        </DbcoFilterGroup>
        <div v-show="items.length !== 0">
            <BTable
                class="table-wrapper bg-white table-hover table-rounded"
                :fields="fields"
                :items="items"
                @row-clicked="rowClicked"
                @sort-changed="sortTable"
                thead-tr-class="heading-row"
                no-local-sorting
            >
                <template #head(uuid)>
                    <div class="table-checkbox-wrapper">
                        <BFormCheckbox v-model="allSelected" :indeterminate="indeterminate" @change="toggleAll" />
                    </div>
                </template>
                <template #cell(uuid)="data">
                    <div v-if="data.item.isEditable || data.item.isAssignable" class="table-checkbox-wrapper">
                        <BFormCheckbox
                            :id="data.item.uuid"
                            v-model="selected"
                            :name="data.item.uuid"
                            v-bind="$as.any({ value: data.item.uuid })"
                            v-on:click.stop.prevent="toggleCheckbox(data.item.uuid)"
                        />
                    </div>
                </template>
                <template #cell(priority)="data">
                    <i
                        v-if="data.item.priority"
                        class="icon icon--center icon--m0 mr-1"
                        :class="['icon--priority-' + data.item.priority]"
                    />
                </template>
                <template #cell(caseId)="data">
                    <div class="table-case-status">
                        <span>
                            {{
                                data.item.organisation && !data.item.organisation.isCurrent
                                    ? `${data.item.organisation.abbreviation}-${data.item.caseId}`
                                    : data.item.caseId
                            }}
                        </span>
                        <i
                            v-if="data.item.wasOutsourced"
                            class="icon icon--returned icon--md icon--center icon--m0"
                            :class="historyViewTriggerClass"
                            v-b-tooltip.hover
                            :title="getOutsourcedOrganisationTooltip(data.item)"
                        />
                        <i
                            v-if="data.item.hasNotes"
                            class="icon icon--annotated icon--md icon--center icon--m0"
                            :class="historyViewTriggerClass"
                        />
                        <i
                            v-if="data.item.isApproved === false"
                            v-b-tooltip.hover
                            :title="`${$t('components.covidCasePlannerTable.tooltips.not_approved')}`"
                            class="icon icon--rejected icon--md icon--center icon--m0"
                            :class="historyViewTriggerClass"
                        />
                    </div>
                </template>
                <template #cell(testResultSource)="data">
                    {{ formattedTestResultSource(data.item) }}
                </template>
                <template #cell(status)="data">
                    <CovidCaseStatusBadge
                        class="w-100"
                        :bcoStatus="data.item.bcoStatus"
                        :statusIndexContactTracing="data.item.statusIndexContactTracing"
                    />
                </template>
                <template #cell(contactsCount)="data">
                    {{ data.item.contactsCount > 0 ? data.item.contactsCount : '-' }}
                </template>
                <template #cell(dateOfBirth)="data">
                    {{ age(data.item.dateOfBirth) }}
                    <i
                        v-if="needsAttention(data.item.dateOfBirth)"
                        class="icon icon--error-warning"
                        v-b-tooltip.hover
                        :title="`${$t('components.covidCasePlannerTable.tooltips.needs_attention')}`"
                    />
                </template>
                <template #cell(caseLabels)="data">
                    {{ caseLabelsString(data.item.caseLabels) }}
                </template>
                <template #cell(updatedAt)="data">
                    {{ formattedUpdatedAtDate(data.item.updatedAt) }}
                </template>
                <template #cell(createdAt)="data">
                    {{ formatDate(parseDate(data.item.createdAt), 'd MMMM yyyy HH:mm') }}
                </template>
                <template #cell(lastAssignedUserName)="data">
                    {{ data.item.lastAssignedUserName || '-' }}
                </template>
                <template #cell(assigned)="data">
                    <DbcoAssignDropdown
                        v-if="data.item.isAssignable"
                        :right="true"
                        :uuid="[data.item.uuid]"
                        :title="getAssigneeTitle(data.item)"
                        :toggleClass="
                            data.item.assignedUser ||
                            (data.item.assignedOrganisation && !data.item.assignedOrganisation.isCurrent) ||
                            data.item.assignedCaseList
                                ? 'text-muted p-0'
                                : 'text-primary p-0'
                        "
                        :staleSince="staleSince"
                        @assignErrors="showAssignErrors"
                        @optionSelected="assigned($event)"
                    />
                    <span v-else>{{ getAssigneeTitle(data.item) }}</span>
                </template>
                <template #cell(actions)="data">
                    <ActionDropdown
                        v-if="data.item"
                        :item="data.item"
                        @changeOrganisation="changeOrganisation(data.item)"
                        @close="archiveCases([data.item.uuid])"
                        @reopen="reopenCases([data.item])"
                        @edit="openCaseForm(data.item)"
                        @delete="openCovidCaseDeleteModal(data.item)"
                    />
                </template>
            </BTable>
        </div>
        <div>
            <InfiniteLoading :identifier="infiniteId" @infinite="infiniteHandler" spinner="spiral">
                <div slot="spinner" class="mt-3 mb-3">
                    <Spinner />
                    <span class="infinite-loader"> {{ $t('components.covidCasePlannerTable.hints.load_more') }} </span>
                </div>
                <div slot="no-more"></div>
                <div slot="no-results"></div>
            </InfiniteLoading>
        </div>
        <div
            v-if="items.length === 0 && !loading"
            class="empty-placeholder d-flex justify-content-center align-items-center bg-white text-center pt-5 pb-5"
        >
            {{ $t('components.covidCasePlannerTable.hints.no_cases') }}
        </div>
        <PlannerBulkAction
            v-if="selected.length > 0"
            :archiveable="archiveable(selected)"
            :assignable="filter !== ListFilterOptions.Archived"
            :selected="selected"
            :staleSince="staleSince"
            @onArchive="archiveCases(selected)"
            @onAssign="assigned"
            @assignErrors="showAssignErrors"
            @onClear="selected = []"
            @onUpdatePriority="updatePriority"
            @phaseChanged="onActionDone"
        />
        <FormCase
            v-if="selectedCase"
            ref="caseForm"
            :list="list"
            :selected-case="selectedCase"
            @created="resetTable"
            @deleted="resetTable"
        />
        <CovidCaseArchiveModal
            ref="caseArchiveModal"
            :cases="$as.defined(casesToArchive)"
            @archiveDone="onActionDone"
        />
        <CovidCaseDeleteModal
            ref="covidCaseDeleteModal"
            :text="`${$t(`shared.case_delete_warning.planner`)}`"
            @confirm="deleteCase"
        />
        <CovidCaseOrganisationEditModal ref="caseOrganisationEditModal" @changed="resetTable" />
        <CovidCaseReopenModal
            ref="caseReopenModal"
            :assigneeTitle="reopenAssigneeTitle"
            :cases="casesToReopen"
            @reopenDone="onActionDone"
        />
        <CovidCaseDetailModal
            v-if="selectedCase"
            ref="caseDetailModal"
            :selectedCase="selectedCase"
            :assigneeTitle="getAssigneeTitle(selectedCase)"
        />
        <CovidCaseAssignConflictModal ref="caseAssignConflictModal" />
    </div>
</template>

<script lang="ts">
import { caseApi, caseListApi } from '@dbco/portal-api';
import type { Organisation } from '@dbco/portal-api/organisation.dto';
import env from '@/env';
import DbcoFilter from '@/components/DbcoFilterGroup/DbcoFilter/DbcoFilter.vue';
import DbcoAgeFilter from '@/components/DbcoFilterGroup/DbcoAgeFilter/DbcoAgeFilter.vue';
import DbcoFilterGroup from '@/components/DbcoFilterGroup/DbcoFilterGroup.vue';
import FormCase from '@/components/form/FormCase/FormCase.vue';
import type { AssignmentConflict } from '@/components/form/ts/formTypes';
import type { AssignmentResult } from '@dbco/portal-api/assignment';
import DbcoAssignDropdown from '@/components/formControls/DbcoAssignDropdown/DbcoAssignDropdown.vue';
import CovidCaseArchiveModal from '@/components/modals/CovidCaseArchiveModal/CovidCaseArchiveModal.vue';
import CovidCaseDeleteModal from '@/components/modals/CovidCaseDeleteModal/CovidCaseDeleteModal.vue';
import CovidCaseAssignConflictModal from '@/components/modals/CovidCaseAssignConflictModal/CovidCaseAssignConflictModal.vue';
import CovidCaseOrganisationEditModal from '@/components/modals/CovidCaseOrganisationEditModal/CovidCaseOrganisationEditModal.vue';
import CovidCaseReopenModal from '@/components/modals/CovidCaseReopenModal/CovidCaseReopenModal.vue';
import ActionDropdown from '@/components/planner/ActionDropdown/ActionDropdown.vue';
import CovidCaseDetailModal, { CovidCaseTab } from '@/components/planner/CovidCaseDetailModal/CovidCaseDetailModal.vue';
import CovidCaseStatusBadge from '@/components/planner/CovidCaseStatusBadge/CovidCaseStatusBadge.vue';
import PlannerBulkAction from '@/components/planner/PlannerBulkAction/PlannerBulkAction.vue';
import { OrganisationMutations } from '@/store/organisation/organisationMutations/organisationMutations';
import { usePlanner } from '@/store/planner/plannerStore';
import { StoreType } from '@/store/storeType';
import { priorityV1Options, testResultSourceV1Options } from '@dbco/enum';
import { calculateAge, formatDate, formatFromNow, parseDate } from '@/utils/date';
import type { CancelTokenSource } from 'axios';
import axios from 'axios';
import type { BvTableCtxObject, BvTableFieldArray } from 'bootstrap-vue';
import { mapState, mapWritableState } from 'pinia';
import type { PropType } from 'vue';
import { defineComponent } from 'vue';
import type { StateChanger } from 'vue-infinite-loading';
import InfiniteLoading from 'vue-infinite-loading';
import { mapMutations } from 'vuex';
import applyAssignment from '../ApplyAssignment';
import type { CaseLabel, PlannerCaseListItem } from '@dbco/portal-api/caseList.dto';
import { ListFilterOptions, ListSortOptions } from '@dbco/portal-api/client/caseList.api';
import { Spinner } from '@dbco/ui-library';

export default defineComponent({
    name: 'CovidCasePlannerTable',
    components: {
        ActionDropdown,
        CovidCaseAssignConflictModal,
        CovidCaseArchiveModal,
        CovidCaseDeleteModal,
        CovidCaseDetailModal,
        CovidCaseOrganisationEditModal,
        CovidCaseReopenModal,
        CovidCaseStatusBadge,
        DbcoAssignDropdown,
        DbcoFilter,
        DbcoAgeFilter,
        DbcoFilterGroup,
        FormCase,
        InfiniteLoading,
        PlannerBulkAction,
        Spinner,
    },
    props: {
        filter: {
            type: String as PropType<ListFilterOptions>,
            required: true,
        },
        list: {
            type: String,
        },
    },
    data() {
        return {
            flow: undefined as string | undefined,
            loading: false,
            cancelToken: null as CancelTokenSource | null,
            casesToArchive: [] as PlannerCaseListItem['uuid'][],
            casesToReopen: [] as PlannerCaseListItem[],
            historyViewTriggerClass: 'history-view-trigger',
            page: 1,
            items: [] as PlannerCaseListItem[],
            priorities: priorityV1Options,
            selected: [] as string[],
            allSelected: false,
            indeterminate: false,
            infiniteId: Date.now(),
            sortBy: undefined as ListSortOptions | undefined,
            sortDirection: undefined as 'asc' | 'desc' | undefined,
            ListFilterOptions: ListFilterOptions,
            caseFilters: {
                organisation: null,
                dateOfBirth: null,
                label: null,
                maxAge: null,
                minAge: null,
                userAssignment: null,
                statusIndexContactTracing: null,
                testResultSource: null,
            } as Record<string, string | null>,
            staleSince: '',
            testResultSources: testResultSourceV1Options,
        };
    },
    computed: {
        ...mapWritableState(usePlanner, ['selectedCase']),
        ...mapState(usePlanner, ['caseLabels', 'organisations', 'caseStatuses', 'userAssignmentOptions']),
        reopenAssigneeTitle() {
            return this.casesToReopen.length === 1 ? this.getAssigneeTitle(this.casesToReopen[0]) : undefined;
        },
        isCovidCaseAgeFilterEnabled() {
            return env.isCovidCaseAgeFilterEnabled;
        },
        triggerResetFields() {
            return [
                // On change of list
                this.list,
                // On change of sort
                this.sortBy,
                this.sortDirection,
            ];
        },
        fields(): BvTableFieldArray {
            return [
                { key: 'uuid', label: '', thStyle: { width: '44px' } },
                {
                    key: 'priority',
                    label: `${this.$t('components.covidCasePlannerTable.headers.priority')}`,
                    thClass: 'text-center',
                    tdClass: 'text-center',
                    sortable: true,
                    sortDirection: 'desc',
                },
                {
                    key: 'caseId',
                    label: `${this.$t('components.covidCasePlannerTable.headers.caseId')}`,
                    tdClass: 'text-body',
                },
                ...(this.filter === ListFilterOptions.Unassigned
                    ? [
                          {
                              key: 'testResultSource',
                              label: `${this.$t('components.covidCasePlannerTable.headers.testResultSource')}`,
                          },
                      ]
                    : []),
                {
                    key: 'status',
                    label: `${this.$t('components.covidCasePlannerTable.headers.status')}`,
                    sortable: true,
                },
                ...(this.filter !== ListFilterOptions.Unassigned
                    ? [
                          {
                              key: 'contactsCount',
                              label: `${this.$t('components.covidCasePlannerTable.headers.contactsCount')}`,
                              sortable: this.filter !== ListFilterOptions.Completed,
                          },
                      ]
                    : []),
                { key: 'dateOfBirth', label: `${this.$t('components.covidCasePlannerTable.headers.dateOfBirth')}` },
                ...(this.filter !== ListFilterOptions.Archived
                    ? [
                          {
                              key: 'caseLabels',
                              label: `${this.$t('components.covidCasePlannerTable.headers.caseLabels')}`,
                          },
                      ]
                    : []),
                {
                    key: 'updatedAt',
                    label: `${this.$t('components.covidCasePlannerTable.headers.updatedAt')}`,
                    sortable: true,
                },
                {
                    key: 'createdAt',
                    label: `${this.$t('components.covidCasePlannerTable.headers.createdAt')}`,
                    sortable: true,
                },
                ...(this.filter === ListFilterOptions.Completed
                    ? [
                          {
                              key: 'lastAssignedUserName',
                              label: `${this.$t('components.covidCasePlannerTable.headers.lastAssignedUserName')}`,
                              thStyle: { width: '180px' },
                          },
                      ]
                    : []),
                ...(this.filter !== ListFilterOptions.Archived
                    ? [
                          {
                              key: 'assigned',
                              label: `${this.$t('components.covidCasePlannerTable.headers.assigned')}`,
                              thStyle: { width: '180px' },
                          },
                      ]
                    : []),
                { key: 'actions', label: '', thStyle: { width: '84px' } },
            ];
        },
    },
    watch: {
        selected(newValue) {
            this.setIndeterminate(newValue);
        },
        triggerResetFields(newVal, oldVal) {
            if (newVal === oldVal) return;

            // Check if there are any previous pending requests
            if (this.cancelToken) {
                this.cancelToken.cancel('Operation canceled due to new request.');
            }

            this.resetTable();
        },
        caseFilters: {
            handler: function () {
                this.resetTable();
            },
            deep: true,
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
        ...mapMutations(StoreType.ORGANISATION, {
            setCurrentOrganisation: OrganisationMutations.SET_CURRENT,
        }),
        parseDate,
        formatDate,
        formatFromNow,
        archiveable(cases: PlannerCaseListItem['uuid'][]) {
            const selectedCases = this.items.filter((i) => cases.includes(i.uuid));
            return selectedCases.some((selectedCase) => selectedCase.isClosable);
        },
        onActionDone() {
            this.casesToArchive = [];
            this.casesToReopen = [];
            this.resetTable();
            this.$emit('refreshList');
        },
        changeOrganisation(selectedCase: PlannerCaseListItem) {
            this.selectedCase = selectedCase;
            this.setCurrentOrganisation(selectedCase.organisation as Partial<Organisation>);
            this.$nextTick(() => {
                (this.$refs.caseOrganisationEditModal as any).show();
            });
        },
        openCaseForm(selectedCase: PlannerCaseListItem) {
            this.selectedCase = selectedCase;
            this.$nextTick(() => {
                (this.$refs.caseForm as any).open();
            });
        },
        age(dateOfBirth: string | null) {
            if (!dateOfBirth) return null;

            return calculateAge(new Date(dateOfBirth));
        },
        needsAttention(dateOfBirth: string | null) {
            const age = this.age(dateOfBirth);
            if (age === null) return false;

            return age >= 70 || age <= 16;
        },
        getAssigneeTitle(item: PlannerCaseListItem | null = null): string {
            // If this case does not belong to an organisation or belongs to the currently active organisation
            // Get its list or user as title
            if (item && (!item.assignedOrganisation || item.assignedOrganisation.isCurrent)) {
                return this.getAssigneeListOrUser(item);
            }

            // If an organisation is assigned, get its title
            if (item?.assignedOrganisation) {
                return this.getAssigneeOrganisation(item);
            }

            return `${this.$t('components.covidCasePlannerTable.assignment.default')}`;
        },
        getAssigneeListOrUser(item: PlannerCaseListItem): string {
            // If the case has been assigned to a list, show the listname
            if (item.assignedCaseList) {
                let title = item.assignedCaseList.name || '';

                if (item.assignedUser) {
                    title += ` (${item.assignedUser.name})`;
                }

                return title.trim();
            }

            // If the case is assigned to a user, show the username
            if (item.assignedUser) {
                return item.assignedUser.name || '';
            }

            return `${this.$t('components.covidCasePlannerTable.assignment.default')}`;
        },
        getAssigneeOrganisation(item: PlannerCaseListItem): string {
            let title = item.assignedOrganisation?.name ?? '';
            if (item.assignedCaseList?.isQueue)
                return `${title} ${this.$t('components.covidCasePlannerTable.assignment.organisation.queue')}`.trim();
            if (item.assignedUser)
                return `${title} ${this.$t('components.covidCasePlannerTable.assignment.organisation.user')}`.trim();
            return title;
        },
        getOutsourcedOrganisationTooltip(item: PlannerCaseListItem): string {
            const organisation = item.wasOutsourcedToOrganisation;

            return organisation
                ? `${this.$t('components.covidCasePlannerTable.assignment.organisation.outsourced', {
                      organisationName: organisation.name,
                  })}`
                : '';
        },
        resetTable() {
            this.page = 1;
            this.selected = [];
            this.items = [];
            this.staleSince = this.getUTCTimeString();
            this.$emit('refreshList');

            // Reset vue-infinite-loading component
            this.infiniteId = Date.now();
        },
        archiveCases(cases: string[]) {
            if (!cases?.length) return;
            this.casesToArchive = cases;
            this.$nextTick(() => {
                (this.$refs.caseArchiveModal as any).show();
            });
        },
        assigned(assignment: AssignmentResult) {
            this.items = applyAssignment(this.items, assignment, this.filter, this.selectedCase);
            this.selected = [];
            this.$emit('refreshList');
        },
        setIndeterminate(newValue: string[]) {
            this.allSelected = newValue.length === this.items.length;
            this.indeterminate = newValue.length > 0 && !this.allSelected;
        },
        showAssignErrors(description: string, assignmentConflicts: AssignmentConflict[]) {
            (this.$refs.caseAssignConflictModal as any).show(description, assignmentConflicts);
        },
        toggleAll(checked: boolean) {
            this.selected = checked
                ? this.items
                      .slice()
                      .filter((item) => item.isEditable || item.isAssignable)
                      .map((item) => item.uuid)
                : [];
        },
        toggleCheckbox(uuid: string) {
            let index = this.selected.findIndex((i) => i == uuid);
            if (index >= 0) {
                this.selected.splice(index, 1);
            } else {
                this.selected.push(uuid);
            }
        },
        async updatePriority(priority: string) {
            await caseApi.updatePriority({
                cases: this.selected,
                priority: priority,
            });
            this.resetTable();
        },
        reopenCases(cases: PlannerCaseListItem[]) {
            if (!cases?.length) return;
            this.casesToReopen = cases;
            this.$nextTick(() => {
                (this.$refs.caseReopenModal as any).show();
            });
        },
        rowClicked(item: PlannerCaseListItem, index: number, event: Event) {
            this.selectedCase = item;
            this.$nextTick(() => {
                const openDetailModalInHistoryView = (event.target as HTMLElement).classList.contains(
                    this.historyViewTriggerClass
                );
                (this.$refs.caseDetailModal as any).show(
                    openDetailModalInHistoryView ? CovidCaseTab.History : undefined
                );
            });
        },
        openCovidCaseDeleteModal(covidCase: PlannerCaseListItem) {
            (this.$refs.covidCaseDeleteModal as any).show(covidCase.uuid, covidCase.caseId);
        },
        async deleteCase(uuid: string) {
            await caseApi.deleteCase(uuid);
            this.resetTable();
            this.$emit('refreshList');
        },
        async infiniteHandler($state: StateChanger) {
            this.loading = true;
            this.cancelToken = axios.CancelToken.source();

            const data = await caseListApi.getListCases(
                this.list,
                this.filter,
                this.caseFilters,
                this.page,
                undefined,
                this.sortBy,
                this.sortDirection,
                this.cancelToken.token
            );

            if (data.data.length) {
                this.items.push(...data.data);
                this.setIndeterminate(this.selected);

                $state.loaded();
                this.page += 1;
            } else {
                $state.complete();
            }

            this.loading = false;
        },
        sortTable(e: BvTableCtxObject) {
            const sortOptions: string[] = Object.values(ListSortOptions);

            this.sortBy = e.sortBy && sortOptions.includes(e.sortBy) ? (e.sortBy as ListSortOptions) : undefined;

            if (this.sortBy === ListSortOptions.Status) {
                this.sortBy = 'caseStatus' as ListSortOptions;
            }

            // Flip sort direction if sorted field is updatedAt due to relative time
            const sortDesc = e.sortBy === ListSortOptions.UpdatedAt ? !e.sortDesc : e.sortDesc;
            this.sortDirection = sortDesc ? 'desc' : 'asc';

            this.resetTable();
        },
        updateAgeFilter(value: { min: string; max: string }) {
            this.caseFilters.maxAge = value.max;
            this.caseFilters.minAge = value.min;
        },
        updateAgeLabel() {
            if (this.caseFilters.minAge || this.caseFilters.maxAge) {
                return `${this.caseFilters.minAge} t/m ${this.caseFilters.maxAge} jaar`;
            }
            return this.$t('components.dropDownAgeFilter.dropDownLabel');
        },
        updateFilter(selectedFilterOption: { value: string | null; type: string }) {
            this.caseFilters[selectedFilterOption.type] = selectedFilterOption.value;
        },
        getUTCTimeString() {
            return new Date().toISOString().replace('T', ' ').substring(0, 19);
        },
        formattedUpdatedAtDate(date: string) {
            const formattedParsedDate = formatFromNow(parseDate(date));
            if (formattedParsedDate.includes('een minuut')) return '1 minuut';
            return formattedParsedDate;
        },
        formattedTestResultSource(item: PlannerCaseListItem) {
            if (!item.testResults?.length) return '-';
            if (item.testResults.every((result, index, array) => result === array[0]))
                return this.testResultSources[item.testResults[0]];
            return this.$t('shared.test_result_source_multiple');
        },
    },
    created() {
        this.staleSince = this.getUTCTimeString();
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

::v-deep {
    .table {
        margin-bottom: 130px;
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

                &:first-of-type {
                    position: relative;
                }
            }
        }

        tbody {
            tr {
                cursor: pointer;
            }

            td {
                position: relative;
                color: $light-grey;
                vertical-align: middle;

                > .dropdown {
                    position: absolute;
                    width: 100%;
                    height: 100%;
                    top: 0;
                    left: 0;
                    padding-left: 0.75rem;
                    padding-right: 0.75rem;
                }

                .icon--priority-3 {
                    width: 1.3rem;
                    height: 1.3rem;
                }

                > .table-case-status {
                    display: flex;

                    span {
                        margin-right: 0.5rem;
                    }

                    i {
                        margin: 0;
                    }

                    i + i {
                        margin-left: 0.25rem;
                    }

                    .icon--returned {
                        margin-top: 1px;
                    }
                }
            }
        }

        .custom-checkbox {
            .custom-control-label::after {
                cursor: pointer;
            }
        }
    }
}
</style>

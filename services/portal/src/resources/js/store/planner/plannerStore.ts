import { caseApi, caseListApi, organisationApi } from '@dbco/portal-api';
import type { CaseLabel, Counts, PlannerCaseListItem } from '@dbco/portal-api/caseList.dto';
import type { Organisation } from '@dbco/portal-api/organisation.dto';
import type { BcoStatusV1 } from '@dbco/enum';
import { ContactTracingStatusV1, contactTracingStatusV1Options } from '@dbco/enum';
import type { Assignment, AssignmentOption } from '@dbco/portal-api/assignment';
import type { AxiosError } from 'axios';
import { defineStore } from 'pinia';
import showToast from '@/utils/showToast';

export const usePlanner = defineStore('planner', {
    state: () => ({
        allCaseCounts: null as Counts | null,
        assignment: {
            conflicts: undefined as Array<any> | undefined,
            options: [] as Array<AssignmentOption>,
            queued: undefined as
                | {
                      uuids: Array<string>;
                      params: Assignment;
                  }
                | undefined,
        },
        caseLabels: [] as CaseLabel[],
        caseStatuses: [
            { value: ContactTracingStatusV1.VALUE_new, label: contactTracingStatusV1Options.new },
            { value: ContactTracingStatusV1.VALUE_not_started, label: contactTracingStatusV1Options.not_started },
            {
                value: ContactTracingStatusV1.VALUE_two_times_not_reached,
                label: contactTracingStatusV1Options.two_times_not_reached,
            },
            {
                value: ContactTracingStatusV1.VALUE_callback_request,
                label: contactTracingStatusV1Options.callback_request,
            },
            { value: ContactTracingStatusV1.VALUE_loose_end, label: contactTracingStatusV1Options.loose_end },
            {
                value: ContactTracingStatusV1.VALUE_four_times_not_reached,
                label: contactTracingStatusV1Options.four_times_not_reached,
            },
            { value: ContactTracingStatusV1.VALUE_bco_finished, label: contactTracingStatusV1Options.bco_finished },
        ],
        counts: null as Counts | null,
        organisations: [] as Organisation[],
        userAssignmentOptions: [] as AssignmentOption[],
        selectedCase: undefined as PlannerCaseListItem | undefined,
    }),
    actions: {
        async changeAssignment() {
            if (!this.assignment.queued?.uuids) return;
            try {
                const response = await caseApi.updateAssignment(
                    this.assignment.queued?.uuids,
                    this.assignment.queued?.params
                );
                if ((response.status === 200 || response.status === 204) && response.data) {
                    this.assignment.conflicts = response.data;
                }
            } catch (error) {
                if ((error as AxiosError).response?.status === 409) {
                    this.assignment.conflicts = (error as AxiosError).response?.data as any;
                }
            }
        },
        async changeCaseOrganisation(payload: { note: string; organisationUuid: string | undefined }) {
            if (!payload.organisationUuid || !this.selectedCase) return;
            try {
                await caseApi.changeOrganisation(this.selectedCase.uuid, payload.note, payload.organisationUuid);
                showToast(`GGD-regio van case ${this.selectedCase.caseId} is gewijzigd.`, 'organisation-edit-toast');
            } catch (error) {
                showToast(`Er ging iets mis bij het wijzigen van de GGD-regio.`, 'organisation-edit-toast', true);
            }
        },
        clearAssignment() {
            this.assignment.conflicts = undefined;
            this.assignment.options = [];
            this.assignment.queued = undefined;
        },
        async fetchAssignmentOptions(uuids: Array<string>) {
            const { options } = await caseApi.getAssignmentOptions(uuids);
            this.assignment.options = options;
        },
        async fetchViewFilters() {
            this.caseLabels = await caseApi.getCaseLabels();
            this.organisations = await organisationApi.getOrganisations();
            this.userAssignmentOptions = await caseApi.getUserAssignmentOptions();
        },
        updateSelectedBCOStatus(data: BcoStatusV1) {
            if (!this.selectedCase) return;
            this.selectedCase.bcoStatus = data;
        },
        async fetchCounts(uuid?: string | null) {
            if (uuid) {
                // viewing specific list
                this.counts = await caseListApi.listCounts(uuid);
                this.allCaseCounts = await caseListApi.listCounts(null);
                return;
            }

            // viewing allcases list
            const counts = await caseListApi.listCounts(null);
            this.counts = counts;
            this.allCaseCounts = counts;
        },
        async updateCounts(uuid?: string | null) {
            this.counts = null;
            this.allCaseCounts = null;
            await this.fetchCounts(uuid);
        },
    },
});

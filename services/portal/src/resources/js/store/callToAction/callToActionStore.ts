import i18n from '@/i18n';
import { defineStore } from 'pinia';
import type {
    CallToActionHistoryItem,
    CallToActionHistoryItems,
    CallToActionRequest,
    CallToActionResponse,
    CallToActionSortOptions,
    CallToActionTable,
} from '@dbco/portal-api/callToAction.dto';
import { callToActionApi } from '@dbco/portal-api';
import { useChoreStore } from '../chore/choreStore';
import type { PaginatedRequestOptions, PaginatedResponse } from '@dbco/portal-api/pagination';
import showToast from '@/utils/showToast';
import type { CaseTimelineDTO } from '@dbco/portal-api/case.dto';
import { formatDate, parseDate } from '@/utils/date';
import { CallToActionEventV1 } from '@dbco/enum';

export enum CallToActionEvent {
    PICKED_UP = 'picked-up',
    RETURNED = 'returned',
    NOTE = 'note',
}

export const useCallToActionStore = defineStore('callToAction', {
    state: () => ({
        history: {
            formattedAndSorted: null as CallToActionHistoryItems | null,
        },
        selected: null as CallToActionResponse | null,
        table: {
            infiniteId: Date.now(),
            page: 1,
            perPage: 20,
        } as CallToActionTable,
        tableContent: [] as Array<CallToActionResponse>,
    }),
    actions: {
        async createCallToAction(payload: CallToActionRequest, token?: string) {
            try {
                await callToActionApi.create(payload, token);
            } catch (error) {
                const choreStore = useChoreStore();
                choreStore.setBackendError(error);
                throw error;
            }
        },
        async addHistoryItemsToTimeline(timelineItems: Array<CaseTimelineDTO>) {
            return await Promise.all(
                timelineItems.map(async (i) => {
                    if (i.call_to_action_uuid) {
                        i.call_to_action_items = await this.getHistoryItems(i.call_to_action_uuid);
                    }
                    return i;
                })
            );
        },
        async checkSelectedAvailability() {
            // Checks if user is still allowed to view the selected callToAction
            if (!this.selected) return;
            try {
                await callToActionApi.get(this.selected.uuid);
            } catch (error) {
                const choreStore = useChoreStore();
                choreStore.setBackendError(error);
                this.selected = null;
            }
        },
        async completeSelected(note: string) {
            if (!this.selected) return;
            const choreStore = useChoreStore();
            try {
                await callToActionApi.complete(this.selected.uuid, note);

                choreStore.stopPollingSelected();

                // Remove from table
                const indexInTable = this.tableContent.findIndex((i) => i.uuid === this.selected?.uuid);
                this.tableContent.splice(indexInTable, 1);

                // Deselect
                this.selected = null;
                showToast(
                    i18n.t('components.callToActionSidebar.note.complete.success').toString(),
                    'complete-cta-toast'
                );
            } catch (error) {
                choreStore.setBackendError(error);
            }
        },
        coupleNotesToEvents(events: CallToActionHistoryItem[]): CallToActionHistoryItem[] {
            const coupledEvents = [...events];
            coupledEvents.forEach((event, index, array) => {
                const previousEvent = array[index - 1];
                const nextEvent = array[index + 1];
                if (!previousEvent && !nextEvent) return event;
                const eventIsNote = event.callToActionEvent === CallToActionEventV1.VALUE_note && event.note?.length;
                if (!eventIsNote) return event;
                const noteEventDateTime = new Date(event.datetime).getTime();
                const previousEventDateTime = new Date(previousEvent ? previousEvent.datetime : 1).getTime();
                const nextEventDateTime = new Date(nextEvent ? nextEvent.datetime : 1).getTime();
                // check if note belongs to an adjacent event
                if (![previousEventDateTime, nextEventDateTime].includes(noteEventDateTime)) return event;
                // Add note to coinciding event
                if (noteEventDateTime === previousEventDateTime) array[index - 1].note = event.note;
                if (noteEventDateTime === nextEventDateTime) array[index + 1].note = event.note;
                // remove redundant note event
                array.splice(index, 1);
            });
            return coupledEvents;
        },
        async dropSelected(note: string) {
            if (!this.selected) return;
            try {
                await callToActionApi.deleteAssignment(this.selected.uuid, note);
                const dropped: CallToActionResponse = {
                    ...this.selected,
                    ...{
                        assignedUserUuid: null,
                    },
                };
                this.selected = dropped;
                this.updateSelectedInTable(dropped);
            } catch (error) {
                const choreStore = useChoreStore();
                choreStore.setBackendError(error);
            }
        },
        async fetchCallToActionHistory(callToActionUuid: string) {
            try {
                const data = await callToActionApi.getCaseHistory(callToActionUuid);
                return data;
            } catch (error) {
                const choreStore = useChoreStore();
                choreStore.setBackendError(error);
            }
        },
        async fetchTableContent() {
            const choreStore = useChoreStore();
            try {
                const { data, lastPage } = await callToActionApi.getAll({
                    order: this.table.order,
                    page: this.table.page,
                    perPage: this.table.perPage,
                    sort: this.table.sort,
                });
                this.tableContent.push(...data);
                choreStore.startPollingTableContent(this.tableContentUpdate);
                return lastPage;
            } catch (error) {
                choreStore.setBackendError(error);
            }
        },
        async getHistoryItems(callToActionUuid?: string) {
            const uuid = callToActionUuid ?? this.selected?.uuid;
            if (!uuid) return;
            const items = await this.fetchCallToActionHistory(uuid);
            if (items?.events) items.events = this.coupleNotesToEvents(items.events);
            return items?.events.map((item: CallToActionHistoryItem) => {
                item.datetime = formatDate(parseDate(item.datetime), 'd MMMM yyyy HH:mm');
                return item;
            });
        },
        incrementTablePage() {
            this.table.page++;
        },
        async pickupSelected() {
            if (!this.selected) return;
            try {
                const pickedUp: CallToActionResponse = await callToActionApi.assignToUser(this.selected.uuid);
                this.selected = pickedUp;
                this.updateSelectedInTable(pickedUp);
            } catch (error) {
                const choreStore = useChoreStore();
                choreStore.setBackendError(error);
                this.selected = null;
                choreStore.stopPollingSelected();
            }
        },
        resetTable() {
            this.table.page = 1;
            this.tableContent = [];

            // Reset vue-infinite-loading component
            this.table.infiniteId = Date.now();
        },
        async select(uuid: string) {
            const choreStore = useChoreStore();
            try {
                const selected: CallToActionResponse = await callToActionApi.get(uuid);
                this.selected = selected;
                choreStore.startPollingSelected(this.checkSelectedAvailability);
            } catch (error) {
                choreStore.setBackendError(error);
            }
        },
        setTableSort(payload: { order: PaginatedRequestOptions['order']; sort: CallToActionSortOptions }) {
            this.table.order = payload.order;
            this.table.sort = payload.sort;
        },
        async tableContentUpdate() {
            // Checks if the table content is still up to date using chore store action
            const choreStore = useChoreStore();
            try {
                const { data }: PaginatedResponse<CallToActionResponse> = await callToActionApi.getAll({
                    order: 'desc',
                    page: 1,
                    perPage: 1,
                });
                if (data.length) {
                    choreStore.checkIfTableContentOutdated(data, i18n.t('components.callToActionTable.update_message'));
                }
            } catch (error) {
                choreStore.setBackendError(error);
            }
        },
        updateSelectedInTable(selected: CallToActionResponse) {
            const indexInTable = this.tableContent.findIndex((i) => i.uuid === selected?.uuid);
            this.tableContent.splice(indexInTable, 1, selected);
        },
    },
});

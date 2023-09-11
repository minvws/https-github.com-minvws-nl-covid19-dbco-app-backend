import { placeApi } from '@dbco/portal-api';
import type { PlaceCasesResponse, PlaceCasesTable } from '@dbco/portal-api/place.dto';
import { defineStore } from 'pinia';
import { useChoreStore } from '../chore/choreStore';
import useStatusAction from '../useStatusAction';

export const useResetIndexCount = defineStore('resetIndexCount', () => {
    const { status: resetStatus, action: reset } = useStatusAction(async (placeUuid: string) => {
        try {
            await placeApi.resetIndexCount(placeUuid);
        } catch (error) {
            const choreStore = useChoreStore();
            choreStore.setBackendError(error);
            throw error;
        }
    });

    return {
        resetStatus,
        reset,
    };
});

export const usePlaceCasesStore = defineStore('placeCases', {
    state: () => ({
        cases: [] as Array<PlaceCasesResponse>,
        table: {
            infiniteId: Date.now(),
            page: 1,
            perPage: 20,
            fetchedPages: [],
        } as PlaceCasesTable,
    }),
    actions: {
        async fetchCases(placeUuid: string) {
            try {
                if (!this.table.fetchedPages.includes(this.table.page)) {
                    const { data, lastPage } = await placeApi.getCases(
                        {
                            order: this.table.order,
                            page: this.table.page,
                            perPage: this.table.perPage,
                            sort: this.table.sort,
                        },
                        placeUuid
                    );
                    this.cases.push(...data);
                    this.table.fetchedPages.push(this.table.page);
                    this.table.lastPage = lastPage;
                }
            } catch (error) {
                const choreStore = useChoreStore();
                choreStore.setBackendError(error);
            }
        },
        incrementTablePage() {
            this.table.page++;
        },
    },
});

export type PlaceCaseStoreState = ReturnType<typeof usePlaceCasesStore>['$state'];

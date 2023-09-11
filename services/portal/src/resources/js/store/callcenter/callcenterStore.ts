import { defineStore } from 'pinia';
import { callcenterApi, caseApi } from '@dbco/portal-api';
import type { CallcenterSearchRequest, CallcenterSearchResult } from '@dbco/portal-api/callcenter.dto';
import showToast from '@/utils/showToast';
import type { AxiosError } from 'axios';
import { getAllErrors } from '@/components/form/ts/formRequest';

export enum RequestState {
    Idle,
    Pending,
    Resolved,
    Rejected,
}

export const useCallcenterStore = defineStore('callcenter', {
    state: () => ({
        searchState: RequestState.Idle,
        searchResults: [] as CallcenterSearchResult[],
        searchValidationErrors: null as { errors: { [k: string]: string } } | null,
        searchedAllFields: false,
    }),
    getters: {
        searchResultsCount: (state) => state.searchResults.length,
    },
    actions: {
        async search(payload: { searchData: CallcenterSearchRequest }) {
            this.searchState = RequestState.Pending;
            this.searchedAllFields = payload.searchData.lastname && payload.searchData.phone ? true : false;

            try {
                this.searchResults = await callcenterApi.search(payload.searchData);
                this.searchState = RequestState.Resolved;
                this.searchValidationErrors = null;
            } catch (error) {
                const { response } = error as AxiosError<any>;
                const validationErrors = response?.data?.errors;
                if (validationErrors) {
                    this.searchValidationErrors = getAllErrors({ fatal: { failed: {}, errors: validationErrors } });
                } else {
                    showToast(`Er ging iets mis bij het zoeken. Probeer het opnieuw.`, 'callcenter-search-toast', true);
                }
                this.searchState = RequestState.Rejected;
            }
        },
        async addNote(payload: { uuid: string; note: string; type: string; token: string }) {
            return await caseApi.addCaseNote(payload.uuid, payload.note, payload.type, payload.token);
        },
        reset() {
            this.searchState = RequestState.Idle;
            this.searchResults = [];
            this.searchValidationErrors = null;
            this.searchedAllFields = false;
        },
    },
});

import type { CallToActionResponse } from '@dbco/portal-api/callToAction.dto';
import type { BackendError } from '@dbco/portal-api/error';
import type { Poll } from '@/store/polling';
import type { ExpertQuestionResponse } from '@dbco/portal-api/supervision.dto';
import type { AxiosError } from 'axios';
import { defineStore } from 'pinia';
import type { TranslateResult } from 'vue-i18n';

const state = () => ({
    backendError: null as BackendError | null,
    pollSelected: {
        polling: null,
        pollInterval: 5000,
    } as Poll,
    pollTableContent: {
        polling: null,
        pollInterval: 30000,
    } as Poll,
    updateMessage: null as TranslateResult | string | null,
});

export type ChoreStoreState = ReturnType<typeof state>;

export const useChoreStore = defineStore('chore', {
    state,
    actions: {
        checkIfTableContentOutdated(
            data: Array<CallToActionResponse | ExpertQuestionResponse>,
            updateMessage: TranslateResult | string
        ) {
            if (!this.pollTableContent.pollStartedAt) return;
            const lastCreated = new Date(data[0].createdAt);
            const hasUpdate = lastCreated.valueOf() - this.pollTableContent.pollStartedAt.valueOf() > 0;
            if (hasUpdate) {
                this.updateMessage = updateMessage;
                this.stopPollingTableContent();
            }
        },
        setBackendError(error: unknown | null) {
            if (!error) return (this.backendError = null);
            const axiosError = error as AxiosError;
            const data = axiosError.response?.data as any;
            const backendError: BackendError = {
                message: data?.message ?? data?.error,
                status: axiosError.response?.status || 404,
            };
            this.backendError = backendError;
        },
        startPollingSelected(callbackFunction: () => void) {
            if (this.pollSelected.polling) clearInterval(this.pollSelected.polling);
            this.pollSelected.polling = setInterval(() => callbackFunction(), this.pollSelected.pollInterval);
        },
        startPollingTableContent(callbackFunction: () => void) {
            if (this.pollTableContent.polling) clearInterval(this.pollTableContent.polling);
            this.pollTableContent.pollStartedAt = new Date();
            this.pollTableContent.polling = setInterval(() => callbackFunction(), this.pollTableContent.pollInterval);
        },
        stopPollingSelected() {
            if (!this.pollSelected.polling) return;
            clearInterval(this.pollSelected.polling);
        },
        stopPollingTableContent() {
            if (!this.pollTableContent.polling) return;
            clearInterval(this.pollTableContent.polling);
        },
    },
});

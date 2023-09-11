export type AppStoreState = {
    hasError: boolean;
    hasPermissionError: boolean;
    isOffline: boolean;
    requestCount: number;
    lastUpdated: number;
};

const state = (): AppStoreState => ({
    hasError: false,
    hasPermissionError: false,
    isOffline: false,
    requestCount: 0,
    lastUpdated: 0,
});

import { defineStore } from 'pinia';

export const useAppStore = defineStore('app', {
    state,
    getters: {
        isUpdating: ({ requestCount }) => requestCount > 0,
    },
    actions: {
        setHasError(value: boolean) {
            this.hasError = value;
        },
        setHasPermissionError(value: boolean) {
            this.hasPermissionError = value;
        },
        setOffline(value: boolean) {
            this.isOffline = value;
        },
        handleRequestStart() {
            this.requestCount += 1;
        },
        handleRequestComplete(isSuccessful: boolean) {
            this.requestCount = Math.max(0, this.requestCount - 1);

            if (isSuccessful) {
                this.lastUpdated = Date.now();
            }
        },
    },
});

import { defineStore } from 'pinia';
import type { Poll } from '@/store/polling';
import { hasCaseLock } from '@/utils/interfaceState';
import type { CaseLock, CaseLockResponse } from '@dbco/portal-api/case.dto';
import i18n from '@/i18n';
import { generateSafeHtml } from '@/utils/safeHtml';
import { getCaseLock, refreshCaseLock } from '@dbco/portal-api/client/case.api';

export const useCaseLockStore = defineStore('caseLock', {
    state: () => ({
        caseLock: {
            removed: false,
            user: {
                name: '',
                organisation: '',
            },
        } as CaseLock,
        caseUuid: '' as string,
        idleTimeout: 180000,
        refreshInterval: 120000,
        statusPollInterval: 30000,
        poll: {
            polling: null,
        } as Poll,
    }),
    getters: {
        translatedCaseLockNotification: (state) => {
            const lock = state.caseLock;
            const userAndOrganisation =
                [lock.user.name, lock.user.organisation].filter(Boolean).join(', ') || 'iemand anders';
            return generateSafeHtml(`${i18n.t('components.caseLockNotification.title')}`, { userAndOrganisation });
        },
    },
    actions: {
        async initialize(caseUuid: string) {
            this.caseUuid = caseUuid;
            const caseLocked = hasCaseLock();
            if (caseLocked) {
                // Poll for status if case is locked by another user.
                await this.getStatus();
                this.poll.polling = setInterval(() => this.getStatus(), this.statusPollInterval);
            } else {
                // OR refresh our own case-lock before it expires.
                this.poll.polling = setInterval(() => this.refresh(), this.refreshInterval);
            }
        },
        async getStatus() {
            const response: CaseLockResponse = await getCaseLock(this.caseUuid);
            const emptyUser = {
                name: '',
                organisation: '',
            };

            if (response.status === 200) {
                const newUser = response.data.user
                    ? {
                          name: response.data.user.name,
                          organisation: response.data.user.organisation,
                      }
                    : emptyUser;
                this.caseLock = {
                    user: newUser,
                    removed: false,
                };
            } else {
                this.caseLock = {
                    user: emptyUser,
                    removed: true,
                };
            }
        },
        async refresh() {
            await refreshCaseLock(this.caseUuid);
        },
        stopPolling() {
            if (this.poll.polling) {
                clearInterval(this.poll.polling);
            }
        },
    },
});

import { defineStore } from 'pinia';
import { assignmentApi } from '@dbco/portal-api';

export const useAssignmentStore = defineStore('assignment', {
    actions: {
        async getAccessToCase(payload: { uuid: string; token: string }) {
            return await assignmentApi.getAccessToCase(payload.uuid, payload.token);
        },
    },
});

import { caseApi } from '@dbco/portal-api';
import type { IndexUpdateContactStatusQueryDTO } from '@dbco/portal-api/case.dto';
import type { Commit } from 'vuex';
import type { IndexStoreState } from './indexStore';
import { IndexStoreMutation } from './indexStoreMutation';

export enum IndexStoreAction {
    UPDATE_CONTACT_STATUS = 'UPDATE_CONTACT_STATUS',
    GET_TEST_RESULTS = 'GET_TEST_RESULTS',
}

export const indexStoreActions = {
    [IndexStoreAction.UPDATE_CONTACT_STATUS]: async (
        { commit }: { commit: Commit },
        payload: IndexUpdateContactStatusQueryDTO
    ) => {
        try {
            await caseApi.updateContactStatus(payload);
        } catch (error) {
            commit(IndexStoreMutation.SET_BACKEND_ERROR, error);
            return Promise.reject({ message: 'Er ging iets mis met het updaten van de status.' });
        }
    },
    [IndexStoreAction.GET_TEST_RESULTS]: async ({ commit, state }: { commit: Commit; state: IndexStoreState }) => {
        const testResults = await caseApi.getTestResults(state.uuid);
        commit(IndexStoreMutation.SET_TEST_RESULTS, testResults);
    },
};

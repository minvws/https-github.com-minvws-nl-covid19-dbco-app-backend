import type { TestResult } from '@dbco/portal-api/case.dto';
import type { AxiosError } from 'axios';
import type { IndexStoreState } from './indexStore';

export enum IndexStoreMutation {
    SET_BACKEND_ERROR = 'SET_BACKEND_ERROR',
    SET_TEST_RESULTS = 'SET_TEST_RESULTS',
}

export const indexStoreMutations = {
    [IndexStoreMutation.SET_BACKEND_ERROR](state: IndexStoreState, error: AxiosError | null) {
        state.backendError = error;
    },
    [IndexStoreMutation.SET_TEST_RESULTS](state: IndexStoreState, testResults: TestResult[]) {
        state.testResults = testResults;
    },
};

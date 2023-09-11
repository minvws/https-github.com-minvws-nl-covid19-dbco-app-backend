import { createLocalVue } from '@vue/test-utils';
import Vuex from 'vuex';
import type { RootStoreState } from '@/store';
import indexStore from './indexStore';
import type { IndexStoreState } from './indexStore';
import { fakeError } from '@/utils/__fakes__/shared';
import { fakeTestResult } from '@/utils/__fakes__/testResults';

describe('indexStoreMutation.ts', () => {
    afterEach(() => {
        vi.clearAllMocks();
    });

    const localVue = createLocalVue();
    localVue.use(Vuex);

    const getStore = (IndexStoreState: Partial<IndexStoreState>) => {
        const indexStoreModule = {
            ...indexStore,
            state: {
                ...indexStore.state,
                ...IndexStoreState,
            },
            actions: indexStore.actions,
        };

        return new Vuex.Store<RootStoreState>({
            modules: {
                index: indexStoreModule,
            },
        });
    };

    it('should set backendError when SET_BACKEND_ERROR is committed', () => {
        const store = getStore({});
        const spyOnCommit = vi.spyOn(store, 'commit');
        store.commit('index/SET_BACKEND_ERROR', fakeError);

        expect(spyOnCommit).toHaveBeenCalledWith('index/SET_BACKEND_ERROR', fakeError);
        expect(store.state.index.backendError).toStrictEqual(fakeError);
    });

    it('should set testResults when SET_TEST_RESULTS is committed', () => {
        const store = getStore({});
        const spyOnCommit = vi.spyOn(store, 'commit');
        store.commit('index/SET_TEST_RESULTS', [fakeTestResult]);

        expect(spyOnCommit).toHaveBeenCalledWith('index/SET_TEST_RESULTS', [fakeTestResult]);
        expect(store.state.index.testResults).toStrictEqual([fakeTestResult]);
    });
});

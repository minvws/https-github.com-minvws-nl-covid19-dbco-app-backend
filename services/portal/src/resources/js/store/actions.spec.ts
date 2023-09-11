import { createLocalVue } from '@vue/test-utils';
import { sharedActions } from './actions';
import Vuex from 'vuex';
import type { StoreType } from './storeType';
import type { RootStoreState } from '@/store';
import * as formRequest from '@/components/form/ts/formRequest';
import { flushCallStack } from '@dbco/ui-library/test';
import { fakerjs } from '@/utils/test';
import { sharedMutations } from './mutations';

type FakeIndexStoreState = {
    uuid: string;
    fragments: {
        fakeFragment?: {
            fakeFragmentProperty: string;
        };
    };
};

const getDefaultState = (): FakeIndexStoreState => ({
    uuid: '',
    fragments: {},
});

const fakeStore = {
    namespaced: true,
    state: getDefaultState(),
    actions: {
        ...sharedActions('fake' as StoreType),
    },
    mutations: {
        ...sharedMutations(getDefaultState),
    },
};

describe('actions.ts', () => {
    afterEach(() => {
        vi.clearAllMocks();
    });

    const localVue = createLocalVue();
    localVue.use(Vuex);

    const getStore = () => {
        const fakeStoreModule = {
            ...fakeStore,
            state: {
                ...fakeStore.state,
            },
            actions: fakeStore.actions,
            mutations: fakeStore.mutations,
        };

        return new Vuex.Store<RootStoreState>({
            modules: {
                index: fakeStoreModule,
            },
        });
    };

    it('should log console error when the load action is triggered with an unknown store type', async () => {
        vi.spyOn(console, 'error').mockImplementation(() => {});
        const store = getStore();
        await store.dispatch('index/LOAD', {});
        expect(console.error).toHaveBeenCalledWith('ACTIONS.TS fake: Store action FILL unknown store type');
    });

    it('should log error to console when the updateFormValue action call is a duplicate of a previous call in the update queue', async () => {
        vi.spyOn(console, 'error').mockImplementation(() => {});
        vi.spyOn(formRequest, 'update').mockImplementationOnce(() =>
            Promise.resolve({ isDuplicated: true, errors: {} })
        );
        const store = getStore();
        await store.dispatch('index/UPDATE_FORM_VALUE', {
            fakeFragment: { fakeFragmentProperty: fakerjs.string.uuid() },
        });
        await flushCallStack();
        expect(console.error).toHaveBeenCalledWith('ACTIONS.TS fake: Duplicated request, ignoring');
    });

    it('should commit fragment data to store it is in the updateFormValue response', async () => {
        const givenData = {
            fakeFragment: {
                fakeFragmentProperty: fakerjs.lorem.word(),
            },
        };
        vi.spyOn(console, 'error').mockImplementation(() => {});
        vi.spyOn(formRequest, 'update').mockImplementationOnce(() =>
            Promise.resolve({ isDuplicated: false, errors: {}, data: {} })
        );
        const store = getStore();
        const spyOnCommit = vi.spyOn(store, 'commit');
        await store.dispatch('index/UPDATE_FORM_VALUE', givenData);
        await flushCallStack();
        expect(spyOnCommit).toHaveBeenCalledWith('index/UPDATE_FRAGMENTS', givenData, undefined);
    });

    it('should commit errors to store when they are returned in the updateFormValue response', async () => {
        const fakeError = {
            errors: { fakeFragment: fakerjs.lorem.words() },
        };
        vi.spyOn(console, 'error').mockImplementation(() => {});
        vi.spyOn(formRequest, 'update').mockImplementationOnce(() =>
            Promise.resolve({
                isDuplicated: false,
                data: {},
                errors: {
                    fakeError,
                },
            })
        );
        const store = getStore();
        const spyOnCommit = vi.spyOn(store, 'commit');
        await store.dispatch('index/UPDATE_FORM_VALUE', {
            fakeFragment: { fakeFragmentProperty: fakerjs.string.uuid() },
        });
        await flushCallStack();
        expect(spyOnCommit).toHaveBeenCalledWith(
            'index/UPDATE_FORM_ERRORS',
            {
                fakeError,
            },
            undefined
        );
    });
});

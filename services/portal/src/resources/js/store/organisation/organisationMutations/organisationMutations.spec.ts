import { createLocalVue } from '@vue/test-utils';
import Vuex from 'vuex';
import organisationStore from '../organisationStore';
import type { OrganisationStoreState } from '../organisationTypes';
import type { RootStoreState } from '@/store';

const mockOrganisations = [
    {
        uuid: '00000000-0000-0000-0000-000000000000',
        name: 'Demo GGD1',
    },
    {
        uuid: '0296ab48-1576-4262-af38-78e9ef06ed07',
        name: 'GGD Gelderland-Midden',
    },
    {
        uuid: '0535e4cd-af98-4113-999e-888f9fdf2a40',
        name: 'GGD Noord- en Oost Gelderland',
    },
    {
        uuid: '08eee942-53ef-4386-96b1-e70bd80d464b',
        name: 'GGD Hollands Noorden',
    },
];

describe('organisationMutations.ts', () => {
    afterEach(() => {
        vi.clearAllMocks();
    });
    const localVue = createLocalVue();
    localVue.use(Vuex);

    const getStore = (organisationStoreState: OrganisationStoreState) => {
        const organisationStoreModule = {
            ...organisationStore,
            state: {
                ...organisationStore.state,
                ...organisationStoreState,
            },
            mutations: organisationStore.mutations,
        };

        return new Vuex.Store<RootStoreState>({
            modules: {
                organisation: organisationStoreModule,
            },
        });
    };

    it('should clear all state properties except state.all when CLEAR_KEEP_ALL is committed', async () => {
        const organisationStoreState = {
            all: mockOrganisations,
            current: mockOrganisations[0],
            currentFromAddressSearch: mockOrganisations[2].uuid,
            error: 'TestError',
        };
        const store = getStore(organisationStoreState);
        await store.commit('organisation/CLEAR_KEEP_ALL');

        expect(store.state.organisation).toStrictEqual({
            all: mockOrganisations,
            current: undefined,
            currentFromAddressSearch: undefined,
            error: '',
        });
    });

    it('should update state.all when SET_ALL is committed', async () => {
        const organisationStoreState = {
            all: [],
            current: undefined,
            currentFromAddressSearch: undefined,
            error: '',
        };
        const store = getStore(organisationStoreState);
        await store.commit('organisation/SET_ALL', mockOrganisations);

        expect(store.state.organisation.all).toBe(mockOrganisations);
    });

    it('should update state.current when SET_CURRENT is committed', async () => {
        const organisationStoreState = {
            all: [],
            current: undefined,
            currentFromAddressSearch: undefined,
            error: '',
        };
        const store = getStore(organisationStoreState);
        await store.commit('organisation/SET_CURRENT', mockOrganisations[0]);

        expect(store.state.organisation.current).toBe(mockOrganisations[0]);
    });

    it('should update state.currentFromAddressSearch when SET_CURRENT_FROM_ADDRESS_SEARCH is committed', async () => {
        const organisationStoreState = {
            all: [],
            current: undefined,
            currentFromAddressSearch: undefined,
            error: '',
        };
        const store = getStore(organisationStoreState);
        await store.commit('organisation/SET_CURRENT_FROM_ADDRESS_SEARCH', mockOrganisations[0]);

        expect(store.state.organisation.currentFromAddressSearch).toBe(mockOrganisations[0]);
    });

    it('should set organisation with uuid matching given uuid as state.current when SET_CURRENT_BY_UUID is committed', async () => {
        const organisationStoreState = {
            all: mockOrganisations,
            current: undefined,
            currentFromAddressSearch: undefined,
            error: '',
        };
        const store = getStore(organisationStoreState);
        await store.commit('organisation/SET_CURRENT_BY_UUID', mockOrganisations[2].uuid);

        expect(store.state.organisation.current).toBe(mockOrganisations[2]);
    });

    it('should set given string as state.error when SET_ERROR is committed', async () => {
        const organisationStoreState = {
            all: [],
            current: undefined,
            currentFromAddressSearch: undefined,
            error: '',
        };
        const store = getStore(organisationStoreState);
        await store.commit('organisation/SET_ERROR', 'test this error mutation.');

        expect(store.state.organisation.error).toBe('test this error mutation.');
    });
});

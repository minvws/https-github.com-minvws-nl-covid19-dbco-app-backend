import { createLocalVue } from '@vue/test-utils';
import Vuex from 'vuex';
import organisationStore from '../organisationStore';
import type { OrganisationStoreState } from '../organisationTypes';
import type { RootStoreState } from '@/store';
import { organisationApi } from '@dbco/portal-api';
import type { Organisation } from '@dbco/portal-api/organisation.dto';

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

describe('organisationActions.ts', () => {
    afterEach(() => {
        vi.clearAllMocks();
    });

    const localVue = createLocalVue();
    localVue.use(Vuex);

    const getStore = (organisationStoreState: Partial<OrganisationStoreState>) => {
        const organisationStoreModule = {
            ...organisationStore,
            state: {
                ...organisationStore.state,
                ...organisationStoreState,
            },
            actions: organisationStore.actions,
        };

        return new Vuex.Store<RootStoreState>({
            modules: {
                organisation: organisationStoreModule,
            },
        });
    };

    it('should commit SET_ALL when FETCH_ALL succeeds', async () => {
        vi.spyOn(organisationApi, 'getOrganisations').mockImplementationOnce(() => Promise.resolve(mockOrganisations));
        const store = getStore({});
        const spyOnCommit = vi.spyOn(store, 'commit');
        await store.dispatch('organisation/FETCH_ALL');

        expect(spyOnCommit).toHaveBeenCalledWith('organisation/SET_ALL', mockOrganisations, undefined);
    });

    it('should commit SET_ERROR when FETCH_ALL fails', async () => {
        vi.spyOn(organisationApi, 'getOrganisations').mockImplementationOnce(() => Promise.reject<Organisation[]>());
        const store = getStore({});
        const spyOnCommit = vi.spyOn(store, 'commit');
        await store.dispatch('organisation/FETCH_ALL');

        expect(spyOnCommit).toHaveBeenCalledWith('organisation/SET_ERROR', 'Organisation fetch failed', undefined);
    });

    it('should cache fetched data', async () => {
        const fetch = vi
            .spyOn(organisationApi, 'getOrganisations')
            .mockImplementationOnce(() => Promise.resolve(mockOrganisations));
        const store = getStore({});

        await store.dispatch('organisation/FETCH_ALL');
        await store.dispatch('organisation/FETCH_ALL');

        expect(fetch).toHaveBeenCalledTimes(1);
    });
});

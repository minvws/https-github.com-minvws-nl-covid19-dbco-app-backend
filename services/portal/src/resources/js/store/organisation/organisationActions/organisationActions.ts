import { getOrganisations } from '@dbco/portal-api/client/organisation.api';
import type { Commit } from 'vuex';
import { OrganisationMutations } from '../organisationMutations/organisationMutations';
import type { OrganisationStoreState } from '../organisationTypes';

export enum OrganisationActions {
    FETCH_ALL = 'FETCH_ALL',
}

const fetchAll = async ({ commit, state }: { commit: Commit; state: OrganisationStoreState }) => {
    // Cache the fetch. The list of organisations does not change between calls.
    if (state.all.length > 0) {
        return;
    }

    try {
        const organisations = await getOrganisations();
        commit(OrganisationMutations.SET_ALL, organisations);
    } catch (error) {
        commit(OrganisationMutations.SET_ERROR, 'Organisation fetch failed');
    }
};

export const organisationActions = {
    [OrganisationActions.FETCH_ALL]: fetchAll,
};

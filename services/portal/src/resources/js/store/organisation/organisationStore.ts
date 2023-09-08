import { sharedMutations } from '../mutations';

import { organisationActions } from './organisationActions/organisationActions';
import { organisationMutations } from './organisationMutations/organisationMutations';
import type { OrganisationStoreState } from './organisationTypes';
import type { Organisation } from '@/components/form/ts/formTypes';

const getDefaultState = (): OrganisationStoreState => ({
    all: [] as Organisation[],
    current: undefined,
    currentFromAddressSearch: undefined,
    error: '',
});

export default {
    namespaced: true,
    state: getDefaultState(),
    actions: organisationActions,
    mutations: {
        ...sharedMutations(getDefaultState),
        ...organisationMutations,
    },
    getters: {
        currentFromAddressSearch: (state: OrganisationStoreState) =>
            state.all.find((org) => org.uuid === state.currentFromAddressSearch),
        getOrganisationByUuid: (state: OrganisationStoreState) => (uuid: string) => {
            return state.all.find((org) => org.uuid === uuid);
        },
    },
};

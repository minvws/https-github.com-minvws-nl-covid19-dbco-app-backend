import type { Organisation } from '@/components/form/ts/formTypes';
import type { OrganisationStoreState } from '../organisationTypes';

export enum OrganisationMutations {
    CLEAR_KEEP_ALL = 'CLEAR_KEEP_ALL',
    SET_ALL = 'SET_ALL',
    SET_CURRENT = 'SET_CURRENT',
    SET_CURRENT_FROM_ADDRESS_SEARCH = 'SET_CURRENT_FROM_ADDRESS_SEARCH',
    SET_CURRENT_BY_UUID = 'SET_CURRENT_BY_UUID',
    SET_ERROR = 'SET_ERROR',
}

export const organisationMutations = {
    [OrganisationMutations.CLEAR_KEEP_ALL](state: OrganisationStoreState) {
        state.current = undefined;
        state.currentFromAddressSearch = undefined;
        state.error = '';
    },
    [OrganisationMutations.SET_ALL]: (state: OrganisationStoreState, organisations: Organisation[]) =>
        (state.all = organisations),
    [OrganisationMutations.SET_CURRENT]: (
        state: OrganisationStoreState,
        organisation: Partial<Organisation> | undefined
    ) => (state.current = organisation),
    [OrganisationMutations.SET_CURRENT_FROM_ADDRESS_SEARCH]: (state: OrganisationStoreState, uuid: string) =>
        (state.currentFromAddressSearch = uuid),
    [OrganisationMutations.SET_CURRENT_BY_UUID]: (state: OrganisationStoreState, uuid: string) =>
        (state.current = state.all.find((org) => org.uuid === uuid)),
    [OrganisationMutations.SET_ERROR]: (state: OrganisationStoreState, error: string) => (state.error = error),
};

import { sharedMutations } from '../mutations';

import { placeActions } from './placeActions/placeActions';
import { placeMutations } from './placeMutations/placeMutations';
import type { PlaceStoreState } from './placeTypes';

const getDefaultState = (): PlaceStoreState => ({
    current: {},
    locations: {
        current: {},
    },
    sections: {
        callQueue: {
            changeLabelQueue: [],
            createQueue: [],
            mergeQueue: [],
        },
        current: [],
    },
});

export default {
    namespaced: true,
    state: getDefaultState(),
    actions: placeActions,
    mutations: {
        ...sharedMutations(getDefaultState),
        ...placeMutations,
    },
    getters: {
        currentLocation: (state: PlaceStoreState) => state.locations?.current,
        currentSections: (state: PlaceStoreState) => state.sections?.current,
        isVerified: (state: PlaceStoreState) => state.current?.isVerified,
        organisationUuidByPostalCode: (state: PlaceStoreState) => state.current?.organisationUuidByPostalCode,
        organisationUuid: (state: PlaceStoreState) => state.current?.organisationUuid,
        organisationIsOverwritten: (state: PlaceStoreState) => {
            return (
                state.current?.organisationUuid?.length &&
                state.current.organisationUuid !== state.current.organisationUuidByPostalCode
            );
        },
    },
};

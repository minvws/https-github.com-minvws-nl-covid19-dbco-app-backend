import type { Commit } from 'vuex';
import { placeApi } from '@dbco/portal-api';
import { PlaceMutations } from '../placeMutations/placeMutations';
import type { PlaceDTO } from '@dbco/portal-api/place.dto';
import type { PlaceStoreState } from '../placeTypes';

import makeAllCallQueueRequests from '@/components/contextManager/PlacesEdit/SectionManagement/utils/makeAllCallQueueRequests';

export enum PlaceActions {
    CREATE = 'CREATE',
    FETCH_SECTIONS = 'FETCH_SECTIONS',
    SAVE_SECTIONS = 'SAVE_SECTIONS',
    TOGGLE_VERIFICATION = 'TOGGLE_VERIFICATION',
    UPDATE = 'UPDATE',
}

const create = async ({ commit }: { commit: Commit }, place: Partial<PlaceDTO>) => {
    try {
        const placeFromApi = await placeApi.createPlace(place);
        commit(PlaceMutations.SET_PLACE, placeFromApi);
    } catch (error) {
        commit(PlaceMutations.SET_PLACE, place);
    }
};

const fetchSections = async ({ commit }: { commit: Commit }, placeUuid: string) => {
    await placeApi.getSections(placeUuid).then((data) => {
        commit(PlaceMutations.SET_SECTIONS, data.sections);
    });
};

const saveSections = async ({ state }: { state: PlaceStoreState }) => {
    if (!state.current.uuid) return;
    await makeAllCallQueueRequests(state.current.uuid, state.sections.callQueue);
};

const toggleVerification = async ({ commit }: { commit: Commit }, place: Partial<PlaceDTO>) => {
    if (!place.uuid) return;
    const { isVerified } = place.isVerified ? await placeApi.unverify(place.uuid) : await placeApi.verify(place.uuid);
    commit(PlaceMutations.SET_PLACE, place);
    commit(PlaceMutations.SET_VERIFICATION, isVerified);
};

const update = async ({ commit }: { commit: Commit }, place: Partial<PlaceDTO>) => {
    try {
        const placeFromApi = await placeApi.updatePlace(place);
        commit(PlaceMutations.SET_PLACE, {
            ...place,
            ...{
                organisationUuid: placeFromApi.organisationUuid,
                organisationUuidByPostalCode: placeFromApi.organisationUuidByPostalCode,
            },
        });
    } catch (error) {
        commit(PlaceMutations.SET_PLACE, place);
    }
};

export const placeActions = {
    [PlaceActions.CREATE]: create,
    [PlaceActions.FETCH_SECTIONS]: fetchSections,
    [PlaceActions.SAVE_SECTIONS]: saveSections,
    [PlaceActions.TOGGLE_VERIFICATION]: toggleVerification,
    [PlaceActions.UPDATE]: update,
};

import { v4 as uuidv4 } from 'uuid';

import changeSectionLabelInQueue from '@/components/contextManager/PlacesEdit/SectionManagement/utils/changeSectionLabelInQueue';
import mergeSectionsInCallQueue from '@/components/contextManager/PlacesEdit/SectionManagement/utils/mergeSectionsInCallQueue';

import type { LocationDTO, PlaceDTO } from '@dbco/portal-api/place.dto';
import type { CurrentSection, PlaceStoreState } from '../placeTypes';

export enum PlaceMutations {
    ADD_SECTION = 'ADD_SECTION',
    CHANGE_SECTION_LABEL = 'CHANGE_SECTION_LABEL',
    MERGE_SECTIONS = 'MERGE_SECTIONS',
    SET_LOCATION = 'SET_LOCATION',
    SET_ORGANISATION = 'SET_ORGANISATION',
    SET_ORGANISATION_BY_POSTALCODE = 'SET_ORGANISATION_BY_POSTALCODE',
    SET_PLACE = 'SET_PLACE',
    SET_SECTIONS = 'SET_SECTIONS',
    SET_VERIFICATION = 'SET_VERIFICATION',
}

export const placeMutations = {
    [PlaceMutations.ADD_SECTION](state: PlaceStoreState, label: string) {
        // Add new section to create queue and local state.
        const newLabel = { label };

        // Mock section for local use until either call to BE or state reset.
        const newTempSection = {
            uuid: uuidv4(),
            ...newLabel,
            indexCount: 0,
        };

        state.sections.callQueue.createQueue.push(newTempSection);

        state.sections.current = [...state.sections.current, { ...newTempSection }];
    },
    [PlaceMutations.CHANGE_SECTION_LABEL](state: PlaceStoreState, payload: { label: string; uuid: string }) {
        // Change label in call queue.
        state.sections.callQueue = changeSectionLabelInQueue(payload.label, payload.uuid, state.sections.callQueue);

        // Change label in local state.
        const targetSection = state.sections.current.findIndex((section) => section.uuid === payload.uuid);
        state.sections.current[targetSection].label = payload.label;
    },
    [PlaceMutations.MERGE_SECTIONS](
        state: PlaceStoreState,
        payload: { mainSection: CurrentSection; mergeSections: CurrentSection[] }
    ) {
        // Place merge in call queue.
        state.sections.callQueue = mergeSectionsInCallQueue(
            payload.mainSection,
            payload.mergeSections,
            state.sections.callQueue
        );

        // Index count of merged sections is summed up in local state.
        // This is a suboptimal compromise made by the PO due to lack of index info for calculation.
        const indexSumToMerge = payload.mergeSections.reduce((a, b) => a + b['indexCount'], 0);

        // Filter out any sections to be merged into another
        const filteredSections = state.sections.current.filter(
            (pS) => !payload.mergeSections.find((mS) => mS.uuid === pS.uuid)
        );

        // Add new indexCount to section to be merged into
        const targetSection = filteredSections.find((section) => section.uuid === payload.mainSection.uuid);
        if (targetSection) {
            targetSection.indexCount += indexSumToMerge;
            targetSection.hasCalculatedIndex = true;
        }

        // Update local state with merge.
        state.sections.current = filteredSections;
    },
    [PlaceMutations.SET_LOCATION]: (state: PlaceStoreState, location: Partial<LocationDTO>) =>
        (state.locations.current = location),
    [PlaceMutations.SET_ORGANISATION]: (state: PlaceStoreState, organisationUuid: string | null) =>
        (state.current.organisationUuid = organisationUuid),
    [PlaceMutations.SET_ORGANISATION_BY_POSTALCODE]: (state: PlaceStoreState, organisationUuid: string | null) =>
        (state.current.organisationUuidByPostalCode = organisationUuid),
    [PlaceMutations.SET_PLACE]: (state: PlaceStoreState, place: Partial<PlaceDTO>) => (state.current = place),
    [PlaceMutations.SET_SECTIONS](state: PlaceStoreState, sections: CurrentSection[]) {
        // Entry for indexes not linked to sections. Currently only for display purposes, with plans for merging in the near future.
        const noSectionEntry = {
            indexCount: state.current.indexCount || 0,
            label: 'Geen afdeling, team of klas geselecteerd',
            uuid: 'no-section-entry-uuid',
        };

        state.sections.current = [...sections, { ...noSectionEntry }];
    },
    [PlaceMutations.SET_VERIFICATION](state: PlaceStoreState, isVerified: boolean) {
        state.current.isVerified = isVerified;
    },
};

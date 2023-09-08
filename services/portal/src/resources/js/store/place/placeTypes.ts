/* istanbul ignore file */
import type { PlaceDTO, LocationDTO } from '@dbco/portal-api/place.dto';
import type { Section } from '@dbco/portal-api/section.dto';

export interface CallQueue {
    changeLabelQueue: QueuedLabelChange[];
    createQueue: Section[];
    mergeQueue: QueuedMerge[];
}

export interface CurrentSection extends Section {
    hasCalculatedIndex?: boolean;
}

export interface PlaceStoreState {
    current: Partial<PlaceDTO>;
    locations: {
        current: Partial<LocationDTO>;
    };
    sections: {
        callQueue: CallQueue;
        current: CurrentSection[];
    };
}

export type QueuedLabelChange = Pick<Section, 'label' | 'uuid'>;

export interface QueuedMerge {
    payload: string[];
    target: string | Section;
}

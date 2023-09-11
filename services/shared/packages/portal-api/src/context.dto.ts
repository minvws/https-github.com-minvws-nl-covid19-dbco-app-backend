import type { Place } from './place.dto';

export type Context = {
    uuid: string;
    label?: string;
    remarks?: string;
    explanation?: string;
    detailedExplanation?: string;
    moments?: string[];
    placeUuid?: string | null;
    place?: Place;
    relationship?: string[];
    otherRelationship?: string;
    isSource?: boolean;
};

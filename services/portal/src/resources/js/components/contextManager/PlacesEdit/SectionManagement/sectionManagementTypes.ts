/* istanbul ignore file */

import type { Section } from '@dbco/portal-api/section.dto';

export interface CallQueue {
    changeLabelQueue: QueuedLabelChange[];
    createQueue: Section[];
    mergeQueue: QueuedMerge[];
}

export interface CurrentSection extends Section {
    hasCalculatedIndex?: boolean;
}

export type QueuedLabelChange = Pick<Section, 'label' | 'uuid'>;

export interface QueuedMerge {
    payload: string[];
    target: string | Section;
}

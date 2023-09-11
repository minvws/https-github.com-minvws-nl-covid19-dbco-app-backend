import type { CallQueue, QueuedMerge } from '../../sectionManagementTypes';
import { placeApi } from '@dbco/portal-api';

import getCreateThenMergeQueue from '../getCreateThenMergeQueue/getCreateThenMergeQueue';
import getCreateQueue from '../getCreateQueue/getCreateQueue';

/**
 * Make createPlaceSections request with entries in createQueue.
 *
 * @param placeUuid uuid of place the sections belong to.
 * @param createQueue queue for creation api calls to make if changes are saved.
 * @param mergeQueue queue for merge api calls to make if changes are saved.
 * @returns createPlaceSectionsRequest.
 */
const makeCreateRequests = (
    placeUuid: string,
    createQueue: CallQueue['createQueue'],
    mergeQueue: CallQueue['mergeQueue']
) => {
    const createThenMergeQueue: QueuedMerge[] = getCreateThenMergeQueue(mergeQueue);
    const queuedCreations: { label: string }[] = getCreateQueue(createQueue, createThenMergeQueue);
    if (!queuedCreations.length) return;
    return placeApi.createPlaceSections(placeUuid, queuedCreations);
};

export default makeCreateRequests;

import type { CallQueue, QueuedMerge } from '../../sectionManagementTypes';
import { placeApi } from '@dbco/portal-api';
import getMergeQueue from '../getMergeQueue/getMergeQueue';

/**
 * Make mergeSections requests with entries in mergeQueue.
 *
 * @param placeUuid uuid of place the sections belong to.
 * @param mergeQueue queue for merge api calls to make if changes are saved.
 * @returns MergeSections requests.
 */
const makeMergeRequests = (placeUuid: string, mergeQueue: CallQueue['mergeQueue']) => {
    const queuedMerges: QueuedMerge[] = getMergeQueue(mergeQueue);
    return Promise.all(
        queuedMerges.map((merge) => placeApi.mergeSections(placeUuid, merge.target as string, merge.payload))
    );
};

export default makeMergeRequests;

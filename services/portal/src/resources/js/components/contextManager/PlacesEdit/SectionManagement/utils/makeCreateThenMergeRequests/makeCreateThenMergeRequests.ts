import type { CallQueue, QueuedMerge } from '../../sectionManagementTypes';
import { placeApi } from '@dbco/portal-api';

import getCreateThenMergeQueue from '../getCreateThenMergeQueue/getCreateThenMergeQueue';
import type { Section } from '@dbco/portal-api/section.dto';

/**
 * Make chained createPlaceSections and mergeSections requests for each entry in createThenMergeQueue.
 *
 * @param placeUuid uuid of place the sections belong to.
 * @param mergeQueue queue for merge api calls to make if changes are saved.
 * @returns Chained createPlaceSections and mergeSections requests.
 */
const makeCreateThenMergeRequests = (placeUuid: string, mergeQueue: CallQueue['mergeQueue']) => {
    const createThenMergeQueue: QueuedMerge[] = getCreateThenMergeQueue(mergeQueue);
    return Promise.all(
        createThenMergeQueue.map((merge) =>
            placeApi
                .createPlaceSections(placeUuid, [{ label: (merge.target as Section).label }])
                .then((data) => placeApi.mergeSections(placeUuid, data.sections[0].uuid as string, merge.payload))
        )
    );
};

export default makeCreateThenMergeRequests;

import type { CallQueue } from '../../sectionManagementTypes';

/**
 * If section to be merged into is already in merge queue as target, return its index.
 *
 * @param uuid uuid of section to be merged into.
 * @param mergeQueue queue for api calls to make if changes are saved.
 * @returns index of section to be merged into that's already in mergeQueue as target.
 */
const getTargetAlreadyInQueueIndex = (uuid: string, mergeQueue: CallQueue['mergeQueue']): number =>
    mergeQueue.findIndex((merge) => typeof merge.target === 'string' && merge.target === uuid);

export default getTargetAlreadyInQueueIndex;

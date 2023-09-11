import type { CallQueue, QueuedMerge } from '../../sectionManagementTypes';

/**
 * Gets merges in merge queue where target is new and needs to be created before merge.
 *
 * @param mergeQueue queue for merge api calls to make if changes are saved.
 * @returns Array of queued merges.
 */
const getCreateThenMergeQueue = (mergeQueue: CallQueue['mergeQueue']): QueuedMerge[] =>
    mergeQueue.filter((mQ) => typeof mQ.target !== 'string');

export default getCreateThenMergeQueue;

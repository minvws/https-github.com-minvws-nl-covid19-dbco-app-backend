import type { CallQueue, QueuedMerge } from '../../sectionManagementTypes';

/**
 * Gets merges in merge queue where target already exists. No create call necessary.
 *
 * @param mergeQueue queue for merge api calls to make if changes are saved.
 * @returns Array of queued merges.
 */
const getMergeQueue = (mergeQueue: CallQueue['mergeQueue']): QueuedMerge[] =>
    mergeQueue.filter((mQ) => typeof mQ.target === 'string');

export default getMergeQueue;

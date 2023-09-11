import type { Section } from '@dbco/portal-api/section.dto';
import type { CallQueue } from '../../sectionManagementTypes';

/**
 * Update queue for merge calls to make if changes are saved.
 * @param targetAlreadyInQueueIndex sections to be merged into another that were already in merge queue as target.
 * @param newPayload payload of new merge.
 * @param previousPayload combined payload of previous merge targets.
 * @param newTarget merge target.
 * @param mergeQueue queue for merge api calls to make if changes are saved.
 * @returns updated call queue.
 */
const updateMergeQueue = (
    targetAlreadyInQueueIndex: number,
    newPayload: string[],
    previousPayload: string[],
    newTarget: string | Section,
    mergeQueue: CallQueue['mergeQueue']
): CallQueue['mergeQueue'] => {
    const newMergeQueue = [...mergeQueue];
    // If a merge queue entry with the same target already exists. Just update its payload.
    if (targetAlreadyInQueueIndex >= 0) {
        newMergeQueue[targetAlreadyInQueueIndex].payload.push(...newPayload, ...previousPayload);
    } else {
        const newMerge = {
            target: newTarget,
            payload: [...previousPayload, ...newPayload],
        };
        newMergeQueue.push(newMerge);
    }
    return newMergeQueue;
};

export default updateMergeQueue;

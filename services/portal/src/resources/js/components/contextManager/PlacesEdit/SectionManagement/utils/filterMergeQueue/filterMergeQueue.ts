import type { CallQueue, CurrentSection } from '../../sectionManagementTypes';

/**
 * Remove merge queue entries with target that will now be merged into another.
 * @param mergeSections sections to be merged into another.
 * @param mergeQueue queue for merge api calls to make if changes are saved.
 * @returns filtered merge queue.
 */
const filterMergeQueue = (
    mergeSections: CurrentSection[],
    mergeQueue: CallQueue['mergeQueue']
): CallQueue['mergeQueue'] =>
    mergeQueue.filter(
        (merge) =>
            !mergeSections.some((mS) =>
                typeof merge.target === 'string' ? mS.uuid === merge.target : mS.uuid === merge.target.uuid
            )
    );

export default filterMergeQueue;

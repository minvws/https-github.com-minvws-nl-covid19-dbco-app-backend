import type { CallQueue, CurrentSection, QueuedMerge } from '../../sectionManagementTypes';

/**
 * Sections to be merged into another that were already in merge queue as target.
 * @param mergeSections sections to be merged into another.
 * @param mergeQueue queue for merge api calls to make if changes are saved.
 * @returns previous merge targets now to be merged into another.
 */
const getPreviousTargetsToBeMerged = (
    mergeSections: CurrentSection[],
    mergeQueue: CallQueue['mergeQueue']
): QueuedMerge[] =>
    mergeQueue.filter((merge) =>
        mergeSections.some((mS) =>
            typeof merge.target === 'string' ? mS.uuid === merge.target : mS.uuid === merge.target.uuid
        )
    );

export default getPreviousTargetsToBeMerged;

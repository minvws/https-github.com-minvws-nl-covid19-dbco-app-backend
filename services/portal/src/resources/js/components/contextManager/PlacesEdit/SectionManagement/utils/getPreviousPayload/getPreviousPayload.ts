import type { QueuedMerge } from '../../sectionManagementTypes';

/**
 * Combined payload of sections to merge already in merge queue as target.
 * To be added to the new merge queue entry's payload.
 * @param previousTargetsToBeMerged sections to be merged into another that were already in merge queue as target.
 * @returns combined payload of previous merge targets.
 */
const getPreviousPayload = (previousTargetsToBeMerged: QueuedMerge[]): string[] =>
    previousTargetsToBeMerged.map((pT) => pT.payload).reduce((a, b) => a.concat(b), []);

export default getPreviousPayload;

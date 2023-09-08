import type { CallQueue, CurrentSection } from '../sectionManagementTypes';

import checkIfTargetNotYetCreated from './checkIfTargetNotYetCreated/checkIfTargetNotYetCreated';
import cleanupMergeSections from './cleanupMergeSections/cleanupMergeSections';
import filterCreateQueue from './filterCreateQueue/filterCreateQueue';
import filterLabelQueue from './filterLabelQueue/filterLabelQueue';
import filterMergeQueue from './filterMergeQueue/filterMergeQueue';
import getPreviousPayload from './getPreviousPayload/getPreviousPayload';
import getPreviousTargetsToBeMerged from './getPreviousTargetsToBeMerged/getPreviousTargetsToBeMerged';
import getTargetAlreadyInQueueIndex from './getTargetAlreadyInQueueIndex/getTargetAlreadyInQueueIndex';
import setMergeTarget from './setMergeTarget/setMergeTarget';
import updateMergeQueue from './updateMergeQueue/updateMergeQueue';

/**
 * Update call queue with merge.
 *
 * @param mainSection section to merge into.
 * @param mergeSections sections being merged.
 * @param callQueue queue for api calls to make if changes are saved.
 * @returns updated call queue.
 */
const mergeSectionsInCallQueue = (
    mainSection: CurrentSection,
    mergeSections: CurrentSection[],
    callQueue: CallQueue
): CallQueue => {
    const newCallQueue = { ...callQueue };
    const targetAlreadyInQueueIndex = getTargetAlreadyInQueueIndex(mainSection.uuid, callQueue.mergeQueue);
    const targetNotYetCreated = checkIfTargetNotYetCreated(mainSection.uuid, callQueue.createQueue);
    const newTarget = setMergeTarget(mainSection, targetNotYetCreated);
    const newPayload = cleanupMergeSections(mergeSections, callQueue.createQueue);
    const previousTargetsToBeMerged = getPreviousTargetsToBeMerged(mergeSections, callQueue.mergeQueue);
    const previousPayload = previousTargetsToBeMerged.length ? getPreviousPayload(previousTargetsToBeMerged) : [];

    if (newPayload.length || previousPayload.length)
        newCallQueue.mergeQueue = updateMergeQueue(
            targetAlreadyInQueueIndex,
            newPayload,
            previousPayload,
            newTarget,
            newCallQueue.mergeQueue
        );

    newCallQueue.mergeQueue = filterMergeQueue(mergeSections, newCallQueue.mergeQueue);

    newCallQueue.createQueue = filterCreateQueue(mergeSections, newCallQueue.createQueue);

    newCallQueue.changeLabelQueue = filterLabelQueue(mergeSections, newCallQueue.changeLabelQueue);

    return newCallQueue;
};

export default mergeSectionsInCallQueue;

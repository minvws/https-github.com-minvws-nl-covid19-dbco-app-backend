import type { CallQueue } from '../sectionManagementTypes';
import makeChangeLabelRequest from './makeChangeLabelRequest/makeChangeLabelRequest';
import makeCreateThenMergeRequests from './makeCreateThenMergeRequests/makeCreateThenMergeRequests';
import makeCreateRequests from './makeCreateRequests/makeCreateRequests';
import makeMergeRequests from './makeMergeRequests/makeMergeRequests';

/**
 * Makes all call queue requests.
 *
 * @param placeUuid uuid of place the sections belong to.
 * @param callQueue queue for api calls to make if changes are saved.
 * @returns Promise.
 */
const makeAllCallQueueRequests = async (placeUuid: string, callQueue: CallQueue) => {
    callQueue.changeLabelQueue.length && (await makeChangeLabelRequest(placeUuid, callQueue.changeLabelQueue));
    callQueue.mergeQueue.length && (await makeCreateThenMergeRequests(placeUuid, callQueue.mergeQueue));
    callQueue.createQueue.length && (await makeCreateRequests(placeUuid, callQueue.createQueue, callQueue.mergeQueue));
    callQueue.mergeQueue.length && (await makeMergeRequests(placeUuid, callQueue.mergeQueue));
};

export default makeAllCallQueueRequests;

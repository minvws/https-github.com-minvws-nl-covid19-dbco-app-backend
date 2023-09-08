import type { CallQueue } from '../sectionManagementTypes';

/**
 * Change section label in call queue.
 *
 * @param newLabel new label for section.
 * @param uuid section's unique identifier.
 * @param callQueue queue for api calls to make if changes are saved.
 * @returns updated call queue.
 */
const changeSectionLabelInQueue = (newLabel: string, uuid: string, callQueue: CallQueue): CallQueue => {
    const newCallQueue = { ...callQueue };

    const sectionInCreateQueue = newCallQueue.createQueue.find((section) => section.uuid === uuid);
    // If section already in queue for creation.
    if (sectionInCreateQueue) {
        // Change its label there.
        sectionInCreateQueue.label = newLabel;
    } else {
        // Else add to queue for label change.
        newCallQueue.changeLabelQueue.push({ label: newLabel, uuid });
    }
    return newCallQueue;
};

export default changeSectionLabelInQueue;

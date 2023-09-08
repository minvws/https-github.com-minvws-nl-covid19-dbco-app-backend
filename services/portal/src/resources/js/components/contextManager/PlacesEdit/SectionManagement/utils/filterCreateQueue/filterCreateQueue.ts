import type { CallQueue, CurrentSection } from '../../sectionManagementTypes';

/**
 * Remove create queue entries that will now be merged into another.
 * @param mergeSections sections to be merged into another.
 * @param createQueue queue for creation calls to make if changes are saved.
 * @returns filtered create queue.
 */
const filterCreateQueue = (
    mergeSections: CurrentSection[],
    createQueue: CallQueue['createQueue']
): CallQueue['createQueue'] => createQueue.filter((section) => !mergeSections.find((mS) => mS.uuid === section.uuid));

export default filterCreateQueue;

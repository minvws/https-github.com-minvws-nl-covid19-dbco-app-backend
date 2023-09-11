import type { CallQueue, CurrentSection } from '../../sectionManagementTypes';

/**
 * Remove label change queue entries that will now be merged into another.
 * @param mergeSections sections to be merged into another.
 * @param changeLabelQueue queue for label change calls to make if changes are saved.
 * @returns filtered label change queue.
 */
const filterLabelQueue = (
    mergeSections: CurrentSection[],
    changeLabelQueue: CallQueue['changeLabelQueue']
): CallQueue['changeLabelQueue'] =>
    changeLabelQueue.filter((section) => !mergeSections.find((mS) => mS.uuid === section.uuid));

export default filterLabelQueue;

import type { CallQueue, CurrentSection } from '../../sectionManagementTypes';

/**
 * Filter sections created in local state out of sections to be merged into another and return the remaining uuids.
 * @param mergeSections sections to be merged into another.
 * @param createQueue queue for section creation api calls to make if changes are saved.
 * @returns uuids of filtered sections to be merged into another.
 */
const cleanupMergeSections = (mergeSections: CurrentSection[], createQueue: CallQueue['createQueue']): string[] => {
    const filtered = mergeSections.filter((mS) => !createQueue.find((cQS) => cQS.uuid === mS.uuid));
    return filtered.map((fS) => fS.uuid);
};

export default cleanupMergeSections;

import type { CallQueue } from '../../sectionManagementTypes';

/**
 * Check if section to be merged into only exists in local state.
 * @param uuid uuid of section to be merged into.
 * @param createQueue queue for section creation api calls to make if changes are saved.
 * @returns if section to be merged into is new.
 */
const checkIfTargetNotYetCreated = (uuid: string, createQueue: CallQueue['createQueue']): boolean =>
    createQueue.some((section) => section.uuid == uuid);

export default checkIfTargetNotYetCreated;

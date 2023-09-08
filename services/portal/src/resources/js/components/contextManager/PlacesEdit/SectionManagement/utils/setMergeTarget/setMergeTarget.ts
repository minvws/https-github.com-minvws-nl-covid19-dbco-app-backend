import type { Section } from '@dbco/portal-api/section.dto';
import type { CurrentSection } from '../../sectionManagementTypes';

/**
 * If section to be merged into only exists in local state. Set target to Object including label for creation.
 * @param mainSection section to be merged into.
 * @param targetNotYetCreated if section to be merged into only exists in local state.
 * @returns merge target.
 */
const setMergeTarget = (mainSection: CurrentSection, targetNotYetCreated: boolean): string | Section => {
    if (!targetNotYetCreated) {
        return mainSection.uuid;
    }
    return {
        label: mainSection.label,
        uuid: mainSection.uuid,
        indexCount: 0,
    };
};

export default setMergeTarget;

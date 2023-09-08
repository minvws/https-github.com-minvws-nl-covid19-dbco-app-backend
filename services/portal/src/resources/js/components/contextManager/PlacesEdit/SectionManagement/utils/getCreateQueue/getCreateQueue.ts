import type { CallQueue, QueuedMerge } from '../../sectionManagementTypes';

/**
 * Gets sections to be created that aren't the target of a merge call.
 *
 * @param createQueue queue for creation api calls to make if changes are saved.
 * @param createThenMergeQueue merges in merge queue where target is new and needs to be created before merge.
 * @returns Array of labels for section creation.
 */
const getCreateQueue = (
    createQueue: CallQueue['createQueue'],
    createThenMergeQueue: QueuedMerge[]
): { label: string }[] => {
    const sectionsToCreate = createQueue.filter(
        (section) =>
            !createThenMergeQueue.find(
                (merge) => typeof merge.target !== 'string' && merge.target.uuid === section.uuid
            )
    );
    return sectionsToCreate.map((section) => ({ label: section.label }));
};

export default getCreateQueue;

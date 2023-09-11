import type { AssignmentResult } from '@dbco/portal-api/assignment';
import type { PlannerCaseListItem } from '@dbco/portal-api/caseList.dto';
import { ListFilterOptions } from '@dbco/portal-api/client/caseList.api';

/**
 * Determines if the plannercase should be removed from the list
 *
 * @param assignment Assignment being applied
 * @param filter Page currently active on the list
 * @param plannerCaseListItem plannerCase that might need removal
 * @returns if the plannerCase should be removed or not
 */
function mustBeRemoved(
    assignment: AssignmentResult,
    filter: ListFilterOptions,
    plannerCaseListItem: PlannerCaseListItem
): boolean {
    if (assignment.hasOwnProperty('assignedUserUuid')) {
        if (assignment.assignedUserUuid) {
            if (
                [ListFilterOptions.Unassigned, ListFilterOptions.Queued, ListFilterOptions.Completed].includes(filter)
            ) {
                return true;
            }
        } else {
            if (
                (filter === ListFilterOptions.Assigned &&
                    !plannerCaseListItem.assignedUser &&
                    !plannerCaseListItem.assignedCaseList) ||
                (filter === ListFilterOptions.Queued && plannerCaseListItem.assignedCaseList?.isQueue)
            ) {
                return true;
            }
        }
    } else if (assignment.hasOwnProperty('assignedCaseListUuid')) {
        return true;
    } else if (assignment.hasOwnProperty('assignedOrganisationUuid')) {
        if (assignment.assignedOrganisationUuid) {
            if (
                [
                    ListFilterOptions.Unassigned,
                    ListFilterOptions.Queued,
                    ListFilterOptions.Assigned,
                    ListFilterOptions.Completed,
                ].includes(filter)
            ) {
                return true;
            }
        } else {
            if (filter === ListFilterOptions.Outsourced || !plannerCaseListItem.organisation?.isCurrent) {
                return true;
            }
        }
    }

    return false;
}

/**
 * Apply the assignment to the item. This mutated the case
 *
 * @param assignment Assingment to apply
 * @param plannerCaseListItem Case to mutate
 */
function manipulateItem(assignment: AssignmentResult, plannerCaseListItem: PlannerCaseListItem): void {
    if (assignment.hasOwnProperty('assignedUserUuid')) {
        if (!assignment.assignedUserUuid) {
            plannerCaseListItem.assignedUser = null;
            return;
        }

        plannerCaseListItem.assignedUser = {
            uuid: assignment.assignedUserUuid,
            name: assignment.option.label ?? '',
            isCurrent: false,
        };

        if (plannerCaseListItem.assignedCaseList && plannerCaseListItem.assignedCaseList.isQueue) {
            plannerCaseListItem.assignedCaseList = null;
        }

        return;
    }

    if (assignment.hasOwnProperty('assignedCaseListUuid')) {
        if (!assignment.assignedCaseListUuid) {
            plannerCaseListItem.assignedCaseList = null;
            return;
        }

        plannerCaseListItem.assignedCaseList = {
            uuid: assignment.assignedCaseListUuid,
            name: assignment.option.label ?? '',
            isQueue: null,
        };

        if (assignment.option && 'isQueue' in assignment.option && assignment.option.isQueue) {
            plannerCaseListItem.assignedUser = null;
        }
        return;
    }

    if (assignment.hasOwnProperty('assignedOrganisationUuid')) {
        if (!assignment.assignedOrganisationUuid) {
            plannerCaseListItem.assignedOrganisation = null;
            return;
        }

        plannerCaseListItem.assignedOrganisation = {
            name: assignment.option.label ?? '',
            abbreviation: null,
            uuid: assignment.assignedOrganisationUuid,
            isCurrent: false,
        };
        return;
    }
}

/**
 * Apply assignment to the itemlist and Cases
 * This method will mutate the PlannerCases in the itemList
 *
 * @param itemList List to apply assignments on
 * @param assignment assignment to apply
 * @param filter filter that is on the list being rendered
 * @param selectedCase Case currently being edited in the modal
 * @returns Updated itemlist, with the assignment applied
 */
export default function applyAssignment(
    itemList: PlannerCaseListItem[],
    assignment: AssignmentResult,
    filter: ListFilterOptions,
    selectedCase?: PlannerCaseListItem
): PlannerCaseListItem[] {
    if (!assignment.cases) {
        return itemList;
    }

    // When the selected item is not in the list. Add it.
    // It should be deleted correctly further down, when it is added to the list but it actually shouldn't be added.
    if (selectedCase && !itemList.some((i) => i.uuid === selectedCase?.uuid)) {
        itemList.push(selectedCase);
    }

    // This finds the items when they are:
    // - selected individually
    // - bulk edited
    // - edited in the modal
    const listItemsToAssign = assignment.cases.map((uuid) => {
        const index = itemList.findIndex((i) => i.uuid == uuid);
        return { item: itemList[index], index };
    });

    listItemsToAssign.forEach(({ item }) => manipulateItem(assignment, item));

    const itemIndexesToRemove = listItemsToAssign
        .filter(({ item }) => mustBeRemoved(assignment, filter, item))
        .map(({ index }) => index);

    return itemList.filter((_, index) => !itemIndexesToRemove.includes(index));
}

import type { PlannerCaseListItem } from '@dbco/portal-api/caseList.dto';
import { PlannerView } from '@dbco/portal-api/caseList.dto';
import { PriorityV1 } from '@dbco/enum';
import type { AssignmentResult } from '@dbco/portal-api/assignment';
import { AssignmentOptionType } from '@dbco/portal-api/assignment';
import applyAssignment from './ApplyAssignment';
import { ListFilterOptions } from '@dbco/portal-api/client/caseList.api';

const emptyCase = {
    uuid: '',
    caseId: '',
    contactsCount: null,
    dateOfBirth: null,
    dateOfTest: null,
    statusIndexContactTracing: '',
    statusExplanation: '',
    createdAt: '',
    updatedAt: '',
    organisation: null,
    osirisNumber: null,
    assignedOrganisation: null,
    assignedCaseList: null,
    assignedUser: null,
    isApproved: null,
    isAssignable: true,
    isClosable: true,
    isDeletable: true,
    isEditable: true,
    isReopenable: true,
    canChangeOrganisation: true,
    label: null,
    plannerView: PlannerView.UNKNOWN,
    wasOutsourced: false,
    wasOutsourcedToOrganisation: null,
    lastAssignedUserName: null,
    caseLabels: [],
    priority: PriorityV1.VALUE_0,
};

describe('ApplyAssignment: apply assignment to the list of cases', () => {
    it('should remove item when User is unassigned', () => {
        const assigmentList = ListFilterOptions.Assigned;
        const caseToUnasign = '3793a1a2-fed7-4ed1-b6c7-8e9352b51792';
        const assignmentToUnasignCase: AssignmentResult = {
            cases: [caseToUnasign],
            assignedUserUuid: undefined,
            option: {
                type: AssignmentOptionType.SEPARATOR,
            },
        };

        const list: PlannerCaseListItem[] = [
            {
                ...emptyCase,
                uuid: caseToUnasign,
            },
        ];

        expect(applyAssignment(list, assignmentToUnasignCase, assigmentList).length).toBe(0);
    });

    it('should move case from unassigend to queue, when case is selected', () => {
        const unassignedList = ListFilterOptions.Unassigned;
        const caseToMoveToQueue = {
            ...emptyCase,
            uuid: '3793a1a2-fed7-4ed1-b6c7-8e9352b51792',
        };

        const list: PlannerCaseListItem[] = [
            {
                ...emptyCase,
            },
            caseToMoveToQueue,
        ];

        const assignmentToQueueCase: AssignmentResult = {
            cases: [caseToMoveToQueue.uuid],
            assignedCaseListUuid: '2ce04adb-c102-49a7-8a85-a98e506f4fbb',
            option: {
                type: AssignmentOptionType.SEPARATOR,
            },
        };

        expect(applyAssignment(list, assignmentToQueueCase, unassignedList, caseToMoveToQueue).length).toBe(1);
    });
});

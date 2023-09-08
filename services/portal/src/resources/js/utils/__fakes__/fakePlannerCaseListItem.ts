import type { PlannerCaseListItem } from '@dbco/portal-api/caseList.dto';
import { PlannerView } from '@dbco/portal-api/caseList.dto';
import { fakerjs } from '@/utils/test';
import { PriorityV1 } from '@dbco/enum';
import { createFakeDataGenerator } from './createFakeDataGenerator';

export const fakePlannerCaseListItem = createFakeDataGenerator<PlannerCaseListItem>(() => ({
    assignedCaseList: null,
    assignedOrganisation: null,
    assignedUser: null,
    bcoStatus: null,
    caseId: fakerjs.string.uuid(),
    caseLabels: [],
    contactsCount: null,
    createdAt: fakerjs.date.past().toString(),
    dateOfBirth: null,
    dateOfTest: null,
    isApproved: null,
    isAssignable: false,
    isClosable: false,
    isDeletable: false,
    isEditable: false,
    isReopenable: false,
    canChangeOrganisation: false,
    label: null,
    organisation: {
        abbreviation: fakerjs.string.alpha(),
        isCurrent: true,
        name: fakerjs.company.name(),
        uuid: fakerjs.string.uuid(),
    },
    osirisNumber: fakerjs.number.int(),
    plannerView: PlannerView.INTAKELIST,
    priority: PriorityV1.VALUE_0,
    statusExplanation: fakerjs.lorem.paragraph(),
    statusIndexContactTracing: '',
    testResults: [],
    updatedAt: fakerjs.date.past().toString(),
    uuid: fakerjs.string.uuid(),
    wasOutsourced: false,
    wasOutsourcedToOrganisation: null,
    lastAssignedUserName: null,
}));

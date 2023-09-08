import type { AssignmentOption } from '@dbco/portal-api/assignment';
import { AssignmentOptionType, AssignmentType } from '@dbco/portal-api/assignment';
import { fakerjs } from '@/utils/test';

const fakeAssignmentOptions: AssignmentOption[] = [
    {
        type: AssignmentOptionType.OPTION,
        label: fakerjs.lorem.words(),
        isSelected: true,
        isEnabled: false,
        assignment: {
            assignedUserUuid: undefined,
            staleSince: fakerjs.date.past().toString(),
        },
    },
    {
        type: AssignmentOptionType.OPTION,
        label: fakerjs.lorem.words(),
        isSelected: false,
        isEnabled: true,
        isQueue: true,
        assignmentType: AssignmentType.CASELIST,
        assignment: {
            assignedCaseListUuid: fakerjs.string.uuid(),
            staleSince: fakerjs.date.past().toString(),
        },
    },
    {
        type: AssignmentOptionType.MENU,
        label: fakerjs.lorem.words(),
        options: [
            {
                type: AssignmentOptionType.OPTION,
                label: fakerjs.lorem.words(),
                isSelected: true,
                isEnabled: false,
                isQueue: undefined,
                assignmentType: AssignmentType.CASELIST,
                assignment: {
                    assignedCaseListUuid: undefined,
                    staleSince: fakerjs.date.past().toString(),
                },
            },
            {
                type: AssignmentOptionType.OPTION,
                label: fakerjs.lorem.words(),
                isSelected: false,
                isEnabled: true,
                isQueue: false,
                assignmentType: AssignmentType.CASELIST,
                assignment: {
                    caseListUuid: fakerjs.string.uuid(),
                    staleSince: fakerjs.date.past().toString(),
                },
            },
        ],
        isEnabled: true,
    },
    {
        type: AssignmentOptionType.MENU,
        label: fakerjs.lorem.words(),
        options: [
            {
                type: AssignmentOptionType.OPTION,
                label: fakerjs.lorem.words(),
                isSelected: false,
                isEnabled: true,
                assignmentType: AssignmentType.ORGANISATION,
                assignment: {
                    assignedOrganisationUuid: fakerjs.string.uuid(),
                    staleSince: fakerjs.date.past().toString(),
                },
            },
            {
                type: AssignmentOptionType.OPTION,
                label: fakerjs.lorem.words(),
                isSelected: false,
                isEnabled: true,
                assignmentType: AssignmentType.ORGANISATION,
                assignment: {
                    assignedOrganisationUuid: fakerjs.string.uuid(),
                    staleSince: fakerjs.date.past().toString(),
                },
            },
        ],
    },
    {
        type: AssignmentOptionType.SEPARATOR,
    },
    {
        type: AssignmentOptionType.OPTION,
        label: fakerjs.lorem.words(),
        isSelected: false,
        isEnabled: true,
        assignmentType: AssignmentType.USER,
        assignment: {
            assignedUserUuid: fakerjs.string.uuid(),
            staleSince: fakerjs.date.past().toString(),
        },
    },
    {
        type: AssignmentOptionType.OPTION,
        label: fakerjs.lorem.words(),
        isSelected: false,
        isEnabled: true,
        assignmentType: AssignmentType.USER,
        assignment: {
            assignedUserUuid: fakerjs.string.uuid(),
            staleSince: fakerjs.date.past().toString(),
        },
    },
];

export default fakeAssignmentOptions;

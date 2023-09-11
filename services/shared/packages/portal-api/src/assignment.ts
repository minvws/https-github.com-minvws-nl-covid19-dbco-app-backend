export type AssignmentOption = {
    type: AssignmentOptionType;
    assignment?: Assignment;
    assignmentType?: AssignmentType;
    isEnabled?: boolean;
    isQueue?: boolean;
    isSelected?: boolean;
    label?: string;
    options?: AssignmentOption[];
};

export type AssignmentResult = {
    cases?: string[];
    caseListUuid?: string;
    assignedUserUuid?: string;
    assignedCaseListUuid?: string;
    assignedOrganisationUuid?: string;
    option: AssignmentOption;
};

export enum AssignmentOptionType {
    MENU = 'menu',
    OPTION = 'option',
    ORGANISATION = 'organisation',
    SEPARATOR = 'separator',
    USER = 'user',
}

export enum AssignmentType {
    CASELIST = 'caseList',
    ORGANISATION = 'organisation',
    USER = 'user',
}

export type Assignment = {
    cases?: string[];
    caseListUuid?: string;
    assignedUserUuid?: string;
    assignedCaseListUuid?: string;
    assignedOrganisationUuid?: string;
    staleSince: string;
};

/**
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit Permission.json!
 */

/**
 * Permission for role values
 * All values are escaped with quotes and prefixed with VALUES_ to prevent generated errors
 * caused by unsupported characters or numeric values
 */
export enum PermissionV1 {
  'VALUE_caseListUserCases' = 'caseListUserCases',
  'VALUE_caseListPlannerCases' = 'caseListPlannerCases',
  'VALUE_caseCreate' = 'caseCreate',
  'VALUE_caseUserEdit' = 'caseUserEdit',
  'VALUE_caseEditContactStatus' = 'caseEditContactStatus',
  'VALUE_caseApprove' = 'caseApprove',
  'VALUE_caseArchiveDirectly' = 'caseArchiveDirectly',
  'VALUE_casePlannerEdit' = 'casePlannerEdit',
  'VALUE_casePlannerDelete' = 'casePlannerDelete',
  'VALUE_caseExport' = 'caseExport',
  'VALUE_caseCanBeAssignedToDraft' = 'caseCanBeAssignedToDraft',
  'VALUE_caseCanBeAssignedToOpen' = 'caseCanBeAssignedToOpen',
  'VALUE_caseCanBeAssignedToCompleted' = 'caseCanBeAssignedToCompleted',
  'VALUE_caseCanBeAssignedToArchived' = 'caseCanBeAssignedToArchived',
  'VALUE_caseCanBeAssignedToUnknown' = 'caseCanBeAssignedToUnknown',
  'VALUE_caseListAccessRequests' = 'caseListAccessRequests',
  'VALUE_caseViewAccessRequest' = 'caseViewAccessRequest',
  'VALUE_caseComplianceDelete' = 'caseComplianceDelete',
  'VALUE_caseRestore' = 'caseRestore',
  'VALUE_caseReopen' = 'caseReopen',
  'VALUE_caseBsnLookup' = 'caseBsnLookup',
  'VALUE_caseAddressLookup' = 'caseAddressLookup',
  'VALUE_caseSearch' = 'caseSearch',
  'VALUE_caseCanPickUpNew' = 'caseCanPickUpNew',
  'VALUE_taskCreate' = 'taskCreate',
  'VALUE_taskEdit' = 'taskEdit',
  'VALUE_taskUserDelete' = 'taskUserDelete',
  'VALUE_taskViewAccessRequest' = 'taskViewAccessRequest',
  'VALUE_taskComplianceDelete' = 'taskComplianceDelete',
  'VALUE_taskRestore' = 'taskRestore',
  'VALUE_contextCreate' = 'contextCreate',
  'VALUE_contextEdit' = 'contextEdit',
  'VALUE_contextDelete' = 'contextDelete',
  'VALUE_contextList' = 'contextList',
  'VALUE_contextMerge' = 'contextMerge',
  'VALUE_contextSearch' = 'contextSearch',
  'VALUE_contextLink' = 'contextLink',
  'VALUE_caseArchive' = 'caseArchive',
  'VALUE_placeCreate' = 'placeCreate',
  'VALUE_placeEdit' = 'placeEdit',
  'VALUE_placeEditOwnedByOrganisation' = 'placeEditOwnedByOrganisation',
  'VALUE_placeEditNotOwnedByOrganisation' = 'placeEditNotOwnedByOrganisation',
  'VALUE_placeDelete' = 'placeDelete',
  'VALUE_placeList' = 'placeList',
  'VALUE_placeMerge' = 'placeMerge',
  'VALUE_placeSearch' = 'placeSearch',
  'VALUE_placeCaseList' = 'placeCaseList',
  'VALUE_placeSectionList' = 'placeSectionList',
  'VALUE_placeSectionCreate' = 'placeSectionCreate',
  'VALUE_placeSectionCreateOwnedByOrganisation' = 'placeSectionCreateOwnedByOrganisation',
  'VALUE_placeSectionCreateNotOwnedByOrganisation' = 'placeSectionCreateNotOwnedByOrganisation',
  'VALUE_placeSectionEdit' = 'placeSectionEdit',
  'VALUE_placeSectionEditOwnedByOrganisation' = 'placeSectionEditOwnedByOrganisation',
  'VALUE_placeSectionEditNotOwnedByOrganisation' = 'placeSectionEditNotOwnedByOrganisation',
  'VALUE_placeSectionDelete' = 'placeSectionDelete',
  'VALUE_placeSectionMerge' = 'placeSectionMerge',
  'VALUE_intakeList' = 'intakeList',
  'VALUE_placeVerify' = 'placeVerify',
  'VALUE_organisationList' = 'organisationList',
  'VALUE_organisationUpdate' = 'organisationUpdate',
  'VALUE_caseViewPlannerTimeline' = 'caseViewPlannerTimeline',
  'VALUE_caseViewSupervisionRegional' = 'caseViewSupervisionRegional',
  'VALUE_caseViewSupervisionNationwide' = 'caseViewSupervisionNationwide',
  'VALUE_caseViewUserTimeline' = 'caseViewUserTimeline',
  'VALUE_caseBcoPhaseEdit' = 'caseBcoPhaseEdit',
  'VALUE_expertQuestionMedicalSupervisor' = 'expertQuestionMedicalSupervisor',
  'VALUE_expertQuestionConversationCoach' = 'expertQuestionConversationCoach',
  'VALUE_expertQuestionList' = 'expertQuestionList',
  'VALUE_expertQuestionAssign' = 'expertQuestionAssign',
  'VALUE_expertQuestionAnswer' = 'expertQuestionAnswer',
  'VALUE_callcenterView' = 'callcenterView',
  'VALUE_caseCreateCallToAction' = 'caseCreateCallToAction',
  'VALUE_caseViewCallToAction' = 'caseViewCallToAction',
  'VALUE_choreList' = 'choreList',
  'VALUE_callToAction' = 'callToAction',
  'VALUE_datacatalog' = 'datacatalog',
  'VALUE_caseEditViaSearchCase' = 'caseEditViaSearchCase',
  'VALUE_caseEditCallcenterExpert' = 'caseEditCallcenterExpert',
  'VALUE_caseMetricsList' = 'caseMetricsList',
  'VALUE_addCasesByChore' = 'addCasesByChore',
  'VALUE_caseCreateNote' = 'caseCreateNote',
  'VALUE_createCallToActionViaSearchCase' = 'createCallToActionViaSearchCase',
  'VALUE_caseViewOsirisHistory' = 'caseViewOsirisHistory',
  'VALUE_adminView' = 'adminView',
  'VALUE_adminPolicyAdviceModule' = 'adminPolicyAdviceModule',
}

/**
 * Permission for role options to be used in the forms
 */
export const permissionV1Options = [
    {
        "label": "caseListUserCases",
        "value": PermissionV1.VALUE_caseListUserCases,
        "entity": "case"
    },
    {
        "label": "caseListPlannerCases",
        "value": PermissionV1.VALUE_caseListPlannerCases,
        "entity": "case"
    },
    {
        "label": "caseCreate",
        "value": PermissionV1.VALUE_caseCreate,
        "entity": "case"
    },
    {
        "label": "caseUserEdit",
        "value": PermissionV1.VALUE_caseUserEdit,
        "entity": "case"
    },
    {
        "label": "caseEditContactStatus",
        "value": PermissionV1.VALUE_caseEditContactStatus,
        "entity": "case"
    },
    {
        "label": "caseApprove",
        "value": PermissionV1.VALUE_caseApprove,
        "entity": "case"
    },
    {
        "label": "caseArchiveDirectly",
        "value": PermissionV1.VALUE_caseArchiveDirectly,
        "entity": "case"
    },
    {
        "label": "casePlannerEdit",
        "value": PermissionV1.VALUE_casePlannerEdit,
        "entity": "case"
    },
    {
        "label": "casePlannerDelete",
        "value": PermissionV1.VALUE_casePlannerDelete,
        "entity": "case"
    },
    {
        "label": "caseExport",
        "value": PermissionV1.VALUE_caseExport,
        "entity": "case"
    },
    {
        "label": "caseCanBeAssignedToDraft",
        "value": PermissionV1.VALUE_caseCanBeAssignedToDraft,
        "entity": "case"
    },
    {
        "label": "caseCanBeAssignedToOpen",
        "value": PermissionV1.VALUE_caseCanBeAssignedToOpen,
        "entity": "case"
    },
    {
        "label": "caseCanBeAssignedToCompleted",
        "value": PermissionV1.VALUE_caseCanBeAssignedToCompleted,
        "entity": "case"
    },
    {
        "label": "caseCanBeAssignedToArchived",
        "value": PermissionV1.VALUE_caseCanBeAssignedToArchived,
        "entity": "case"
    },
    {
        "label": "caseCanBeAssignedToUnknown",
        "value": PermissionV1.VALUE_caseCanBeAssignedToUnknown,
        "entity": "case"
    },
    {
        "label": "caseListAccessRequests",
        "value": PermissionV1.VALUE_caseListAccessRequests,
        "entity": "case"
    },
    {
        "label": "caseViewAccessRequest",
        "value": PermissionV1.VALUE_caseViewAccessRequest,
        "entity": "case"
    },
    {
        "label": "caseComplianceDelete",
        "value": PermissionV1.VALUE_caseComplianceDelete,
        "entity": "case"
    },
    {
        "label": "caseRestore",
        "value": PermissionV1.VALUE_caseRestore,
        "entity": "case"
    },
    {
        "label": "caseReopen",
        "value": PermissionV1.VALUE_caseReopen,
        "entity": "case"
    },
    {
        "label": "caseBsnLookup",
        "value": PermissionV1.VALUE_caseBsnLookup,
        "entity": "case"
    },
    {
        "label": "caseAddressLookup",
        "value": PermissionV1.VALUE_caseAddressLookup,
        "entity": "case"
    },
    {
        "label": "caseSearch",
        "value": PermissionV1.VALUE_caseSearch,
        "entity": "case"
    },
    {
        "label": "caseCanPickUpNew",
        "value": PermissionV1.VALUE_caseCanPickUpNew,
        "entity": "case"
    },
    {
        "label": "taskCreate",
        "value": PermissionV1.VALUE_taskCreate,
        "entity": "task"
    },
    {
        "label": "taskEdit",
        "value": PermissionV1.VALUE_taskEdit,
        "entity": "task"
    },
    {
        "label": "taskUserDelete",
        "value": PermissionV1.VALUE_taskUserDelete,
        "entity": "task"
    },
    {
        "label": "taskViewAccessRequest",
        "value": PermissionV1.VALUE_taskViewAccessRequest,
        "entity": "task"
    },
    {
        "label": "taskComplianceDelete",
        "value": PermissionV1.VALUE_taskComplianceDelete,
        "entity": "task"
    },
    {
        "label": "taskRestore",
        "value": PermissionV1.VALUE_taskRestore,
        "entity": "task"
    },
    {
        "label": "contextCreate",
        "value": PermissionV1.VALUE_contextCreate,
        "entity": "context"
    },
    {
        "label": "contextEdit",
        "value": PermissionV1.VALUE_contextEdit,
        "entity": "context"
    },
    {
        "label": "contextDelete",
        "value": PermissionV1.VALUE_contextDelete,
        "entity": "context"
    },
    {
        "label": "contextList",
        "value": PermissionV1.VALUE_contextList,
        "entity": "context"
    },
    {
        "label": "contextMerge",
        "value": PermissionV1.VALUE_contextMerge,
        "entity": "context"
    },
    {
        "label": "contextSearch",
        "value": PermissionV1.VALUE_contextSearch,
        "entity": "context"
    },
    {
        "label": "contextLink",
        "value": PermissionV1.VALUE_contextLink,
        "entity": "context"
    },
    {
        "label": "caseArchive",
        "value": PermissionV1.VALUE_caseArchive,
        "entity": "case"
    },
    {
        "label": "placeCreate",
        "value": PermissionV1.VALUE_placeCreate,
        "entity": "place"
    },
    {
        "label": "placeEdit",
        "value": PermissionV1.VALUE_placeEdit,
        "entity": "place"
    },
    {
        "label": "placeEditOwnedByOrganisation",
        "value": PermissionV1.VALUE_placeEditOwnedByOrganisation,
        "entity": "place"
    },
    {
        "label": "placeEditNotOwnedByOrganisation",
        "value": PermissionV1.VALUE_placeEditNotOwnedByOrganisation,
        "entity": "place"
    },
    {
        "label": "placeDelete",
        "value": PermissionV1.VALUE_placeDelete,
        "entity": "context"
    },
    {
        "label": "placeList",
        "value": PermissionV1.VALUE_placeList,
        "entity": "place"
    },
    {
        "label": "placeMerge",
        "value": PermissionV1.VALUE_placeMerge,
        "entity": "place"
    },
    {
        "label": "placeSearch",
        "value": PermissionV1.VALUE_placeSearch,
        "entity": "place"
    },
    {
        "label": "placeCaseList",
        "value": PermissionV1.VALUE_placeCaseList,
        "entity": "place"
    },
    {
        "label": "placeSectionList",
        "value": PermissionV1.VALUE_placeSectionList,
        "entity": "place"
    },
    {
        "label": "placeSectionCreate",
        "value": PermissionV1.VALUE_placeSectionCreate,
        "entity": "place"
    },
    {
        "label": "placeSectionCreateOwnedByOrganisation",
        "value": PermissionV1.VALUE_placeSectionCreateOwnedByOrganisation,
        "entity": "place"
    },
    {
        "label": "placeSectionCreateNotOwnedByOrganisation",
        "value": PermissionV1.VALUE_placeSectionCreateNotOwnedByOrganisation,
        "entity": "place"
    },
    {
        "label": "placeSectionEdit",
        "value": PermissionV1.VALUE_placeSectionEdit,
        "entity": "place"
    },
    {
        "label": "placeSectionEditOwnedByOrganisation",
        "value": PermissionV1.VALUE_placeSectionEditOwnedByOrganisation,
        "entity": "place"
    },
    {
        "label": "placeSectionEditNotOwnedByOrganisation",
        "value": PermissionV1.VALUE_placeSectionEditNotOwnedByOrganisation,
        "entity": "place"
    },
    {
        "label": "placeSectionDelete",
        "value": PermissionV1.VALUE_placeSectionDelete,
        "entity": "place"
    },
    {
        "label": "placeSectionMerge",
        "value": PermissionV1.VALUE_placeSectionMerge,
        "entity": "place"
    },
    {
        "label": "intakeList",
        "value": PermissionV1.VALUE_intakeList,
        "entity": "intake"
    },
    {
        "label": "placeVerify",
        "value": PermissionV1.VALUE_placeVerify,
        "entity": "place"
    },
    {
        "label": "organisationList",
        "value": PermissionV1.VALUE_organisationList,
        "entity": "organisation"
    },
    {
        "label": "organisationUpdate",
        "value": PermissionV1.VALUE_organisationUpdate,
        "entity": "organisation"
    },
    {
        "label": "caseViewPlannerTimeline",
        "value": PermissionV1.VALUE_caseViewPlannerTimeline,
        "entity": "case"
    },
    {
        "label": "caseViewSupervisionRegional",
        "value": PermissionV1.VALUE_caseViewSupervisionRegional,
        "entity": "case"
    },
    {
        "label": "caseViewSupervisionNationwide",
        "value": PermissionV1.VALUE_caseViewSupervisionNationwide,
        "entity": "case"
    },
    {
        "label": "caseViewUserTimeline",
        "value": PermissionV1.VALUE_caseViewUserTimeline,
        "entity": "case"
    },
    {
        "label": "caseBcoPhaseEdit",
        "value": PermissionV1.VALUE_caseBcoPhaseEdit,
        "entity": "case"
    },
    {
        "label": "expertQuestionMedicalSupervisor",
        "value": PermissionV1.VALUE_expertQuestionMedicalSupervisor,
        "entity": null
    },
    {
        "label": "expertQuestionConversationCoach",
        "value": PermissionV1.VALUE_expertQuestionConversationCoach,
        "entity": null
    },
    {
        "label": "expertQuestionList",
        "value": PermissionV1.VALUE_expertQuestionList,
        "entity": null
    },
    {
        "label": "expertQuestionAssign",
        "value": PermissionV1.VALUE_expertQuestionAssign,
        "entity": null
    },
    {
        "label": "expertQuestionAnswer",
        "value": PermissionV1.VALUE_expertQuestionAnswer,
        "entity": null
    },
    {
        "label": "callcenterView",
        "value": PermissionV1.VALUE_callcenterView,
        "entity": null
    },
    {
        "label": "caseCreateCallToAction",
        "value": PermissionV1.VALUE_caseCreateCallToAction,
        "entity": null
    },
    {
        "label": "caseViewCallToAction",
        "value": PermissionV1.VALUE_caseViewCallToAction,
        "entity": null
    },
    {
        "label": "choreList",
        "value": PermissionV1.VALUE_choreList,
        "entity": null
    },
    {
        "label": "callToAction",
        "value": PermissionV1.VALUE_callToAction,
        "entity": null
    },
    {
        "label": "datacatalog",
        "value": PermissionV1.VALUE_datacatalog,
        "entity": null
    },
    {
        "label": "caseEditViaSearchCase",
        "value": PermissionV1.VALUE_caseEditViaSearchCase,
        "entity": null
    },
    {
        "label": "caseEditCallcenterExpert",
        "value": PermissionV1.VALUE_caseEditCallcenterExpert,
        "entity": null
    },
    {
        "label": "caseMetricsList",
        "value": PermissionV1.VALUE_caseMetricsList,
        "entity": null
    },
    {
        "label": "addCasesByChore",
        "value": PermissionV1.VALUE_addCasesByChore,
        "entity": null
    },
    {
        "label": "caseCreateNote",
        "value": PermissionV1.VALUE_caseCreateNote,
        "entity": "case"
    },
    {
        "label": "createCallToActionViaSearchCase",
        "value": PermissionV1.VALUE_createCallToActionViaSearchCase,
        "entity": null
    },
    {
        "label": "caseViewOsirisHistory",
        "value": PermissionV1.VALUE_caseViewOsirisHistory,
        "entity": null
    },
    {
        "label": "adminView",
        "value": PermissionV1.VALUE_adminView,
        "entity": null
    },
    {
        "label": "adminPolicyAdviceModule",
        "value": PermissionV1.VALUE_adminPolicyAdviceModule,
        "entity": null
    }
];

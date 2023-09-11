<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 * Permission for role
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit Permission.json!
 *
 * @codeCoverageIgnore
 *
 * @method static Permission caseListUserCases() caseListUserCases() caseListUserCases
 * @method static Permission caseListPlannerCases() caseListPlannerCases() caseListPlannerCases
 * @method static Permission caseCreate() caseCreate() caseCreate
 * @method static Permission caseUserEdit() caseUserEdit() caseUserEdit
 * @method static Permission caseEditContactStatus() caseEditContactStatus() caseEditContactStatus
 * @method static Permission caseApprove() caseApprove() caseApprove
 * @method static Permission caseArchiveDirectly() caseArchiveDirectly() caseArchiveDirectly
 * @method static Permission casePlannerEdit() casePlannerEdit() casePlannerEdit
 * @method static Permission casePlannerDelete() casePlannerDelete() casePlannerDelete
 * @method static Permission caseExport() caseExport() caseExport
 * @method static Permission caseCanBeAssignedToDraft() caseCanBeAssignedToDraft() caseCanBeAssignedToDraft
 * @method static Permission caseCanBeAssignedToOpen() caseCanBeAssignedToOpen() caseCanBeAssignedToOpen
 * @method static Permission caseCanBeAssignedToCompleted() caseCanBeAssignedToCompleted() caseCanBeAssignedToCompleted
 * @method static Permission caseCanBeAssignedToArchived() caseCanBeAssignedToArchived() caseCanBeAssignedToArchived
 * @method static Permission caseCanBeAssignedToUnknown() caseCanBeAssignedToUnknown() caseCanBeAssignedToUnknown
 * @method static Permission caseListAccessRequests() caseListAccessRequests() caseListAccessRequests
 * @method static Permission caseViewAccessRequest() caseViewAccessRequest() caseViewAccessRequest
 * @method static Permission caseComplianceDelete() caseComplianceDelete() caseComplianceDelete
 * @method static Permission caseRestore() caseRestore() caseRestore
 * @method static Permission caseReopen() caseReopen() caseReopen
 * @method static Permission caseBsnLookup() caseBsnLookup() caseBsnLookup
 * @method static Permission caseAddressLookup() caseAddressLookup() caseAddressLookup
 * @method static Permission caseSearch() caseSearch() caseSearch
 * @method static Permission caseCanPickUpNew() caseCanPickUpNew() caseCanPickUpNew
 * @method static Permission taskCreate() taskCreate() taskCreate
 * @method static Permission taskEdit() taskEdit() taskEdit
 * @method static Permission taskUserDelete() taskUserDelete() taskUserDelete
 * @method static Permission taskViewAccessRequest() taskViewAccessRequest() taskViewAccessRequest
 * @method static Permission taskComplianceDelete() taskComplianceDelete() taskComplianceDelete
 * @method static Permission taskRestore() taskRestore() taskRestore
 * @method static Permission contextCreate() contextCreate() contextCreate
 * @method static Permission contextEdit() contextEdit() contextEdit
 * @method static Permission contextDelete() contextDelete() contextDelete
 * @method static Permission contextList() contextList() contextList
 * @method static Permission contextMerge() contextMerge() contextMerge
 * @method static Permission contextSearch() contextSearch() contextSearch
 * @method static Permission contextLink() contextLink() contextLink
 * @method static Permission caseArchive() caseArchive() caseArchive
 * @method static Permission placeCreate() placeCreate() placeCreate
 * @method static Permission placeEdit() placeEdit() placeEdit
 * @method static Permission placeEditOwnedByOrganisation() placeEditOwnedByOrganisation() placeEditOwnedByOrganisation
 * @method static Permission placeEditNotOwnedByOrganisation() placeEditNotOwnedByOrganisation() placeEditNotOwnedByOrganisation
 * @method static Permission placeDelete() placeDelete() placeDelete
 * @method static Permission placeList() placeList() placeList
 * @method static Permission placeMerge() placeMerge() placeMerge
 * @method static Permission placeSearch() placeSearch() placeSearch
 * @method static Permission placeCaseList() placeCaseList() placeCaseList
 * @method static Permission placeSectionList() placeSectionList() placeSectionList
 * @method static Permission placeSectionCreate() placeSectionCreate() placeSectionCreate
 * @method static Permission placeSectionCreateOwnedByOrganisation() placeSectionCreateOwnedByOrganisation() placeSectionCreateOwnedByOrganisation
 * @method static Permission placeSectionCreateNotOwnedByOrganisation() placeSectionCreateNotOwnedByOrganisation() placeSectionCreateNotOwnedByOrganisation
 * @method static Permission placeSectionEdit() placeSectionEdit() placeSectionEdit
 * @method static Permission placeSectionEditOwnedByOrganisation() placeSectionEditOwnedByOrganisation() placeSectionEditOwnedByOrganisation
 * @method static Permission placeSectionEditNotOwnedByOrganisation() placeSectionEditNotOwnedByOrganisation() placeSectionEditNotOwnedByOrganisation
 * @method static Permission placeSectionDelete() placeSectionDelete() placeSectionDelete
 * @method static Permission placeSectionMerge() placeSectionMerge() placeSectionMerge
 * @method static Permission intakeList() intakeList() intakeList
 * @method static Permission placeVerify() placeVerify() placeVerify
 * @method static Permission organisationList() organisationList() organisationList
 * @method static Permission organisationUpdate() organisationUpdate() organisationUpdate
 * @method static Permission caseViewPlannerTimeline() caseViewPlannerTimeline() caseViewPlannerTimeline
 * @method static Permission caseViewSupervisionRegional() caseViewSupervisionRegional() caseViewSupervisionRegional
 * @method static Permission caseViewSupervisionNationwide() caseViewSupervisionNationwide() caseViewSupervisionNationwide
 * @method static Permission caseViewUserTimeline() caseViewUserTimeline() caseViewUserTimeline
 * @method static Permission caseBcoPhaseEdit() caseBcoPhaseEdit() caseBcoPhaseEdit
 * @method static Permission expertQuestionMedicalSupervisor() expertQuestionMedicalSupervisor() expertQuestionMedicalSupervisor
 * @method static Permission expertQuestionConversationCoach() expertQuestionConversationCoach() expertQuestionConversationCoach
 * @method static Permission expertQuestionList() expertQuestionList() expertQuestionList
 * @method static Permission expertQuestionAssign() expertQuestionAssign() expertQuestionAssign
 * @method static Permission expertQuestionAnswer() expertQuestionAnswer() expertQuestionAnswer
 * @method static Permission callcenterView() callcenterView() callcenterView
 * @method static Permission caseCreateCallToAction() caseCreateCallToAction() caseCreateCallToAction
 * @method static Permission caseViewCallToAction() caseViewCallToAction() caseViewCallToAction
 * @method static Permission choreList() choreList() choreList
 * @method static Permission callToAction() callToAction() callToAction
 * @method static Permission datacatalog() datacatalog() datacatalog
 * @method static Permission caseEditViaSearchCase() caseEditViaSearchCase() caseEditViaSearchCase
 * @method static Permission caseEditCallcenterExpert() caseEditCallcenterExpert() caseEditCallcenterExpert
 * @method static Permission caseMetricsList() caseMetricsList() caseMetricsList
 * @method static Permission addCasesByChore() addCasesByChore() addCasesByChore
 * @method static Permission caseCreateNote() caseCreateNote() caseCreateNote
 * @method static Permission createCallToActionViaSearchCase() createCallToActionViaSearchCase() createCallToActionViaSearchCase
 * @method static Permission caseViewOsirisHistory() caseViewOsirisHistory() caseViewOsirisHistory
 * @method static Permission adminView() adminView() adminView
 * @method static Permission adminPolicyAdviceModule() adminPolicyAdviceModule() adminPolicyAdviceModule

 * @property-read string $value
 * @property-read PermissionEntity $entity Entity for permission
*/
final class Permission extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'Permission',
           'tsConst' => 'permission',
           'description' => 'Permission for role',
           'properties' =>
          (object) array(
             'entity' =>
            (object) array(
               'type' => 'PermissionEntity',
               'description' => 'Entity for permission',
               'scope' => 'shared',
               'phpType' => 'PermissionEntity',
            ),
          ),
           'items' =>
          array (
            0 =>
            (object) array(
               'label' => 'caseListUserCases',
               'value' => 'caseListUserCases',
               'entity' => 'case',
               'name' => 'caseListUserCases',
            ),
            1 =>
            (object) array(
               'label' => 'caseListPlannerCases',
               'value' => 'caseListPlannerCases',
               'entity' => 'case',
               'name' => 'caseListPlannerCases',
            ),
            2 =>
            (object) array(
               'label' => 'caseCreate',
               'value' => 'caseCreate',
               'entity' => 'case',
               'name' => 'caseCreate',
            ),
            3 =>
            (object) array(
               'label' => 'caseUserEdit',
               'value' => 'caseUserEdit',
               'entity' => 'case',
               'name' => 'caseUserEdit',
            ),
            4 =>
            (object) array(
               'label' => 'caseEditContactStatus',
               'value' => 'caseEditContactStatus',
               'entity' => 'case',
               'name' => 'caseEditContactStatus',
            ),
            5 =>
            (object) array(
               'label' => 'caseApprove',
               'value' => 'caseApprove',
               'entity' => 'case',
               'name' => 'caseApprove',
            ),
            6 =>
            (object) array(
               'label' => 'caseArchiveDirectly',
               'value' => 'caseArchiveDirectly',
               'entity' => 'case',
               'name' => 'caseArchiveDirectly',
            ),
            7 =>
            (object) array(
               'label' => 'casePlannerEdit',
               'value' => 'casePlannerEdit',
               'entity' => 'case',
               'name' => 'casePlannerEdit',
            ),
            8 =>
            (object) array(
               'label' => 'casePlannerDelete',
               'value' => 'casePlannerDelete',
               'entity' => 'case',
               'name' => 'casePlannerDelete',
            ),
            9 =>
            (object) array(
               'label' => 'caseExport',
               'value' => 'caseExport',
               'entity' => 'case',
               'name' => 'caseExport',
            ),
            10 =>
            (object) array(
               'label' => 'caseCanBeAssignedToDraft',
               'value' => 'caseCanBeAssignedToDraft',
               'entity' => 'case',
               'name' => 'caseCanBeAssignedToDraft',
            ),
            11 =>
            (object) array(
               'label' => 'caseCanBeAssignedToOpen',
               'value' => 'caseCanBeAssignedToOpen',
               'entity' => 'case',
               'name' => 'caseCanBeAssignedToOpen',
            ),
            12 =>
            (object) array(
               'label' => 'caseCanBeAssignedToCompleted',
               'value' => 'caseCanBeAssignedToCompleted',
               'entity' => 'case',
               'name' => 'caseCanBeAssignedToCompleted',
            ),
            13 =>
            (object) array(
               'label' => 'caseCanBeAssignedToArchived',
               'value' => 'caseCanBeAssignedToArchived',
               'entity' => 'case',
               'name' => 'caseCanBeAssignedToArchived',
            ),
            14 =>
            (object) array(
               'label' => 'caseCanBeAssignedToUnknown',
               'value' => 'caseCanBeAssignedToUnknown',
               'entity' => 'case',
               'name' => 'caseCanBeAssignedToUnknown',
            ),
            15 =>
            (object) array(
               'label' => 'caseListAccessRequests',
               'value' => 'caseListAccessRequests',
               'entity' => 'case',
               'name' => 'caseListAccessRequests',
            ),
            16 =>
            (object) array(
               'label' => 'caseViewAccessRequest',
               'value' => 'caseViewAccessRequest',
               'entity' => 'case',
               'name' => 'caseViewAccessRequest',
            ),
            17 =>
            (object) array(
               'label' => 'caseComplianceDelete',
               'value' => 'caseComplianceDelete',
               'entity' => 'case',
               'name' => 'caseComplianceDelete',
            ),
            18 =>
            (object) array(
               'label' => 'caseRestore',
               'value' => 'caseRestore',
               'entity' => 'case',
               'name' => 'caseRestore',
            ),
            19 =>
            (object) array(
               'label' => 'caseReopen',
               'value' => 'caseReopen',
               'entity' => 'case',
               'name' => 'caseReopen',
            ),
            20 =>
            (object) array(
               'label' => 'caseBsnLookup',
               'value' => 'caseBsnLookup',
               'entity' => 'case',
               'name' => 'caseBsnLookup',
            ),
            21 =>
            (object) array(
               'label' => 'caseAddressLookup',
               'value' => 'caseAddressLookup',
               'entity' => 'case',
               'name' => 'caseAddressLookup',
            ),
            22 =>
            (object) array(
               'label' => 'caseSearch',
               'value' => 'caseSearch',
               'entity' => 'case',
               'name' => 'caseSearch',
            ),
            23 =>
            (object) array(
               'label' => 'caseCanPickUpNew',
               'value' => 'caseCanPickUpNew',
               'entity' => 'case',
               'name' => 'caseCanPickUpNew',
            ),
            24 =>
            (object) array(
               'label' => 'taskCreate',
               'value' => 'taskCreate',
               'entity' => 'task',
               'name' => 'taskCreate',
            ),
            25 =>
            (object) array(
               'label' => 'taskEdit',
               'value' => 'taskEdit',
               'entity' => 'task',
               'name' => 'taskEdit',
            ),
            26 =>
            (object) array(
               'label' => 'taskUserDelete',
               'value' => 'taskUserDelete',
               'entity' => 'task',
               'name' => 'taskUserDelete',
            ),
            27 =>
            (object) array(
               'label' => 'taskViewAccessRequest',
               'value' => 'taskViewAccessRequest',
               'entity' => 'task',
               'name' => 'taskViewAccessRequest',
            ),
            28 =>
            (object) array(
               'label' => 'taskComplianceDelete',
               'value' => 'taskComplianceDelete',
               'entity' => 'task',
               'name' => 'taskComplianceDelete',
            ),
            29 =>
            (object) array(
               'label' => 'taskRestore',
               'value' => 'taskRestore',
               'entity' => 'task',
               'name' => 'taskRestore',
            ),
            30 =>
            (object) array(
               'label' => 'contextCreate',
               'value' => 'contextCreate',
               'entity' => 'context',
               'name' => 'contextCreate',
            ),
            31 =>
            (object) array(
               'label' => 'contextEdit',
               'value' => 'contextEdit',
               'entity' => 'context',
               'name' => 'contextEdit',
            ),
            32 =>
            (object) array(
               'label' => 'contextDelete',
               'value' => 'contextDelete',
               'entity' => 'context',
               'name' => 'contextDelete',
            ),
            33 =>
            (object) array(
               'label' => 'contextList',
               'value' => 'contextList',
               'entity' => 'context',
               'name' => 'contextList',
            ),
            34 =>
            (object) array(
               'label' => 'contextMerge',
               'value' => 'contextMerge',
               'entity' => 'context',
               'name' => 'contextMerge',
            ),
            35 =>
            (object) array(
               'label' => 'contextSearch',
               'value' => 'contextSearch',
               'entity' => 'context',
               'name' => 'contextSearch',
            ),
            36 =>
            (object) array(
               'label' => 'contextLink',
               'value' => 'contextLink',
               'entity' => 'context',
               'name' => 'contextLink',
            ),
            37 =>
            (object) array(
               'label' => 'caseArchive',
               'value' => 'caseArchive',
               'entity' => 'case',
               'name' => 'caseArchive',
            ),
            38 =>
            (object) array(
               'label' => 'placeCreate',
               'value' => 'placeCreate',
               'entity' => 'place',
               'name' => 'placeCreate',
            ),
            39 =>
            (object) array(
               'label' => 'placeEdit',
               'value' => 'placeEdit',
               'entity' => 'place',
               'name' => 'placeEdit',
            ),
            40 =>
            (object) array(
               'label' => 'placeEditOwnedByOrganisation',
               'value' => 'placeEditOwnedByOrganisation',
               'entity' => 'place',
               'name' => 'placeEditOwnedByOrganisation',
            ),
            41 =>
            (object) array(
               'label' => 'placeEditNotOwnedByOrganisation',
               'value' => 'placeEditNotOwnedByOrganisation',
               'entity' => 'place',
               'name' => 'placeEditNotOwnedByOrganisation',
            ),
            42 =>
            (object) array(
               'label' => 'placeDelete',
               'value' => 'placeDelete',
               'entity' => 'context',
               'name' => 'placeDelete',
            ),
            43 =>
            (object) array(
               'label' => 'placeList',
               'value' => 'placeList',
               'entity' => 'place',
               'name' => 'placeList',
            ),
            44 =>
            (object) array(
               'label' => 'placeMerge',
               'value' => 'placeMerge',
               'entity' => 'place',
               'name' => 'placeMerge',
            ),
            45 =>
            (object) array(
               'label' => 'placeSearch',
               'value' => 'placeSearch',
               'entity' => 'place',
               'name' => 'placeSearch',
            ),
            46 =>
            (object) array(
               'label' => 'placeCaseList',
               'value' => 'placeCaseList',
               'entity' => 'place',
               'name' => 'placeCaseList',
            ),
            47 =>
            (object) array(
               'label' => 'placeSectionList',
               'value' => 'placeSectionList',
               'entity' => 'place',
               'name' => 'placeSectionList',
            ),
            48 =>
            (object) array(
               'label' => 'placeSectionCreate',
               'value' => 'placeSectionCreate',
               'entity' => 'place',
               'name' => 'placeSectionCreate',
            ),
            49 =>
            (object) array(
               'label' => 'placeSectionCreateOwnedByOrganisation',
               'value' => 'placeSectionCreateOwnedByOrganisation',
               'entity' => 'place',
               'name' => 'placeSectionCreateOwnedByOrganisation',
            ),
            50 =>
            (object) array(
               'label' => 'placeSectionCreateNotOwnedByOrganisation',
               'value' => 'placeSectionCreateNotOwnedByOrganisation',
               'entity' => 'place',
               'name' => 'placeSectionCreateNotOwnedByOrganisation',
            ),
            51 =>
            (object) array(
               'label' => 'placeSectionEdit',
               'value' => 'placeSectionEdit',
               'entity' => 'place',
               'name' => 'placeSectionEdit',
            ),
            52 =>
            (object) array(
               'label' => 'placeSectionEditOwnedByOrganisation',
               'value' => 'placeSectionEditOwnedByOrganisation',
               'entity' => 'place',
               'name' => 'placeSectionEditOwnedByOrganisation',
            ),
            53 =>
            (object) array(
               'label' => 'placeSectionEditNotOwnedByOrganisation',
               'value' => 'placeSectionEditNotOwnedByOrganisation',
               'entity' => 'place',
               'name' => 'placeSectionEditNotOwnedByOrganisation',
            ),
            54 =>
            (object) array(
               'label' => 'placeSectionDelete',
               'value' => 'placeSectionDelete',
               'entity' => 'place',
               'name' => 'placeSectionDelete',
            ),
            55 =>
            (object) array(
               'label' => 'placeSectionMerge',
               'value' => 'placeSectionMerge',
               'entity' => 'place',
               'name' => 'placeSectionMerge',
            ),
            56 =>
            (object) array(
               'label' => 'intakeList',
               'value' => 'intakeList',
               'entity' => 'intake',
               'name' => 'intakeList',
            ),
            57 =>
            (object) array(
               'label' => 'placeVerify',
               'value' => 'placeVerify',
               'entity' => 'place',
               'name' => 'placeVerify',
            ),
            58 =>
            (object) array(
               'label' => 'organisationList',
               'value' => 'organisationList',
               'entity' => 'organisation',
               'name' => 'organisationList',
            ),
            59 =>
            (object) array(
               'label' => 'organisationUpdate',
               'value' => 'organisationUpdate',
               'entity' => 'organisation',
               'name' => 'organisationUpdate',
            ),
            60 =>
            (object) array(
               'label' => 'caseViewPlannerTimeline',
               'value' => 'caseViewPlannerTimeline',
               'entity' => 'case',
               'name' => 'caseViewPlannerTimeline',
            ),
            61 =>
            (object) array(
               'label' => 'caseViewSupervisionRegional',
               'value' => 'caseViewSupervisionRegional',
               'entity' => 'case',
               'name' => 'caseViewSupervisionRegional',
            ),
            62 =>
            (object) array(
               'label' => 'caseViewSupervisionNationwide',
               'value' => 'caseViewSupervisionNationwide',
               'entity' => 'case',
               'name' => 'caseViewSupervisionNationwide',
            ),
            63 =>
            (object) array(
               'label' => 'caseViewUserTimeline',
               'value' => 'caseViewUserTimeline',
               'entity' => 'case',
               'name' => 'caseViewUserTimeline',
            ),
            64 =>
            (object) array(
               'label' => 'caseBcoPhaseEdit',
               'value' => 'caseBcoPhaseEdit',
               'entity' => 'case',
               'name' => 'caseBcoPhaseEdit',
            ),
            65 =>
            (object) array(
               'label' => 'expertQuestionMedicalSupervisor',
               'value' => 'expertQuestionMedicalSupervisor',
               'name' => 'expertQuestionMedicalSupervisor',
            ),
            66 =>
            (object) array(
               'label' => 'expertQuestionConversationCoach',
               'value' => 'expertQuestionConversationCoach',
               'name' => 'expertQuestionConversationCoach',
            ),
            67 =>
            (object) array(
               'label' => 'expertQuestionList',
               'value' => 'expertQuestionList',
               'name' => 'expertQuestionList',
            ),
            68 =>
            (object) array(
               'label' => 'expertQuestionAssign',
               'value' => 'expertQuestionAssign',
               'name' => 'expertQuestionAssign',
            ),
            69 =>
            (object) array(
               'label' => 'expertQuestionAnswer',
               'value' => 'expertQuestionAnswer',
               'name' => 'expertQuestionAnswer',
            ),
            70 =>
            (object) array(
               'label' => 'callcenterView',
               'value' => 'callcenterView',
               'name' => 'callcenterView',
            ),
            71 =>
            (object) array(
               'label' => 'caseCreateCallToAction',
               'value' => 'caseCreateCallToAction',
               'name' => 'caseCreateCallToAction',
            ),
            72 =>
            (object) array(
               'label' => 'caseViewCallToAction',
               'value' => 'caseViewCallToAction',
               'name' => 'caseViewCallToAction',
            ),
            73 =>
            (object) array(
               'label' => 'choreList',
               'value' => 'choreList',
               'name' => 'choreList',
            ),
            74 =>
            (object) array(
               'label' => 'callToAction',
               'value' => 'callToAction',
               'name' => 'callToAction',
            ),
            75 =>
            (object) array(
               'label' => 'datacatalog',
               'value' => 'datacatalog',
               'name' => 'datacatalog',
            ),
            76 =>
            (object) array(
               'label' => 'caseEditViaSearchCase',
               'value' => 'caseEditViaSearchCase',
               'name' => 'caseEditViaSearchCase',
            ),
            77 =>
            (object) array(
               'label' => 'caseEditCallcenterExpert',
               'value' => 'caseEditCallcenterExpert',
               'name' => 'caseEditCallcenterExpert',
            ),
            78 =>
            (object) array(
               'label' => 'caseMetricsList',
               'value' => 'caseMetricsList',
               'name' => 'caseMetricsList',
            ),
            79 =>
            (object) array(
               'label' => 'addCasesByChore',
               'value' => 'addCasesByChore',
               'name' => 'addCasesByChore',
            ),
            80 =>
            (object) array(
               'label' => 'caseCreateNote',
               'value' => 'caseCreateNote',
               'entity' => 'case',
               'name' => 'caseCreateNote',
            ),
            81 =>
            (object) array(
               'label' => 'createCallToActionViaSearchCase',
               'value' => 'createCallToActionViaSearchCase',
               'name' => 'createCallToActionViaSearchCase',
            ),
            82 =>
            (object) array(
               'label' => 'caseViewOsirisHistory',
               'value' => 'caseViewOsirisHistory',
               'name' => 'caseViewOsirisHistory',
            ),
            83 =>
            (object) array(
               'label' => 'adminView',
               'value' => 'adminView',
               'name' => 'adminView',
            ),
            84 =>
            (object) array(
               'label' => 'adminPolicyAdviceModule',
               'value' => 'adminPolicyAdviceModule',
               'name' => 'adminPolicyAdviceModule',
            ),
          ),
        );
    }
}

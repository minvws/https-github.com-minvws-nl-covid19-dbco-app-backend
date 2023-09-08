<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 *
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit CaseNoteType.json!
 *
 * @codeCoverageIgnore
 *
 * @method static CaseNoteType caseAdded() caseAdded() Notitie toegevoegd bij aanmaken
 * @method static CaseNoteType caseReturned() caseReturned() Case teruggegeven aan werkverdeler
 * @method static CaseNoteType caseCheckedApprovedClosed() caseCheckedApprovedClosed() Case gecontroleerd, goedgekeurd en case gesloten
 * @method static CaseNoteType caseCheckedRejectedReturned() caseCheckedRejectedReturned() Case gecontroleerd, afgekeurd en teruggegeven
 * @method static CaseNoteType caseNotCheckedReturned() caseNotCheckedReturned() Case niet gecontroleerd en teruggegeven voor controle
 * @method static CaseNoteType caseNotCheckedClosed() caseNotCheckedClosed() Case niet gecontroleerd en case gesloten
 * @method static CaseNoteType caseDirectlyArchived() caseDirectlyArchived() Case direct gesloten
 * @method static CaseNoteType caseReopened() caseReopened() Case heropend
 * @method static CaseNoteType caseChangedOrganisation() caseChangedOrganisation() Case is overgedragen van organisatie
 * @method static CaseNoteType caseNote() caseNote() Notitie door werkverdeler
 * @method static CaseNoteType caseNoteIndexBySearch() caseNoteIndexBySearch() Notitie over index via dossier zoeken
 * @method static CaseNoteType caseNoteContactBySearch() caseNoteContactBySearch() Notitie over contact via dossier zoeken

 * @property-read string $value
*/
final class CaseNoteType extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'CaseNoteType',
           'tsConst' => 'caseNoteType',
           'items' =>
          array (
            0 =>
            (object) array(
               'label' => 'Notitie toegevoegd bij aanmaken',
               'value' => 'case-added',
               'name' => 'caseAdded',
            ),
            1 =>
            (object) array(
               'label' => 'Case teruggegeven aan werkverdeler',
               'value' => 'case-returned',
               'name' => 'caseReturned',
            ),
            2 =>
            (object) array(
               'label' => 'Case gecontroleerd, goedgekeurd en case gesloten',
               'value' => 'case-checked-approved-closed',
               'name' => 'caseCheckedApprovedClosed',
            ),
            3 =>
            (object) array(
               'label' => 'Case gecontroleerd, afgekeurd en teruggegeven',
               'value' => 'case-checked-rejected-returned',
               'name' => 'caseCheckedRejectedReturned',
            ),
            4 =>
            (object) array(
               'label' => 'Case niet gecontroleerd en teruggegeven voor controle',
               'value' => 'case-not-checked-returned',
               'name' => 'caseNotCheckedReturned',
            ),
            5 =>
            (object) array(
               'label' => 'Case niet gecontroleerd en case gesloten',
               'value' => 'case-not-checked-closed',
               'name' => 'caseNotCheckedClosed',
            ),
            6 =>
            (object) array(
               'label' => 'Case direct gesloten',
               'value' => 'case-directly-archived',
               'name' => 'caseDirectlyArchived',
            ),
            7 =>
            (object) array(
               'label' => 'Case heropend',
               'value' => 'case-reopened',
               'name' => 'caseReopened',
            ),
            8 =>
            (object) array(
               'label' => 'Case is overgedragen van organisatie',
               'value' => 'case-changed-organisation',
               'name' => 'caseChangedOrganisation',
            ),
            9 =>
            (object) array(
               'label' => 'Notitie door werkverdeler',
               'value' => 'case-note',
               'name' => 'caseNote',
            ),
            10 =>
            (object) array(
               'label' => 'Notitie over index via dossier zoeken',
               'value' => 'case-note-index-by-search',
               'name' => 'caseNoteIndexBySearch',
            ),
            11 =>
            (object) array(
               'label' => 'Notitie over contact via dossier zoeken',
               'value' => 'case-note-contact-by-search',
               'name' => 'caseNoteContactBySearch',
            ),
          ),
        );
    }
}

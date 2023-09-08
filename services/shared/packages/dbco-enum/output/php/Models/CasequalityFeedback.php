<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 *
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit CasequalityFeedback.json!
 *
 * @codeCoverageIgnore
 *
 * @method static CasequalityFeedback approveAndArchive() approveAndArchive() Goedgekeurd: sluiten
 * @method static CasequalityFeedback rejectAndReopen() rejectAndReopen() Aanpassing nodig: teruggeven aan werkverdeler
 * @method static CasequalityFeedback complete() complete() Niet gecontroleerd: teruggeven aan werkverdeler voor controle
 * @method static CasequalityFeedback archive() archive() Niet gecontroleerd: sluiten

 * @property-read string $value
*/
final class CasequalityFeedback extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'CasequalityFeedback',
           'tsConst' => 'casequalityFeedback',
           'items' =>
          array (
            0 =>
            (object) array(
               'label' => 'Goedgekeurd: sluiten',
               'value' => 'approve_and_archive',
               'name' => 'approveAndArchive',
            ),
            1 =>
            (object) array(
               'label' => 'Aanpassing nodig: teruggeven aan werkverdeler',
               'value' => 'reject_and_reopen',
               'name' => 'rejectAndReopen',
            ),
            2 =>
            (object) array(
               'label' => 'Niet gecontroleerd: teruggeven aan werkverdeler voor controle',
               'value' => 'complete',
               'name' => 'complete',
            ),
            3 =>
            (object) array(
               'label' => 'Niet gecontroleerd: sluiten',
               'value' => 'archive',
               'name' => 'archive',
            ),
          ),
        );
    }
}

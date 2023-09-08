<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 *
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit ContactTracingStatus.json!
 *
 * @codeCoverageIgnore
 *
 * @method static ContactTracingStatus notApproached() notApproached() Index nog niet benaderd
 * @method static ContactTracingStatus notReachable() notReachable() De index was onbereikbaar
 * @method static ContactTracingStatus conversationStarted() conversationStarted() Gestart, nog niet afgerond
 * @method static ContactTracingStatus closedOutsideGgd() closedOutsideGgd() Case afronden, BCO wordt uitgevoerd buiten GGD
 * @method static ContactTracingStatus closedNoCollaboration() closedNoCollaboration() Case afronden, index wil niet (volledig) meewerken aan BCO
 * @method static ContactTracingStatus completed() completed() Indexgesprek voltooid
 * @method static ContactTracingStatus new() new() Nieuw
 * @method static ContactTracingStatus notStarted() notStarted() Nog niet begonnen
 * @method static ContactTracingStatus twoTimesNotReached() twoTimesNotReached() 2x geen gehoor: index niet bereikt
 * @method static ContactTracingStatus callbackRequest() callbackRequest() Terugbelverzoek
 * @method static ContactTracingStatus looseEnd() looseEnd() Los eindje: kleine openstaande taken
 * @method static ContactTracingStatus fourTimesNotReached() fourTimesNotReached() 4x geen gehoor: index niet bereikt
 * @method static ContactTracingStatus bcoFinished() bcoFinished() BCO Afgerond
 * @method static ContactTracingStatus closed() closed() Gesloten
 * @method static ContactTracingStatus unknown() unknown() Onbekend

 * @property-read string $value
*/
final class ContactTracingStatus extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'ContactTracingStatus',
           'tsConst' => 'contactTracingStatus',
           'default' => 'new',
           'items' =>
          array (
            0 =>
            (object) array(
               'label' => 'Index nog niet benaderd',
               'value' => 'not_approached',
               'name' => 'notApproached',
            ),
            1 =>
            (object) array(
               'label' => 'De index was onbereikbaar',
               'value' => 'not_reachable',
               'name' => 'notReachable',
            ),
            2 =>
            (object) array(
               'label' => 'Gestart, nog niet afgerond',
               'value' => 'conversation_started',
               'name' => 'conversationStarted',
            ),
            3 =>
            (object) array(
               'label' => 'Case afronden, BCO wordt uitgevoerd buiten GGD',
               'value' => 'closed_outside_ggd',
               'name' => 'closedOutsideGgd',
            ),
            4 =>
            (object) array(
               'label' => 'Case afronden, index wil niet (volledig) meewerken aan BCO',
               'value' => 'closed_no_collaboration',
               'name' => 'closedNoCollaboration',
            ),
            5 =>
            (object) array(
               'label' => 'Indexgesprek voltooid',
               'value' => 'completed',
               'name' => 'completed',
            ),
            6 =>
            (object) array(
               'label' => 'Nieuw',
               'value' => 'new',
               'name' => 'new',
            ),
            7 =>
            (object) array(
               'label' => 'Nog niet begonnen',
               'value' => 'not_started',
               'name' => 'notStarted',
            ),
            8 =>
            (object) array(
               'label' => '2x geen gehoor: index niet bereikt',
               'value' => 'two_times_not_reached',
               'name' => 'twoTimesNotReached',
            ),
            9 =>
            (object) array(
               'label' => 'Terugbelverzoek',
               'value' => 'callback_request',
               'name' => 'callbackRequest',
            ),
            10 =>
            (object) array(
               'label' => 'Los eindje: kleine openstaande taken',
               'value' => 'loose_end',
               'name' => 'looseEnd',
            ),
            11 =>
            (object) array(
               'label' => '4x geen gehoor: index niet bereikt',
               'value' => 'four_times_not_reached',
               'name' => 'fourTimesNotReached',
            ),
            12 =>
            (object) array(
               'label' => 'BCO Afgerond',
               'value' => 'bco_finished',
               'name' => 'bcoFinished',
            ),
            13 =>
            (object) array(
               'label' => 'Gesloten',
               'value' => 'closed',
               'name' => 'closed',
            ),
            14 =>
            (object) array(
               'label' => 'Onbekend',
               'value' => 'unknown',
               'name' => 'unknown',
            ),
          ),
        );
    }
}

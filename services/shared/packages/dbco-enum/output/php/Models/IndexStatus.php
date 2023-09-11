<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 * Index status for case
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit IndexStatus.json!
 *
 * @codeCoverageIgnore
 *
 * @method static IndexStatus initial() initial() Koppelcode gedeeld
 * @method static IndexStatus pairingRequestAccepted() pairingRequestAccepted() Pairing request geaccepteerd
 * @method static IndexStatus paired() paired() Nog niets ontvangen
 * @method static IndexStatus delivered() delivered() Gegevens aangeleverd
 * @method static IndexStatus timeout() timeout() Verlopen
 * @method static IndexStatus expired() expired() Koppelcode verlopen

 * @property-read string $value
*/
final class IndexStatus extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'IndexStatus',
           'tsConst' => 'indexStatus',
           'description' => 'Index status for case',
           'default' => 'initial',
           'items' =>
          array (
            0 =>
            (object) array(
               'label' => 'Koppelcode gedeeld',
               'value' => 'initial',
               'name' => 'initial',
            ),
            1 =>
            (object) array(
               'label' => 'Pairing request geaccepteerd',
               'value' => 'pairing_request_accepted',
               'name' => 'pairingRequestAccepted',
            ),
            2 =>
            (object) array(
               'label' => 'Nog niets ontvangen',
               'value' => 'paired',
               'name' => 'paired',
            ),
            3 =>
            (object) array(
               'label' => 'Gegevens aangeleverd',
               'value' => 'delivered',
               'name' => 'delivered',
            ),
            4 =>
            (object) array(
               'label' => 'Verlopen',
               'value' => 'timeout',
               'name' => 'timeout',
            ),
            5 =>
            (object) array(
               'label' => 'Koppelcode verlopen',
               'value' => 'expired',
               'name' => 'expired',
            ),
          ),
        );
    }
}

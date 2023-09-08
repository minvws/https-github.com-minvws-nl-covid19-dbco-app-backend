<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 * Reason for missing bsn
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit NoBsnOrAddressReason.json!
 *
 * @codeCoverageIgnore
 *
 * @method static NoBsnOrAddressReason homeless() homeless() Dak- of thuisloos
 * @method static NoBsnOrAddressReason foreignPasserby() foreignPasserby() Buitenlandse passant (geen BSN)
 * @method static NoBsnOrAddressReason postalcodeUnknown() postalcodeUnknown() Postcode onbekend
 * @method static NoBsnOrAddressReason noCooperation() noCooperation() Wil niet meewerken

 * @property-read string $value
*/
final class NoBsnOrAddressReason extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'NoBsnOrAddressReason',
           'tsConst' => 'noBsnOrAddressReason',
           'description' => 'Reason for missing bsn',
           'items' =>
          array (
            0 =>
            (object) array(
               'value' => 'homeless',
               'label' => 'Dak- of thuisloos',
               'name' => 'homeless',
            ),
            1 =>
            (object) array(
               'value' => 'foreign_passerby',
               'label' => 'Buitenlandse passant (geen BSN)',
               'name' => 'foreignPasserby',
            ),
            2 =>
            (object) array(
               'value' => 'postalcode_unknown',
               'label' => 'Postcode onbekend',
               'name' => 'postalcodeUnknown',
            ),
            3 =>
            (object) array(
               'value' => 'no_cooperation',
               'label' => 'Wil niet meewerken',
               'name' => 'noCooperation',
            ),
          ),
        );
    }
}

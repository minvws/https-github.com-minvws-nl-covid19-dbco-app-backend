<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 * Verification status of address details, automatically executed by the system during index identification.
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit AutomaticAddressVerificationStatus.json!
 *
 * @codeCoverageIgnore
 *
 * @method static AutomaticAddressVerificationStatus unchecked() unchecked() Niet gecontroleerd
 * @method static AutomaticAddressVerificationStatus verified() verified() Geverifieerd
 * @method static AutomaticAddressVerificationStatus unverified() unverified() Ongeverifieerd

 * @property-read string $value
*/
final class AutomaticAddressVerificationStatus extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'AutomaticAddressVerificationStatus',
           'tsConst' => 'automaticAddressVerificationStatus',
           'description' => 'Verification status of address details, automatically executed by the system during index identification.',
           'default' => 'unchecked',
           'items' =>
          array (
            0 =>
            (object) array(
               'label' => 'Niet gecontroleerd',
               'value' => 'unchecked',
               'name' => 'unchecked',
            ),
            1 =>
            (object) array(
               'label' => 'Geverifieerd',
               'value' => 'verified',
               'name' => 'verified',
            ),
            2 =>
            (object) array(
               'label' => 'Ongeverifieerd',
               'value' => 'unverified',
               'name' => 'unverified',
            ),
          ),
        );
    }
}

<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 *
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit ContactOriginDate.json!
 *
 * @codeCoverageIgnore
 *
 * @method static ContactOriginDate dateOfLastExposure() dateOfLastExposure() Laatste contactdatum

 * @property-read string $value
*/
final class ContactOriginDate extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'ContactOriginDate',
           'tsConst' => 'contactOriginDate',
           'default' => 'dateOfLastExposure',
           'items' =>
          array (
            0 =>
            (object) array(
               'label' => 'Laatste contactdatum',
               'value' => 'dateOfLastExposure',
               'name' => 'dateOfLastExposure',
            ),
          ),
        );
    }
}

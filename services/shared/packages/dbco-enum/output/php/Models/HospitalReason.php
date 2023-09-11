<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 *
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit HospitalReason.json!
 *
 * @codeCoverageIgnore
 *
 * @method static HospitalReason covid() covid() (Verdenking van) COVID-19
 * @method static HospitalReason other() other() Andere indicatie
 * @method static HospitalReason unknown() unknown() Onbekend

 * @property-read string $value
*/
final class HospitalReason extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'HospitalReason',
           'tsConst' => 'hospitalReason',
           'items' =>
          array (
            0 =>
            (object) array(
               'label' => '(Verdenking van) COVID-19',
               'value' => 'covid',
               'name' => 'covid',
            ),
            1 =>
            (object) array(
               'label' => 'Andere indicatie',
               'value' => 'other',
               'name' => 'other',
            ),
            2 =>
            (object) array(
               'label' => 'Onbekend',
               'value' => 'unknown',
               'name' => 'unknown',
            ),
          ),
        );
    }
}

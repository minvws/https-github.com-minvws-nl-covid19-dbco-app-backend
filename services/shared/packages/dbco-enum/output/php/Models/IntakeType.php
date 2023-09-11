<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 *
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit IntakeType.json!
 *
 * @codeCoverageIgnore
 *
 * @method static IntakeType bco() bco() BCO
 * @method static IntakeType selftest() selftest() Zelftest

 * @property-read string $value
*/
final class IntakeType extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'IntakeType',
           'tsConst' => 'intakeType',
           'items' =>
          array (
            0 =>
            (object) array(
               'label' => 'BCO',
               'value' => 'bco',
               'name' => 'bco',
            ),
            1 =>
            (object) array(
               'label' => 'Zelftest',
               'value' => 'selftest',
               'name' => 'selftest',
            ),
          ),
        );
    }
}

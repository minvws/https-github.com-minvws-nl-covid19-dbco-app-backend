<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 *
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit InfectionIndicator.json!
 *
 * @codeCoverageIgnore
 *
 * @method static InfectionIndicator selfTest() selfTest() Zelftest
 * @method static InfectionIndicator labTest() labTest() Laboratoriumtest
 * @method static InfectionIndicator unknown() unknown() Onbekend

 * @property-read string $value
*/
final class InfectionIndicator extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'InfectionIndicator',
           'tsConst' => 'infectionIndicator',
           'items' =>
          array (
            0 =>
            (object) array(
               'label' => 'Zelftest',
               'value' => 'selfTest',
               'name' => 'selfTest',
            ),
            1 =>
            (object) array(
               'label' => 'Laboratoriumtest',
               'value' => 'labTest',
               'name' => 'labTest',
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

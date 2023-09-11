<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 * Test result
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit TestResult.json!
 *
 * @codeCoverageIgnore
 *
 * @method static TestResult positive() positive() Positief
 * @method static TestResult negative() negative() Negatief
 * @method static TestResult unknown() unknown() Onbekend

 * @property-read string $value
*/
final class TestResult extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'TestResult',
           'tsConst' => 'testResult',
           'description' => 'Test result',
           'items' =>
          array (
            0 =>
            (object) array(
               'label' => 'Positief',
               'value' => 'positive',
               'name' => 'positive',
            ),
            1 =>
            (object) array(
               'label' => 'Negatief',
               'value' => 'negative',
               'name' => 'negative',
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

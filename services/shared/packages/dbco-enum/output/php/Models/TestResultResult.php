<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 *
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit TestResultResult.json!
 *
 * @codeCoverageIgnore
 *
 * @method static TestResultResult positive() positive() Positief
 * @method static TestResultResult negative() negative() Negatief
 * @method static TestResultResult unknown() unknown() Onbekend

 * @property-read string $value
*/
final class TestResultResult extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'TestResultResult',
           'tsConst' => 'testResultResult',
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

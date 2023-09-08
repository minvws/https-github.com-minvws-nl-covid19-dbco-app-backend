<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 *
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit TestResultType.json!
 *
 * @codeCoverageIgnore
 *
 * @method static TestResultType lab() lab() Labuitslag
 * @method static TestResultType selftest() selftest() Zelftest
 * @method static TestResultType unknown() unknown() Onbekend

 * @property-read string $value
*/
final class TestResultType extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'TestResultType',
           'tsConst' => 'testResultType',
           'items' =>
          array (
            0 =>
            (object) array(
               'label' => 'Labuitslag',
               'value' => 'lab',
               'name' => 'lab',
            ),
            1 =>
            (object) array(
               'label' => 'Zelftest',
               'value' => 'selftest',
               'name' => 'selftest',
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

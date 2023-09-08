<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 *
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit TestResultSource.json!
 *
 * @codeCoverageIgnore
 *
 * @method static TestResultSource coronit() coronit() CoronIT
 * @method static TestResultSource manual() manual() Handmatig aangemaakt
 * @method static TestResultSource meldportaal() meldportaal() Meldportaal
 * @method static TestResultSource publicWebPortal() publicWebPortal() Zelfportaal

 * @property-read string $value
*/
final class TestResultSource extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'TestResultSource',
           'tsConst' => 'testResultSource',
           'items' =>
          array (
            0 =>
            (object) array(
               'label' => 'CoronIT',
               'value' => 'coronit',
               'name' => 'coronit',
            ),
            1 =>
            (object) array(
               'label' => 'Handmatig aangemaakt',
               'value' => 'manual',
               'name' => 'manual',
            ),
            2 =>
            (object) array(
               'label' => 'Meldportaal',
               'value' => 'meldportaal',
               'name' => 'meldportaal',
            ),
            3 =>
            (object) array(
               'label' => 'Zelfportaal',
               'value' => 'publicWebPortal',
               'name' => 'publicWebPortal',
            ),
          ),
        );
    }
}

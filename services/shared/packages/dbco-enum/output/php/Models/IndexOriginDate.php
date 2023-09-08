<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 *
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit IndexOriginDate.json!
 *
 * @codeCoverageIgnore
 *
 * @method static IndexOriginDate dateOfTest() dateOfTest() Testdatum
 * @method static IndexOriginDate symptomsOnset() symptomsOnset() EZD

 * @property-read string $value
*/
final class IndexOriginDate extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'IndexOriginDate',
           'tsConst' => 'indexOriginDate',
           'default' => 'dateOfTest',
           'items' =>
          array (
            0 =>
            (object) array(
               'label' => 'Testdatum',
               'value' => 'dateOfTest',
               'name' => 'dateOfTest',
            ),
            1 =>
            (object) array(
               'label' => 'EZD',
               'value' => 'symptomsOnset',
               'name' => 'symptomsOnset',
            ),
          ),
        );
    }
}

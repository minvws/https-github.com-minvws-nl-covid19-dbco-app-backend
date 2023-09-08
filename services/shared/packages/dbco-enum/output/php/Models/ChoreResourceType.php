<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 * Chore resource types
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit ChoreResourceType.json!
 *
 * @codeCoverageIgnore
 *
 * @method static ChoreResourceType covidCase() covidCase() Covid case

 * @property-read string $value
*/
final class ChoreResourceType extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'ChoreResourceType',
           'tsConst' => 'choreResourceType',
           'description' => 'Chore resource types',
           'items' =>
          array (
            0 =>
            (object) array(
               'label' => 'Covid case',
               'value' => 'covid-case',
               'name' => 'covidCase',
            ),
          ),
        );
    }
}

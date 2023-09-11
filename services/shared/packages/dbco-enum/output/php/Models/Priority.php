<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 * Prioriteit
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit Priority.json!
 *
 * @codeCoverageIgnore
 *
 * @method static Priority none() none() Geen prioriteit
 * @method static Priority normal() normal() Normaal
 * @method static Priority high() high() Hoog
 * @method static Priority veryHigh() veryHigh() Heel hoog

 * @property-read int $value
*/
final class Priority extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'Priority',
           'tsConst' => 'priority',
           'description' => 'Prioriteit',
           'scalarType' => 'int',
           'items' =>
          array (
            0 =>
            (object) array(
               'value' => 0,
               'name' => 'none',
               'label' => 'Geen prioriteit',
            ),
            1 =>
            (object) array(
               'value' => 1,
               'name' => 'normal',
               'label' => 'Normaal',
            ),
            2 =>
            (object) array(
               'value' => 2,
               'name' => 'high',
               'label' => 'Hoog',
            ),
            3 =>
            (object) array(
               'value' => 3,
               'name' => 'veryHigh',
               'label' => 'Heel hoog',
            ),
          ),
        );
    }
}

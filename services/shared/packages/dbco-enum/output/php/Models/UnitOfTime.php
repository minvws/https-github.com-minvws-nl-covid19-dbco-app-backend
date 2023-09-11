<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 *
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit UnitOfTime.json!
 *
 * @codeCoverageIgnore
 *
 * @method static UnitOfTime day() day() day
 * @method static UnitOfTime week() week() week
 * @method static UnitOfTime month() month() month

 * @property-read string $value
*/
final class UnitOfTime extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'UnitOfTime',
           'tsConst' => 'unitOfTime',
           'items' =>
          array (
            0 =>
            (object) array(
               'label' => 'day',
               'value' => 'day',
               'name' => 'day',
            ),
            1 =>
            (object) array(
               'label' => 'week',
               'value' => 'week',
               'name' => 'week',
            ),
            2 =>
            (object) array(
               'label' => 'month',
               'value' => 'month',
               'name' => 'month',
            ),
          ),
        );
    }
}

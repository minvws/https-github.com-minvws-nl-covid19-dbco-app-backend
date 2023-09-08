<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 *
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit CalendarItem.json!
 *
 * @codeCoverageIgnore
 *
 * @method static CalendarItem point() point() Één dag
 * @method static CalendarItem period() period() Periode

 * @property-read string $value
*/
final class CalendarItem extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'CalendarItem',
           'tsConst' => 'calendarItem',
           'items' =>
          array (
            0 =>
            (object) array(
               'label' => 'Één dag',
               'value' => 'point',
               'name' => 'point',
            ),
            1 =>
            (object) array(
               'label' => 'Periode',
               'value' => 'period',
               'name' => 'period',
            ),
          ),
        );
    }
}

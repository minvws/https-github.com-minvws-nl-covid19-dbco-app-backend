<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 *
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit CalendarItemConfigStrategyIdentifierType.json!
 *
 * @codeCoverageIgnore
 *
 * @method static CalendarItemConfigStrategyIdentifierType point() point() point
 * @method static CalendarItemConfigStrategyIdentifierType periodStart() periodStart() Period Start
 * @method static CalendarItemConfigStrategyIdentifierType periodEnd() periodEnd() Period End

 * @property-read string $value
*/
final class CalendarItemConfigStrategyIdentifierType extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'CalendarItemConfigStrategyIdentifierType',
           'tsConst' => 'calendarItemConfigStrategyIdentifierType',
           'items' =>
          array (
            0 =>
            (object) array(
               'label' => 'point',
               'value' => 'point',
               'name' => 'point',
            ),
            1 =>
            (object) array(
               'label' => 'Period Start',
               'value' => 'periodStart',
               'name' => 'periodStart',
            ),
            2 =>
            (object) array(
               'label' => 'Period End',
               'value' => 'periodEnd',
               'name' => 'periodEnd',
            ),
          ),
        );
    }
}

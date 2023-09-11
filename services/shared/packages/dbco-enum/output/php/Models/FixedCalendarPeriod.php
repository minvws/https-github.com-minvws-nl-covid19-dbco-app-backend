<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 * All fixed calendar periods
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit FixedCalendarPeriod.json!
 *
 * @codeCoverageIgnore
 *
 * @method static FixedCalendarPeriod episode() episode() Episode
 * @method static FixedCalendarPeriod source() source() Source
 * @method static FixedCalendarPeriod contagious() contagious() Contagious

 * @property-read string $value
*/
final class FixedCalendarPeriod extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'FixedCalendarPeriod',
           'tsConst' => 'fixedCalendarPeriod',
           'description' => 'All fixed calendar periods',
           'items' =>
          array (
            0 =>
            (object) array(
               'label' => 'Episode',
               'value' => 'episode',
               'name' => 'episode',
            ),
            1 =>
            (object) array(
               'label' => 'Source',
               'value' => 'source',
               'name' => 'source',
            ),
            2 =>
            (object) array(
               'label' => 'Contagious',
               'value' => 'contagious',
               'name' => 'contagious',
            ),
          ),
        );
    }
}

<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 * Fixed calendar items
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit FixedCalendarItem.json!
 *
 * @codeCoverageIgnore
 *
 * @method static FixedCalendarItem source() source() Source
 * @method static FixedCalendarItem contagious() contagious() Contagious

 * @property-read string $value
*/
final class FixedCalendarItem extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'FixedCalendarItem',
           'tsConst' => 'FixedCalendarItem',
           'description' => 'Fixed calendar items',
           'items' =>
          array (
            0 =>
            (object) array(
               'label' => 'Source',
               'value' => 'source',
               'name' => 'source',
            ),
            1 =>
            (object) array(
               'label' => 'Contagious',
               'value' => 'contagious',
               'name' => 'contagious',
            ),
          ),
        );
    }
}

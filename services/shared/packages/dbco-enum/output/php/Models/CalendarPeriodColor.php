<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 *
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit CalendarPeriodColor.json!
 *
 * @codeCoverageIgnore
 *
 * @method static CalendarPeriodColor lightRed() lightRed() Licht rood
 * @method static CalendarPeriodColor lightOrange() lightOrange() Licht oranje
 * @method static CalendarPeriodColor lightYellow() lightYellow() Licht geel
 * @method static CalendarPeriodColor lightGreen() lightGreen() Licht groen
 * @method static CalendarPeriodColor lightBlue() lightBlue() Licht blauw
 * @method static CalendarPeriodColor lightPurple() lightPurple() Licht paars
 * @method static CalendarPeriodColor lightLavender() lightLavender() Licht lavendel
 * @method static CalendarPeriodColor lightPink() lightPink() Licht roze

 * @property-read string $value
*/
final class CalendarPeriodColor extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'CalendarPeriodColor',
           'tsConst' => 'calendarPeriodColor',
           'items' =>
          array (
            0 =>
            (object) array(
               'label' => 'Licht rood',
               'value' => 'light_red',
               'name' => 'lightRed',
            ),
            1 =>
            (object) array(
               'label' => 'Licht oranje',
               'value' => 'light_orange',
               'name' => 'lightOrange',
            ),
            2 =>
            (object) array(
               'label' => 'Licht geel',
               'value' => 'light_yellow',
               'name' => 'lightYellow',
            ),
            3 =>
            (object) array(
               'label' => 'Licht groen',
               'value' => 'light_green',
               'name' => 'lightGreen',
            ),
            4 =>
            (object) array(
               'label' => 'Licht blauw',
               'value' => 'light_blue',
               'name' => 'lightBlue',
            ),
            5 =>
            (object) array(
               'label' => 'Licht paars',
               'value' => 'light_purple',
               'name' => 'lightPurple',
            ),
            6 =>
            (object) array(
               'label' => 'Licht lavendel',
               'value' => 'light_lavender',
               'name' => 'lightLavender',
            ),
            7 =>
            (object) array(
               'label' => 'Licht roze',
               'value' => 'light_pink',
               'name' => 'lightPink',
            ),
          ),
        );
    }
}

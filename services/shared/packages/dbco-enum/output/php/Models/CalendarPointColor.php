<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 *
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit CalendarPointColor.json!
 *
 * @codeCoverageIgnore
 *
 * @method static CalendarPointColor red() red() Rood
 * @method static CalendarPointColor orange() orange() Oranje
 * @method static CalendarPointColor yellow() yellow() Accessibility geel
 * @method static CalendarPointColor green() green() Zee Groen
 * @method static CalendarPointColor azureBlue() azureBlue() Azuur blauw
 * @method static CalendarPointColor purple() purple() Paars blauw
 * @method static CalendarPointColor lavender() lavender() Lavendel
 * @method static CalendarPointColor pink() pink() Rose

 * @property-read string $value
*/
final class CalendarPointColor extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'CalendarPointColor',
           'tsConst' => 'calendarPointColor',
           'items' =>
          array (
            0 =>
            (object) array(
               'label' => 'Rood',
               'value' => 'red',
               'name' => 'red',
            ),
            1 =>
            (object) array(
               'label' => 'Oranje',
               'value' => 'orange',
               'name' => 'orange',
            ),
            2 =>
            (object) array(
               'label' => 'Accessibility geel',
               'value' => 'yellow',
               'name' => 'yellow',
            ),
            3 =>
            (object) array(
               'label' => 'Zee Groen',
               'value' => 'green',
               'name' => 'green',
            ),
            4 =>
            (object) array(
               'label' => 'Azuur blauw',
               'value' => 'azure_blue',
               'name' => 'azureBlue',
            ),
            5 =>
            (object) array(
               'label' => 'Paars blauw',
               'value' => 'purple',
               'name' => 'purple',
            ),
            6 =>
            (object) array(
               'label' => 'Lavendel',
               'value' => 'lavender',
               'name' => 'lavender',
            ),
            7 =>
            (object) array(
               'label' => 'Rose',
               'value' => 'pink',
               'name' => 'pink',
            ),
          ),
        );
    }
}

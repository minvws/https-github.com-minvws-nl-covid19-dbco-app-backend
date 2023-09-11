<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 *
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit TransportationType.json!
 *
 * @codeCoverageIgnore
 *
 * @method static TransportationType car() car() Auto
 * @method static TransportationType bus() bus() Bus
 * @method static TransportationType moped() moped() Brommer / Scooter
 * @method static TransportationType caravan() caravan() Caravan / camper
 * @method static TransportationType bicycle() bicycle() Fiets
 * @method static TransportationType byFoot() byFoot() Lopend / Te voet
 * @method static TransportationType subway() subway() Metro
 * @method static TransportationType motorcycle() motorcycle() Motor
 * @method static TransportationType tram() tram() Tram
 * @method static TransportationType train() train() Trein
 * @method static TransportationType ship() ship() (Veer)boot
 * @method static TransportationType plane() plane() Vliegtuig
 * @method static TransportationType truck() truck() Vrachtwagen
 * @method static TransportationType other() other() Overig

 * @property-read string $value
*/
final class TransportationType extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'TransportationType',
           'tsConst' => 'transportationType',
           'items' =>
          array (
            0 =>
            (object) array(
               'label' => 'Auto',
               'value' => 'car',
               'name' => 'car',
            ),
            1 =>
            (object) array(
               'label' => 'Bus',
               'value' => 'bus',
               'name' => 'bus',
            ),
            2 =>
            (object) array(
               'label' => 'Brommer / Scooter',
               'value' => 'moped',
               'name' => 'moped',
            ),
            3 =>
            (object) array(
               'label' => 'Caravan / camper',
               'value' => 'caravan',
               'name' => 'caravan',
            ),
            4 =>
            (object) array(
               'label' => 'Fiets',
               'value' => 'bicycle',
               'name' => 'bicycle',
            ),
            5 =>
            (object) array(
               'label' => 'Lopend / Te voet',
               'value' => 'by-foot',
               'name' => 'byFoot',
            ),
            6 =>
            (object) array(
               'label' => 'Metro',
               'value' => 'subway',
               'name' => 'subway',
            ),
            7 =>
            (object) array(
               'label' => 'Motor',
               'value' => 'motorcycle',
               'name' => 'motorcycle',
            ),
            8 =>
            (object) array(
               'label' => 'Tram',
               'value' => 'tram',
               'name' => 'tram',
            ),
            9 =>
            (object) array(
               'label' => 'Trein',
               'value' => 'train',
               'name' => 'train',
            ),
            10 =>
            (object) array(
               'label' => '(Veer)boot',
               'value' => 'ship',
               'name' => 'ship',
            ),
            11 =>
            (object) array(
               'label' => 'Vliegtuig',
               'value' => 'plane',
               'name' => 'plane',
            ),
            12 =>
            (object) array(
               'label' => 'Vrachtwagen',
               'value' => 'truck',
               'name' => 'truck',
            ),
            13 =>
            (object) array(
               'label' => 'Overig',
               'value' => 'other',
               'name' => 'other',
            ),
          ),
        );
    }
}

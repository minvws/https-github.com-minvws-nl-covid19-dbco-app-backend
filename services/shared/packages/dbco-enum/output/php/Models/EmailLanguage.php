<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 *
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit EmailLanguage.json!
 *
 * @codeCoverageIgnore
 *
 * @method static EmailLanguage nl() nl() Nederlands
 * @method static EmailLanguage en() en() Engels
 * @method static EmailLanguage de() de() Duits
 * @method static EmailLanguage pl() pl() Pools
 * @method static EmailLanguage ua() ua() Oekraïens
 * @method static EmailLanguage to() to() Roemeens
 * @method static EmailLanguage tr() tr() Turks
 * @method static EmailLanguage ar() ar() Arabisch
 * @method static EmailLanguage b1() b1() Eenvoudig Nederlands + Afbeeldingen

 * @property-read string $value
*/
final class EmailLanguage extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'EmailLanguage',
           'tsConst' => 'emailLanguage',
           'items' =>
          array (
            0 =>
            (object) array(
               'label' => 'Nederlands',
               'value' => 'nl',
               'name' => 'nl',
            ),
            1 =>
            (object) array(
               'label' => 'Engels',
               'value' => 'en',
               'name' => 'en',
            ),
            2 =>
            (object) array(
               'label' => 'Duits',
               'value' => 'de',
               'name' => 'de',
            ),
            3 =>
            (object) array(
               'label' => 'Pools',
               'value' => 'pl',
               'name' => 'pl',
            ),
            4 =>
            (object) array(
               'label' => 'Oekraïens',
               'value' => 'ua',
               'name' => 'ua',
            ),
            5 =>
            (object) array(
               'label' => 'Roemeens',
               'value' => 'to',
               'name' => 'to',
            ),
            6 =>
            (object) array(
               'label' => 'Turks',
               'value' => 'tr',
               'name' => 'tr',
            ),
            7 =>
            (object) array(
               'label' => 'Arabisch',
               'value' => 'ar',
               'name' => 'ar',
            ),
            8 =>
            (object) array(
               'label' => 'Eenvoudig Nederlands + Afbeeldingen',
               'value' => 'b1',
               'name' => 'b1',
            ),
          ),
        );
    }
}

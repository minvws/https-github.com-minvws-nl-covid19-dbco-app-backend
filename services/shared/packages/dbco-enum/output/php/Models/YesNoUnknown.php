<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 * Used when a boolean just doesn't cut it.
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit YesNoUnknown.json!
 *
 * @codeCoverageIgnore
 *
 * @method static YesNoUnknown yes() yes() Ja
 * @method static YesNoUnknown no() no() Nee
 * @method static YesNoUnknown unknown() unknown() Onbekend

 * @property-read string $value
*/
final class YesNoUnknown extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'YesNoUnknown',
           'tsConst' => 'yesNoUnknown',
           'description' => 'Used when a boolean just doesn\'t cut it.',
           'items' =>
          array (
            0 =>
            (object) array(
               'value' => 'yes',
               'label' => 'Ja',
               'name' => 'yes',
            ),
            1 =>
            (object) array(
               'value' => 'no',
               'label' => 'Nee',
               'name' => 'no',
            ),
            2 =>
            (object) array(
               'value' => 'unknown',
               'label' => 'Onbekend',
               'name' => 'unknown',
            ),
          ),
        );
    }
}

<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 *
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit ReinfectionType.json!
 *
 * @codeCoverageIgnore
 *
 * @method static ReinfectionType truth() truth() Ja, bewezen herbesmetting
 * @method static ReinfectionType maybe() maybe() Ja, mogelijke herbesmetting
 * @method static ReinfectionType no() no() Nee

 * @property-read string $value
*/
final class ReinfectionType extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'ReinfectionType',
           'tsConst' => 'reinfectionType',
           'items' =>
          array (
            0 =>
            (object) array(
               'label' => 'Ja, bewezen herbesmetting',
               'value' => 'truth',
               'name' => 'truth',
            ),
            1 =>
            (object) array(
               'label' => 'Ja, mogelijke herbesmetting',
               'value' => 'maybe',
               'name' => 'maybe',
            ),
            2 =>
            (object) array(
               'label' => 'Nee',
               'value' => 'no',
               'name' => 'no',
            ),
          ),
        );
    }
}

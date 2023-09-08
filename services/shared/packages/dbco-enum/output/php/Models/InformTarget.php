<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 * Defines who should be informed.
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit informTarget.json!
 *
 * @codeCoverageIgnore
 *
 * @method static InformTarget contact() contact() Contact
 * @method static InformTarget representative() representative() Vertegenwoordiger

 * @property-read string $value
*/
final class InformTarget extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'InformTarget',
           'tsConst' => 'informTarget',
           'description' => 'Defines who should be informed.',
           'items' =>
          array (
            0 =>
            (object) array(
               'value' => 'contact',
               'label' => 'Contact',
               'name' => 'contact',
            ),
            1 =>
            (object) array(
               'value' => 'representative',
               'label' => 'Vertegenwoordiger',
               'name' => 'representative',
            ),
          ),
        );
    }
}

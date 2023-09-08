<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 * BCO status for case
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit BCOStatus.json!
 *
 * @codeCoverageIgnore
 *
 * @method static BCOStatus draft() draft() Concept
 * @method static BCOStatus open() open() Open
 * @method static BCOStatus completed() completed() Controleren
 * @method static BCOStatus archived() archived() Gesloten
 * @method static BCOStatus unknown() unknown() Onbekend

 * @property-read string $value
*/
final class BCOStatus extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'BCOStatus',
           'tsConst' => 'bcoStatus',
           'description' => 'BCO status for case',
           'default' => 'draft',
           'items' =>
          array (
            0 =>
            (object) array(
               'label' => 'Concept',
               'value' => 'draft',
               'name' => 'draft',
            ),
            1 =>
            (object) array(
               'label' => 'Open',
               'value' => 'open',
               'name' => 'open',
            ),
            2 =>
            (object) array(
               'label' => 'Controleren',
               'value' => 'completed',
               'name' => 'completed',
            ),
            3 =>
            (object) array(
               'label' => 'Gesloten',
               'value' => 'archived',
               'name' => 'archived',
            ),
            4 =>
            (object) array(
               'label' => 'Onbekend',
               'value' => 'unknown',
               'name' => 'unknown',
            ),
          ),
        );
    }
}

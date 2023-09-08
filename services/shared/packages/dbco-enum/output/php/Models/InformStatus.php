<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 * Status for informing a contact
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit InformStatus.json!
 *
 * @codeCoverageIgnore
 *
 * @method static InformStatus uninformed() uninformed() Nog niet geïnformeerd
 * @method static InformStatus unreachable() unreachable() Geen gehoor
 * @method static InformStatus emailSent() emailSent() Alleen gemaild
 * @method static InformStatus informed() informed() Geïnformeerd

 * @property-read string $value
*/
final class InformStatus extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'InformStatus',
           'tsConst' => 'informStatus',
           'description' => 'Status for informing a contact',
           'default' => 'uninformed',
           'items' =>
          array (
            0 =>
            (object) array(
               'value' => 'uninformed',
               'label' => 'Nog niet geïnformeerd',
               'name' => 'uninformed',
            ),
            1 =>
            (object) array(
               'value' => 'unreachable',
               'label' => 'Geen gehoor',
               'name' => 'unreachable',
            ),
            2 =>
            (object) array(
               'value' => 'emailSent',
               'label' => 'Alleen gemaild',
               'name' => 'emailSent',
            ),
            3 =>
            (object) array(
               'value' => 'informed',
               'label' => 'Geïnformeerd',
               'name' => 'informed',
            ),
          ),
        );
    }
}

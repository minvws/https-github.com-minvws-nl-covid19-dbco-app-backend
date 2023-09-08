<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 * Status for a message
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit MessageStatus.json!
 *
 * @codeCoverageIgnore
 *
 * @method static MessageStatus draft() draft() Concept

 * @property-read string $value
*/
final class MessageStatus extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'MessageStatus',
           'tsConst' => 'messageStatus',
           'description' => 'Status for a message',
           'items' =>
          array (
            0 =>
            (object) array(
               'value' => 'draft',
               'label' => 'Concept',
               'name' => 'draft',
            ),
          ),
        );
    }
}

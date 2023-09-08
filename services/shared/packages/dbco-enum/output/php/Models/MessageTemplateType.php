<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 * Message template type
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit MessageTemplateType.json!
 *
 * @codeCoverageIgnore
 *
 * @method static MessageTemplateType deletedMessage() deletedMessage() Bericht verwijderd
 * @method static MessageTemplateType personalAdvice() personalAdvice() Informatiebrief versturen
 * @method static MessageTemplateType contactInfection() contactInfection() Informatiebrief versturen naar contact
 * @method static MessageTemplateType missedPhone() missedPhone() Verstuur een e-mail als telefonisch contact niet lukt

 * @property-read string $value
*/
final class MessageTemplateType extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'MessageTemplateType',
           'tsConst' => 'messageTemplateType',
           'description' => 'Message template type',
           'items' =>
          array (
            0 =>
            (object) array(
               'label' => 'Bericht verwijderd',
               'value' => 'deletedMessage',
               'name' => 'deletedMessage',
            ),
            1 =>
            (object) array(
               'label' => 'Informatiebrief versturen',
               'value' => 'personalAdvice',
               'name' => 'personalAdvice',
            ),
            2 =>
            (object) array(
               'label' => 'Informatiebrief versturen naar contact',
               'value' => 'contactInfection',
               'name' => 'contactInfection',
            ),
            3 =>
            (object) array(
               'label' => 'Verstuur een e-mail als telefonisch contact niet lukt',
               'value' => 'missedPhone',
               'name' => 'missedPhone',
            ),
          ),
        );
    }
}

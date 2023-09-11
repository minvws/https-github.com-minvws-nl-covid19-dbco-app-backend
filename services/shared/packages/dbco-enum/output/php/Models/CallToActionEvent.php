<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 * CallToAction events
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit CallToActionEvent.json!
 *
 * @codeCoverageIgnore
 *
 * @method static CallToActionEvent pickedUp() pickedUp() Opgepakt
 * @method static CallToActionEvent returned() returned() Teruggegeven
 * @method static CallToActionEvent note() note() Notitie geplaatst
 * @method static CallToActionEvent completed() completed() Afgerond
 * @method static CallToActionEvent expired() expired() Verlopen

 * @property-read string $value
*/
final class CallToActionEvent extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'CallToActionEvent',
           'tsConst' => 'callToActionEvent',
           'description' => 'CallToAction events',
           'items' =>
          array (
            0 =>
            (object) array(
               'label' => 'Opgepakt',
               'value' => 'picked-up',
               'name' => 'pickedUp',
            ),
            1 =>
            (object) array(
               'label' => 'Teruggegeven',
               'value' => 'returned',
               'name' => 'returned',
            ),
            2 =>
            (object) array(
               'label' => 'Notitie geplaatst',
               'value' => 'note',
               'name' => 'note',
            ),
            3 =>
            (object) array(
               'label' => 'Afgerond',
               'value' => 'completed',
               'name' => 'completed',
            ),
            4 =>
            (object) array(
               'label' => 'Verlopen',
               'value' => 'expired',
               'name' => 'expired',
            ),
          ),
        );
    }
}

<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 *
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit ContactsInformedStatus.json!
 *
 * @codeCoverageIgnore
 *
 * @method static ContactsInformedStatus notStarted() notStarted() Nog niet gestart
 * @method static ContactsInformedStatus notCompleted() notCompleted() Gestart, nog niet afgerond
 * @method static ContactsInformedStatus completedNotEveryoneReached() completedNotEveryoneReached() Afgerond, niet iedereen bereikt
 * @method static ContactsInformedStatus completed() completed() Afgerond: iedereen bereikt of n.v.t.

 * @property-read string $value
*/
final class ContactsInformedStatus extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'ContactsInformedStatus',
           'tsConst' => 'contactsInformedStatus',
           'items' =>
          array (
            0 =>
            (object) array(
               'label' => 'Nog niet gestart',
               'value' => 'not_started',
               'name' => 'notStarted',
            ),
            1 =>
            (object) array(
               'label' => 'Gestart, nog niet afgerond',
               'value' => 'not_completed',
               'name' => 'notCompleted',
            ),
            2 =>
            (object) array(
               'label' => 'Afgerond, niet iedereen bereikt',
               'value' => 'completed_not_everyone_reached',
               'name' => 'completedNotEveryoneReached',
            ),
            3 =>
            (object) array(
               'label' => 'Afgerond: iedereen bereikt of n.v.t.',
               'value' => 'completed',
               'name' => 'completed',
            ),
          ),
        );
    }
}

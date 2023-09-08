<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 *
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit CauseOfDeath.json!
 *
 * @codeCoverageIgnore
 *
 * @method static CauseOfDeath covid19() covid19() SARS-CoV-2
 * @method static CauseOfDeath other() other() Anders
 * @method static CauseOfDeath unknown() unknown() Onbekend

 * @property-read string $value
*/
final class CauseOfDeath extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'CauseOfDeath',
           'tsConst' => 'causeOfDeath',
           'items' =>
          array (
            0 =>
            (object) array(
               'label' => 'SARS-CoV-2',
               'value' => 'covid19',
               'name' => 'covid19',
            ),
            1 =>
            (object) array(
               'label' => 'Anders',
               'value' => 'other',
               'name' => 'other',
            ),
            2 =>
            (object) array(
               'label' => 'Onbekend',
               'value' => 'unknown',
               'name' => 'unknown',
            ),
          ),
        );
    }
}

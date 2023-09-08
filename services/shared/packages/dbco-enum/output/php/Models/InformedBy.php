<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 * Defines who the task is in contact with for contact research.
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit InformedBy.json!
 *
 * @codeCoverageIgnore
 *
 * @method static InformedBy index() index() Index
 * @method static InformedBy staff() staff() GGD

 * @property-read string $value
*/
final class InformedBy extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'InformedBy',
           'tsConst' => 'informedBy',
           'description' => 'Defines who the task is in contact with for contact research.',
           'items' =>
          array (
            0 =>
            (object) array(
               'value' => 'index',
               'label' => 'Index',
               'name' => 'index',
            ),
            1 =>
            (object) array(
               'value' => 'staff',
               'label' => 'GGD',
               'name' => 'staff',
            ),
          ),
        );
    }
}

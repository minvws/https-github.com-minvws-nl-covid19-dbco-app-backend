<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 *
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit TaskGroup.json!
 *
 * @codeCoverageIgnore
 *
 * @method static TaskGroup contact() contact() contact
 * @method static TaskGroup positiveSource() positiveSource() positiveSource
 * @method static TaskGroup symptomaticSource() symptomaticSource() symptomaticSource

 * @property-read string $value
*/
final class TaskGroup extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'TaskGroup',
           'tsConst' => 'taskGroup',
           'items' =>
          array (
            0 =>
            (object) array(
               'value' => 'contact',
               'label' => 'contact',
               'name' => 'contact',
            ),
            1 =>
            (object) array(
               'name' => 'positiveSource',
               'value' => 'positivesource',
               'label' => 'positiveSource',
            ),
            2 =>
            (object) array(
               'name' => 'symptomaticSource',
               'value' => 'symptomaticsource',
               'label' => 'symptomaticSource',
            ),
          ),
        );
    }
}

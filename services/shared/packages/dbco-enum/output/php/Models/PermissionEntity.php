<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 * Entity for permission
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit PermissionEntity.json!
 *
 * @codeCoverageIgnore
 *
 * @method static PermissionEntity case() case() case
 * @method static PermissionEntity task() task() task
 * @method static PermissionEntity context() context() context
 * @method static PermissionEntity place() place() place
 * @method static PermissionEntity intake() intake() intake
 * @method static PermissionEntity organisation() organisation() organisation

 * @property-read string $value
*/
final class PermissionEntity extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'PermissionEntity',
           'description' => 'Entity for permission',
           'items' =>
          array (
            0 =>
            (object) array(
               'label' => 'case',
               'value' => 'case',
               'name' => 'case',
            ),
            1 =>
            (object) array(
               'label' => 'task',
               'value' => 'task',
               'name' => 'task',
            ),
            2 =>
            (object) array(
               'label' => 'context',
               'value' => 'context',
               'name' => 'context',
            ),
            3 =>
            (object) array(
               'label' => 'place',
               'value' => 'place',
               'name' => 'place',
            ),
            4 =>
            (object) array(
               'label' => 'intake',
               'value' => 'intake',
               'name' => 'intake',
            ),
            5 =>
            (object) array(
               'label' => 'organisation',
               'value' => 'organisation',
               'name' => 'organisation',
            ),
          ),
        );
    }
}

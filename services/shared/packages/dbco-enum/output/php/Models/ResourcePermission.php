<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 * Resource Permission
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit ResourcePermission.json!
 *
 * @codeCoverageIgnore
 *
 * @method static ResourcePermission edit() edit() Edit
 * @method static ResourcePermission view() view() View

 * @property-read string $value
*/
final class ResourcePermission extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'ResourcePermission',
           'tsConst' => 'resourcePermission',
           'description' => 'Resource Permission',
           'default' => null,
           'items' =>
          array (
            0 =>
            (object) array(
               'label' => 'Edit',
               'value' => 'edit',
               'name' => 'edit',
            ),
            1 =>
            (object) array(
               'label' => 'View',
               'value' => 'view',
               'name' => 'view',
            ),
          ),
        );
    }
}

<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 *
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit DateOperationMutation.json!
 *
 * @codeCoverageIgnore
 *
 * @method static DateOperationMutation add() add() add
 * @method static DateOperationMutation sub() sub() sub

 * @property-read string $value
*/
final class DateOperationMutation extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'DateOperationMutation',
           'tsConst' => 'dateOperationMutation',
           'items' =>
          array (
            0 =>
            (object) array(
               'label' => 'add',
               'value' => 'add',
               'name' => 'add',
            ),
            1 =>
            (object) array(
               'label' => 'sub',
               'value' => 'sub',
               'name' => 'sub',
            ),
          ),
        );
    }
}

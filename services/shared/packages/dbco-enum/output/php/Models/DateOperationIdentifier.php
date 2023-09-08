<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 *
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit DateOperationIdentifier.json!
 *
 * @codeCoverageIgnore
 *
 * @method static DateOperationIdentifier default() default() default
 * @method static DateOperationIdentifier min() min() min
 * @method static DateOperationIdentifier max() max() max

 * @property-read string $value
*/
final class DateOperationIdentifier extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'DateOperationIdentifier',
           'tsConst' => 'dateOperationIdentifier',
           'default' => 'default',
           'items' =>
          array (
            0 =>
            (object) array(
               'label' => 'default',
               'value' => 'default',
               'name' => 'default',
            ),
            1 =>
            (object) array(
               'label' => 'min',
               'value' => 'min',
               'name' => 'min',
            ),
            2 =>
            (object) array(
               'label' => 'max',
               'value' => 'max',
               'name' => 'max',
            ),
          ),
        );
    }
}

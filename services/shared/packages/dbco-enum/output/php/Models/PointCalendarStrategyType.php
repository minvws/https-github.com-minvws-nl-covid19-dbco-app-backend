<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 *
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit PointCalendarStrategyType.json!
 *
 * @codeCoverageIgnore
 *
 * @method static PointCalendarStrategyType fixedStrategy() fixedStrategy() Vast
 * @method static PointCalendarStrategyType flexStrategy() flexStrategy() Flexibel met minimum en maximum

 * @property-read string $value
*/
final class PointCalendarStrategyType extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'PointCalendarStrategyType',
           'tsConst' => 'pointCalendarStrategyType',
           'default' => 'pointFixedStrategy',
           'items' =>
          array (
            0 =>
            (object) array(
               'label' => 'Vast',
               'value' => 'pointFixedStrategy',
               'name' => 'fixedStrategy',
            ),
            1 =>
            (object) array(
               'label' => 'Flexibel met minimum en maximum',
               'value' => 'pointFlexStrategy',
               'name' => 'flexStrategy',
            ),
          ),
        );
    }
}

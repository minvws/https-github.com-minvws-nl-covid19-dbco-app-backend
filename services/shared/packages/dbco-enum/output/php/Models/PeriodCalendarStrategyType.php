<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 *
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit PeriodCalendarStrategyType.json!
 *
 * @codeCoverageIgnore
 *
 * @method static PeriodCalendarStrategyType fixedStrategy() fixedStrategy() Vast
 * @method static PeriodCalendarStrategyType flexStrategy() flexStrategy() Flexibel met minimum en maximum
 * @method static PeriodCalendarStrategyType fadedStrategy() fadedStrategy() Datum onbekend

 * @property-read string $value
*/
final class PeriodCalendarStrategyType extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'PeriodCalendarStrategyType',
           'tsConst' => 'periodCalendarStrategyType',
           'default' => 'periodFixedStrategy',
           'items' =>
          array (
            0 =>
            (object) array(
               'label' => 'Vast',
               'value' => 'periodFixedStrategy',
               'name' => 'fixedStrategy',
            ),
            1 =>
            (object) array(
               'label' => 'Flexibel met minimum en maximum',
               'value' => 'periodFlexStrategy',
               'name' => 'flexStrategy',
            ),
            2 =>
            (object) array(
               'label' => 'Datum onbekend',
               'value' => 'periodFadedStrategy',
               'name' => 'fadedStrategy',
            ),
          ),
        );
    }
}

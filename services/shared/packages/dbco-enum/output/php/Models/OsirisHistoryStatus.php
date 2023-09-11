<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 * Osiris history status
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit OsirisHistoryStatus.json!
 *
 * @codeCoverageIgnore
 *
 * @method static OsirisHistoryStatus success() success() Success
 * @method static OsirisHistoryStatus failed() failed() Failed
 * @method static OsirisHistoryStatus validation() validation() Validation
 * @method static OsirisHistoryStatus blocked() blocked() Blocked

 * @property-read string $value
*/
final class OsirisHistoryStatus extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'OsirisHistoryStatus',
           'tsConst' => 'osirisHistoryStatus',
           'description' => 'Osiris history status',
           'default' => 'success',
           'items' =>
          array (
            0 =>
            (object) array(
               'label' => 'Success',
               'value' => 'success',
               'name' => 'success',
            ),
            1 =>
            (object) array(
               'label' => 'Failed',
               'value' => 'failed',
               'name' => 'failed',
            ),
            2 =>
            (object) array(
               'label' => 'Validation',
               'value' => 'validation',
               'name' => 'validation',
            ),
            3 =>
            (object) array(
               'label' => 'Blocked',
               'value' => 'blocked',
               'name' => 'blocked',
            ),
          ),
        );
    }
}

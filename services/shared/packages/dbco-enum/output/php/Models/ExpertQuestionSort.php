<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 *
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit ExpertQuestionSort.json!
 *
 * @codeCoverageIgnore
 *
 * @method static ExpertQuestionSort createdAt() createdAt() createdAt
 * @method static ExpertQuestionSort status() status() status

 * @property-read string $value
*/
final class ExpertQuestionSort extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'ExpertQuestionSort',
           'tsConst' => 'expertQuestionSort',
           'items' =>
          array (
            0 =>
            (object) array(
               'value' => 'createdAt',
               'label' => 'createdAt',
               'name' => 'createdAt',
            ),
            1 =>
            (object) array(
               'value' => 'status',
               'label' => 'status',
               'name' => 'status',
            ),
          ),
        );
    }
}

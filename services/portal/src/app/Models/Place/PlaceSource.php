<?php

declare(strict_types=1);

namespace App\Models\Place;

use MinVWS\DBCO\Enum\Models\Enum;

/**
 * @method static PlaceSource manual() manual() Assigned
 * @method static PlaceSource external() external() Queued
 *
 * @codeCoverageIgnore
 */
final class PlaceSource extends Enum
{
    protected static function enumSchema(): object
    {
        return (object) [
            'items' => [
                (object) ['value' => 'manual', 'label' => 'manual'],
                (object) ['value' => 'external', 'label' => 'external'],
            ],
        ];
    }
}

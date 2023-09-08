<?php

declare(strict_types=1);

namespace App\Models\PlannerCase;

use MinVWS\DBCO\Enum\Models\Enum;

use function array_filter;

/**
 * @method static PlannerView unassigned() unassigned() Unassigned
 * @method static PlannerView assigned() assigned() Assigned
 * @method static PlannerView outsourced() outsourced() Outsourced
 * @method static PlannerView queued() queued() Queued
 * @method static PlannerView archived() archived() Archived
 * @method static PlannerView completed() completed() Completed
 * @method static PlannerView unknown() unknown() Unknown
 *
 * @property-read bool $caseList
 */
final class PlannerView extends Enum
{
    protected static function enumSchema(): object
    {
        return (object) [
            'properties' => (object) [
                'caseList' => (object) [
                    'type' => 'bool',
                    'phpType' => 'bool',
                ],
            ],
            'items' => [
                (object) [
                    'value' => 'unassigned',
                    'label' => 'unassigned',
                    'caseList' => true,
                ],
                (object) [
                    'value' => 'assigned',
                    'label' => 'assigned',
                    'caseList' => true,
                ],
                (object) [
                    'value' => 'outsourced',
                    'label' => 'outsourced',
                    'caseList' => false,
                ],
                (object) [
                    'value' => 'queued',
                    'label' => 'queued',
                    'caseList' => false,
                ],
                (object) [
                    'value' => 'archived',
                    'label' => 'archived',
                    'caseList' => true,
                ],
                (object) [
                    'value' => 'completed',
                    'label' => 'completed',
                    'caseList' => true,
                ],
                (object) [
                    'value' => 'unknown',
                    'label' => 'unknown',
                    'caseList' => false,
                ],
            ],
        ];
    }

    /**
     * @return array<PlannerView>
     */
    public static function onlyValuesForCaseList(): array
    {
        return array_filter(self::all(), static fn (self $enum) => $enum->caseList);
    }
}

<?php

declare(strict_types=1);

namespace App\Models\PlannerCase;

use MinVWS\DBCO\Enum\Models\Enum;

/**
 * @method static PlannerSort dateOfTest() dateOfTest()
 * @method static PlannerSort updatedAt() updatedAt()
 * @method static PlannerSort createdAt() createdAt()
 * @method static PlannerSort contactsCount() contactsCount()
 * @method static PlannerSort caseStatus() caseStatus()
 * @method static PlannerSort priority() priority()
 */
final class PlannerSort extends Enum
{
    protected static function enumSchema(): object
    {
        return (object) [
            'items' => [
                (object) ['value' => 'dateOfTest', 'label' => 'dateOfTest'],
                (object) ['value' => 'updatedAt', 'label' => 'updatedAt'],
                (object) ['value' => 'createdAt', 'label' => 'createdAt'],
                (object) ['value' => 'contactsCount', 'label' => 'contactsCount'],
                (object) ['value' => 'caseStatus', 'label' => 'caseStatus'],
                (object) ['value' => 'priority', 'label' => 'priority'],
            ],
        ];
    }
}

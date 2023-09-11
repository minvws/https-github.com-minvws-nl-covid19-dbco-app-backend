<?php

declare(strict_types=1);

namespace App\Models\ExpertQuestion;

use Illuminate\Support\Collection;
use MinVWS\DBCO\Enum\Models\ExpertQuestionType;

use function collect;
use function in_array;

class ExpertQuestionTypeRoleMap
{
    /**
     * @param array<int, string> $roles
     *
     * @return array<string, ExpertQuestionType>
     */
    public static function getExpertQuestionTypesForRoles(array $roles): array
    {
        return self::getExpertQuestionTypeRoleMap()
            ->filter(static fn(ExpertQuestionType $type, $mappedRole) => in_array($mappedRole, $roles, true))
            ->all();
    }

    /**
     * @return Collection<string, ExpertQuestionType>
     */
    private static function getExpertQuestionTypeRoleMap(): Collection
    {
        return collect([
            'medical_supervisor' => ExpertQuestionType::medicalSupervision(),
            'medical_supervisor_nationwide' => ExpertQuestionType::medicalSupervision(),
            'conversation_coach' => ExpertQuestionType::conversationCoach(),
            'conversation_coach_nationwide' => ExpertQuestionType::conversationCoach(),
        ]);
    }
}

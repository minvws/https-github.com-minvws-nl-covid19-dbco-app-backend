<?php

declare(strict_types=1);

namespace App\Http\Responses\ExpertQuestion;

use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentUser;
use App\Models\Eloquent\ExpertQuestion;
use MinVWS\Codable\EncodableDecorator;
use MinVWS\Codable\EncodingContainer;

class ExpertQuestionEncoder implements EncodableDecorator
{
    public function encode(object $value, EncodingContainer $container): void
    {
        if (!$value instanceof ExpertQuestion) {
            return;
        }

        $container->uuid = $value->uuid;
        $container->caseUuid = $value->case_uuid;
        $container->user = $this->encodeUser($value->user);
        $container->assignedUser = $this->encodeUser($value->assignedUser);
        $container->answer = $this->encodeAnswer($value);
        $container->type = $value->type->value;
        $container->subject = $value->subject;
        $container->phone = $value->phone;
        $container->question = $value->question;
        $container->createdAt = $value->created_at;
        $container->updatedAt = $value->updated_at;
        $container->caseOrganisationName = $this->encodeCaseOrganisationName($value->case);
    }

    protected function encodeAnswer(ExpertQuestion $expertQuestion): ?array
    {
        return $expertQuestion->hasAnswer() ? [
            'value' => $expertQuestion->answer->answer,
            'answeredBy' => $this->encodeUser($expertQuestion->answer->answeredBy),
            'createdAt' => $expertQuestion->answer->created_at,
        ] : null;
    }

    protected function encodeUser(?EloquentUser $user): ?array
    {
        return $user ? [
            'name' => $user->name,
            'roles' => $user->getRolesArray(),
            'uuid' => $user->uuid,
        ] : null;
    }

    protected function encodeCaseOrganisationName(?EloquentCase $case): ?string
    {
        return $case?->organisation->name;
    }
}

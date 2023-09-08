<?php

declare(strict_types=1);

namespace App\Http\Responses\CallToAction;

use App\Models\Eloquent\CallToAction;
use App\Repositories\UserRepository;
use Carbon\CarbonImmutable;
use MinVWS\Codable\EncodableDecorator;
use MinVWS\Codable\EncodingContainer;

use function explode;

class CallToActionListEncoder implements EncodableDecorator
{
    public function __construct(
        private readonly UserRepository $userRepository,
    ) {
    }

    public function encode(object $value, EncodingContainer $container): void
    {
        if (!$value instanceof CallToAction) {
            return;
        }

        $chore = $value->chore;

        $container->uuid = $value->uuid;
        $container->subject = $value->subject;
        $container->description = $value->description;
        $container->organisationUuid = $chore->organisation_uuid;
        $container->resource = [
            'uuid' => $chore->resource_id,
            'type' => $chore->resource_type,
        ];

        if ($value->created_by) {
            $user = $this->userRepository->getByUuid($value->created_by);
            $userRoles = $user?->roles;
            $container->createdBy = [
                'name' => $user?->name,
                'roles' => $userRoles !== null ? explode(',', $userRoles) : null,
                'uuid' => $value->created_by,
            ];
        }

        $container->createdAt = CarbonImmutable::parse($chore->created_at);
        $container->expiresAt = CarbonImmutable::parse($chore->expires_at);
        $container->assignedUserUuid = $chore->assignment?->user_uuid ?? null;
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Responses\CaseLock;

use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentCaseLock;
use App\Models\Eloquent\EloquentUser;
use MinVWS\Codable\EncodableDecorator;
use MinVWS\Codable\EncodingContainer;

class CaseLockEncoder implements EncodableDecorator
{
    public function encode(object $value, EncodingContainer $container): void
    {
        if (!$value instanceof EloquentCaseLock) {
            return;
        }

        $container->uuid = $value->uuid;
        $container->user = $this->encodeUser($value->user);
        $container->case = $this->encodeCase($value->case);
        $container->locked_at = $value->locked_at;
        $container->created_at = $value->created_at;
    }

    protected function encodeUser(EloquentUser $user): array
    {
        return [
            'name' => $user->name,
            'organisation' => $user->getOrganisation()?->name,
        ];
    }

    protected function encodeCase(EloquentCase $case): array
    {
        return [
            'uuid' => $case->uuid,
            'case_id' => $case->case_id,
        ];
    }
}

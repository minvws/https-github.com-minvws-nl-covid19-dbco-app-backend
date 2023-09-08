<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\CallToAction;

use App\Http\Requests\Api\ApiRequest;

/**
 * @property string $subject
 * @property string $organisationUuid
 * @property string $resourceUuid
 * @property string $resourceType
 * @property string $resourcePermission
 * @property ?string $expiresAt
 */
class PickupRequest extends ApiRequest
{
    public const FIELD_EXPIRES_AT = 'expires_at';

    public function rules(): array
    {
        return [
            self::FIELD_EXPIRES_AT => 'date|nullable',
        ];
    }
}

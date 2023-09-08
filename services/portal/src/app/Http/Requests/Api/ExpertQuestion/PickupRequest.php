<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\ExpertQuestion;

use App\Http\Requests\Api\ApiRequest;

/**
 * @property string $assigned_user_uuid
 */
class PickupRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'assigned_user_uuid' => [
                'required',
                'string',
            ],
        ];
    }

    public function getAssignUserUuid(): string
    {
        return $this->assigned_user_uuid;
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\CallToAction;

use App\Http\Requests\Api\ApiRequest;
use MinVWS\DBCO\Enum\Models\ChoreResourceType;
use MinVWS\DBCO\Enum\Models\ResourcePermission;

use function implode;

/**
 * @property string $subject
 * @property string $description
 * @property string $organisation_uuid
 * @property string $resource_uuid
 * @property string $resource_type
 * @property string $resource_permission
 * @property ?string $expires_at
 */
class CreateRequest extends ApiRequest
{
    public function rules(): array
    {
        $allowedPermissions = [
            ResourcePermission::edit()->value,
            ResourcePermission::view()->value,
        ];

        return [
            'subject' => 'required|string',
            'description' => 'required|string|max:5000',
            'organisation_uuid' => 'nullable|string|exists:organisation,uuid',
            'resource_uuid' => 'required|string',
            'resource_type' => 'required|string|in:' . implode(',', ChoreResourceType::allValues()),
            'resource_permission' => 'required|string|in:' . implode(',', $allowedPermissions),
            'expires_at' => 'string|nullable',
        ];
    }
}

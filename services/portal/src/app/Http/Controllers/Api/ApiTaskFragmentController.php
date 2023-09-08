<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Services\TaskFragmentService;
use MinVWS\Audit\Models\AuditObject;

class ApiTaskFragmentController extends ApiAbstractFragmentController
{
    public function __construct(
        TaskFragmentService $taskFragmentService,
    ) {
        parent::__construct($taskFragmentService);
    }

    /**
     * @inheritDoc
     */
    protected function objectForAuditEvent(string $ownerUuid, array $fragmentNames): AuditObject
    {
        return AuditObject::create('task', $ownerUuid)->detail('fragments', $fragmentNames);
    }
}

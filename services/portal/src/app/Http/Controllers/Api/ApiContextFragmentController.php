<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Services\ContextFragmentService;
use MinVWS\Audit\Models\AuditObject;

class ApiContextFragmentController extends ApiAbstractFragmentController
{
    public function __construct(
        ContextFragmentService $contextFragmentService,
    ) {
        parent::__construct($contextFragmentService);
    }

    /**
     * @inheritDoc
     */
    protected function objectForAuditEvent(string $ownerUuid, array $fragmentNames): AuditObject
    {
        return AuditObject::create('context', $ownerUuid)
            ->detail('fragments', $fragmentNames);
    }
}

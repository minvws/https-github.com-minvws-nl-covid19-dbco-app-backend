<?php

namespace MinVWS\Audit\Repositories;

use MinVWS\Audit\Models\AuditEvent;

interface AuditRepository
{
    public function registerEvent(AuditEvent $event): void;
}

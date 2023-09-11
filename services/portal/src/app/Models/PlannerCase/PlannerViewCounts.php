<?php

declare(strict_types=1);

namespace App\Models\PlannerCase;

use MinVWS\Codable\Encodable;
use MinVWS\Codable\EncodingContainer;

class PlannerViewCounts implements Encodable
{
    public ?int $intakeList = null;
    public ?int $unassigned = null;
    public ?int $assigned = null;
    public ?int $outsourced = null;
    public ?int $queued = null;
    public ?int $completed = null;
    public ?int $archived = null;

    public function encode(EncodingContainer $container): void
    {
        foreach (['intakeList', 'unassigned', 'assigned', 'outsourced', 'queued', 'completed', 'archived'] as $field) {
            if ($this->$field !== null) {
                $container->$field = $this->$field;
            }
        }
    }
}

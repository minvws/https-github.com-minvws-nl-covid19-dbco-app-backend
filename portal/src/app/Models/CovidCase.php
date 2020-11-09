<?php

namespace App\Models;

use Jenssegers\Date\Date;

class CovidCase
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_OPEN = 'open';
    public const STATUS_CLOSED = 'closed';

    public string $uuid;

    public string $owner;
    public ?string $assignedUuid;
    public string $organisationUuid;

    public string $status;

    public ?string $name;

    public ?string $caseId;

    public ?Date $dateOfSymptomOnset;

    public ?Date $updatedAt;

    public array $tasks = array();

}

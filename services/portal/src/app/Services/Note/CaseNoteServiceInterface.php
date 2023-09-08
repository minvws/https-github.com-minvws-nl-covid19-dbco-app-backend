<?php

declare(strict_types=1);

namespace App\Services\Note;

use App\Models\Eloquent\EloquentUser;
use App\Models\Eloquent\Note;
use Illuminate\Support\Collection;
use MinVWS\DBCO\Enum\Models\CaseNoteType;

interface CaseNoteServiceInterface
{
    public function createNote(string $caseUuid, CaseNoteType $type, string $note, EloquentUser $user): ?Note;

    public function getNotes(string $caseUuid): Collection;
}

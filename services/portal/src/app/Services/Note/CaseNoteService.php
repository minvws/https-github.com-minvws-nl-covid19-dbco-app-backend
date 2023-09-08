<?php

declare(strict_types=1);

namespace App\Services\Note;

use App\Models\Eloquent\Contracts\TimelineInterface;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentUser;
use App\Models\Eloquent\Note;
use App\Repositories\NoteRepository;
use App\Services\Timeline\TimelineService;
use Illuminate\Support\Collection;
use MinVWS\DBCO\Enum\Models\CaseNoteType;

class CaseNoteService implements CaseNoteServiceInterface
{
    private NoteRepository $noteRepository;
    private TimelineService $timelineService;

    public function __construct(NoteRepository $noteRepository, TimelineService $timelineService)
    {
        $this->noteRepository = $noteRepository;
        $this->timelineService = $timelineService;
    }

    public function createNote(string $caseUuid, CaseNoteType $type, string $note, EloquentUser $user): ?Note
    {
        $case = EloquentCase::findOrFail($caseUuid);
        $note = $this->noteRepository->createNote($case, $type, $note, $user);

        if ($note instanceof TimelineInterface) {
            $this->timelineService->addToTimeline($note);
        }

        return $note;
    }

    public function getNotes(string $caseUuid): Collection
    {
        $case = EloquentCase::findOrFail($caseUuid);

        return $this->noteRepository->getNotes($case);
    }
}

<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Models\Eloquent\Timeline;
use App\Services\Note\CaseNoteService;
use Illuminate\Support\Str;
use MinVWS\DBCO\Enum\Models\CaseNoteType;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function app;

#[Group('note')]
#[Group('case-note')]
class CaseNoteServiceTest extends FeatureTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createUser();
        $this->case = $this->createCaseForUser($this->user);

        $this->caseNoteService = app()->get(CaseNoteService::class);
    }

    public function testCreateCaseNoteSuccessful(): void
    {
        $this->be($this->user);
        $note = Str::random(500);
        $noteType = CaseNoteType::caseNote();

        $caseNote = $this->caseNoteService->createNote($this->case->uuid, $noteType, $note, $this->user);

        $this->assertNotNull($caseNote);
        $this->assertEquals($noteType, $caseNote->type);
    }

    public function testGetCaseNotesWithResults(): void
    {
        $this->testCreateCaseNoteSuccessful();
        $caseNotes = $this->caseNoteService->getNotes($this->case->uuid);

        $this->assertCount(1, $caseNotes);
    }

    public function testGetCaseNotesWithoutResults(): void
    {
        $caseNotes = $this->caseNoteService->getNotes($this->case->uuid);

        $this->assertCount(0, $caseNotes);
    }

    public function testNoteCreatesTimelineEntry(): void
    {
        $this->be($this->user);
        $note = Str::random(500);
        $noteType = CaseNoteType::caseNote();

        $caseNote = $this->caseNoteService->createNote($this->case->uuid, $noteType, $note, $this->user);

        $this->assertNotNull($caseNote);
        $this->assertEquals($noteType, $caseNote->type);


        $this->assertDatabaseHas(Timeline::class, [
            'timelineable_id' => $caseNote->uuid,
            'timelineable_type' => 'note',
        ]);
    }
}

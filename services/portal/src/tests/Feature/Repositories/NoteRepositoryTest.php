<?php

declare(strict_types=1);

namespace Tests\Feature\Repositories;

use App\Repositories\NoteRepository;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use MinVWS\DBCO\Encryption\Security\EncryptionHelper;
use MinVWS\DBCO\Enum\Models\CaseNoteType;
use Tests\Feature\FeatureTestCase;

class NoteRepositoryTest extends FeatureTestCase
{
    private NoteRepository $noteRepository;

    private EncryptionHelper $encryptionHelper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->noteRepository = $this->app->get(NoteRepository::class);

        $this->encryptionHelper = $this->app->make('\MinVWS\DBCO\Encryption\Security\EncryptionHelper');
    }

    public function testCreateNoteSuccessful(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $note = Str::random(500);

        $this->assertDatabaseMissing('note', [
            'case_uuid' => $case->uuid,
        ]);

        $this->noteRepository->createNote($case, CaseNoteType::caseNote(), $note, $user);

        $this->assertDatabaseHas('note', [
            'case_uuid' => $case->uuid,
        ]);
    }

    public function testCreatedNoteIsEncrypted(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $note = Str::random(500);

        $createdNote = $this->noteRepository->createNote($case, CaseNoteType::caseNote(), $note, $user);

        $rs = DB::table('note')->where('uuid', $createdNote->uuid)->sole();
        $this->assertStringContainsString('ciphertext', $rs->note);
    }

    public function testCreatedNoteHasDateOfCase(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user, ['created_at' => new DateTimeImmutable('2021-01-01 13:01:02')]);
        $note = Str::random(500);

        $createdNote = $this->noteRepository->createNote($case, CaseNoteType::caseNote(), $note, $user);

        $this->assertSame($createdNote->case_created_at->format('Y-m-d H:i:s'), '2021-01-01 13:01:02');
    }

    public function testGetNotesSuccessful(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $note = Str::random(500);

        $notesBeforeInserting = $this->noteRepository->getNotes($case);
        $this->assertCount(0, $notesBeforeInserting);

        $this->noteRepository->createNote($case, CaseNoteType::caseNote(), $note, $user);

        $notesAfterOneInsert = $this->noteRepository->getNotes($case);
        $this->assertCount(1, $notesAfterOneInsert);

        $this->noteRepository->createNote($case, CaseNoteType::caseNote(), $note, $user);
        $this->noteRepository->createNote($case, CaseNoteType::caseNote(), $note, $user);
        $this->noteRepository->createNote($case, CaseNoteType::caseNote(), $note, $user);
        $this->noteRepository->createNote($case, CaseNoteType::caseNote(), $note, $user);

        $notesAfterFiveInserts = $this->noteRepository->getNotes($case);
        $this->assertCount(5, $notesAfterFiveInserts);
    }

    public function testGetNotesScopeSuccessful(): void
    {
        $user = $this->createUser();
        $caseOne = $this->createCaseForUser($user);
        $caseTwo = $this->createCaseForUser($user);

        $note = Str::random(500);

        $this->noteRepository->createNote($caseOne, CaseNoteType::caseNote(), $note, $user);

        $notesCaseOne = $this->noteRepository->getNotes($caseOne);
        $this->assertCount(1, $notesCaseOne);

        $notesCaseTwo = $this->noteRepository->getNotes($caseTwo);
        $this->assertCount(0, $notesCaseTwo);
    }
}

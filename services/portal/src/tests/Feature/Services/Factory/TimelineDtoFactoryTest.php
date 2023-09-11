<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Factory;

use App\Repositories\ChoreRepository;
use App\Services\AuthenticationService;
use App\Services\Factory\TimelineDtoFactory;
use App\Services\Timeline\AssignmentChangeBuilder;
use App\Services\Timeline\AssignmentMessageService;
use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Support\Collection;
use MinVWS\DBCO\Enum\Models\CaseNoteType;
use Tests\Feature\FeatureTestCase;

class TimelineDtoFactoryTest extends FeatureTestCase
{
    private TimelineDtoFactory $timelineDtoFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->timelineDtoFactory = new TimelineDtoFactory(
            $this->app->get(AssignmentChangeBuilder::class),
            $this->app->get(AssignmentMessageService::class),
            $this->app->get(AuthenticationService::class),
            $this->app->get(ChoreRepository::class),
        );
    }

    public function testNoteTimeIsShownInEuropeAmsterdam(): void
    {
        $case = $this->createCase();
        $note = $this->createNoteForCase($case, [
            'created_at' => new DateTimeImmutable('2021-01-01 14:00:00', new DateTimeZone('utc')),
            'type' => CaseNoteType::caseDirectlyArchived(),
        ]);

        $result = $this->timelineDtoFactory->fromNote($note);
        $this->assertSame('1 januari 2021  om  15:00 • Case direct gesloten', $result->getTime());
    }

    public function testAssignmentTimeIsShownInEuropeAmsterdam(): void
    {
        $case = $this->createCase();
        $assignment = $this->createAssignmentHistoryForCase($case, [
            'assigned_at' => new DateTimeImmutable('2021-01-01 14:00:00', new DateTimeZone('utc')),
        ]);

        $user = $this->createUser();
        $this->be($user);

        $result = $this->timelineDtoFactory->fromCaseAssignmentHistory($assignment, new Collection());
        $this->assertSame('1 januari 2021  om  15:00', $result->getTime());
    }

    public function testFromConflictingCaseAssignmentHistory(): void
    {
        $case = $this->createCase();
        $assignment = $this->createAssignmentHistoryForCase($case, [
            'assigned_at' => new DateTimeImmutable('2021-01-01 14:00:00', new DateTimeZone('utc')),
        ]);

        $user = $this->createUser();
        $this->be($user);

        $result = $this->timelineDtoFactory->fromConflictingCaseAssignmentHistory($assignment, new Collection());
        $this->assertStringStartsWith('Toegewezen aan', $result->getNote());
    }

    public function testTimelineHistoryWithAssigner(): void
    {
        $assigner = $this->createUser();
        $timelines = new Collection();
        $case = $this->createCase();
        $assignmentHistory = $this->createAssignmentHistoryForCase($case, ['assigned_by' => $assigner]);

        $this->be($this->createUser());
        $dto = $this->timelineDtoFactory->fromCaseAssignmentHistory($assignmentHistory, $timelines);

        $this->assertStringContainsString($assigner->name, $dto->getNote());
    }
}

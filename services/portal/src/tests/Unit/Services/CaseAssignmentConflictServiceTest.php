<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Dto\TimelineDto;
use App\Models\Eloquent\CaseAssignmentHistory;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\Timeline;
use App\Repositories\DbCaseAssignmentHistoryRepository;
use App\Services\CaseAssignmentConflictService;
use App\Services\Factory\TimelineDtoFactory;
use App\Services\Timeline\TimelineService;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

use function app;
use function json_decode;

class CaseAssignmentConflictServiceTest extends TestCase
{
    #[Group('case-assignment')]
    public function testItFindsLatestConflictingCaseAssignmentsForListOfCovidCaseUuids(): void
    {
        $covidCaseUuids = ['1', '2', '3'];
        $staleSince = '2022-02-16 09:59:59';

        $this->mock(
            DbCaseAssignmentHistoryRepository::class,
            static function (MockInterface $mock) use ($covidCaseUuids, $staleSince): void {
                $mock->expects('findByCaseUuidAssignedSince')
                    ->with($covidCaseUuids, $staleSince)
                    ->andReturn(new EloquentCollection([
                        new CaseAssignmentHistory([
                            'covidcase_uuid' => '1',
                            'assigned_at' => '2022-02-16 09:59:41',
                        ]),
                        new CaseAssignmentHistory([
                            'covidcase_uuid' => '2',
                            'assigned_user_uuid' => '42',
                            'assigned_at' => '2022-02-16 10:00:01',
                        ]),
                        new CaseAssignmentHistory([
                            'covidcase_uuid' => '2',
                            'assigned_user_uuid' => null,
                            'assigned_at' => '2022-02-16 10:00:00',
                        ]),
                    ]));
            },
        );

        $actual = app(CaseAssignmentConflictService::class)->findConflictingAssignments($covidCaseUuids, $staleSince);

        $this->assertCount(2, $actual);
        $this->assertEquals('1', $actual->first()->getCaseUuid());
        $this->assertEquals('2', $actual->last()->getCaseUuid());
        $this->assertTrue($actual->last()->hasUser());
    }

    public function testItCreatesAnUpdateAssignmentResponseForASingleCaseWithoutConflict(): void
    {
        $caseUuid = '3';

        $caseAssignmentConflictService = $this->app->get(CaseAssignmentConflictService::class);

        $actual = $caseAssignmentConflictService->createUpdateAssignmentResponse([$caseUuid], new Collection());

        $content = json_decode($actual->getContent());

        $this->assertEquals(Response::HTTP_OK, $actual->getStatusCode());
        $this->assertIsArray($content);
        $this->assertEmpty($content);
    }

    public function testItCreatesAnUpdateAssignmentResponseForASingleCaseWithConflict(): void
    {
        $caseId = '1234567';
        $assignmentStatus = 'abc123';
        $conflicts = $this->mockConflicts($caseId, $assignmentStatus);

        $caseAssignmentConflictService = $this->app->get(CaseAssignmentConflictService::class);

        /** @var JsonResponse $actual */
        $actual = $caseAssignmentConflictService->createUpdateAssignmentResponse(['3'], $conflicts);

        $content = json_decode($actual->getContent());

        $this->assertEquals(Response::HTTP_CONFLICT, $actual->getStatusCode());
        $this->assertCount(1, $content);
        $this->assertEquals($caseId, $content[0]->caseId);
        $this->assertEquals($assignmentStatus, $content[0]->assignmentStatus);
    }

    public function testItCreatesAnUpdateAssignmentResponseForMultipleCasesWithoutConflict(): void
    {
        $caseAssignmentConflictService = $this->app->get(CaseAssignmentConflictService::class);

        /** @var JsonResponse $actual */
        $actual = $caseAssignmentConflictService->createUpdateAssignmentResponse(
            ['4', '5'],
            new Collection(),
        );
        $content = json_decode($actual->getContent());

        $this->assertEquals(Response::HTTP_NO_CONTENT, $actual->getStatusCode());
        $this->assertIsArray($content);
        $this->assertEmpty($content);
    }

    public function testItCreatesAnUpdateAssignmentResponseForMultipleCasesWithPartialConflict(): void
    {
        $caseId = '1234567';
        $assignmentStatus = 'abc123';
        $conflicts = $this->mockConflicts($caseId, $assignmentStatus);

        $caseAssignmentConflictService = $this->app->get(CaseAssignmentConflictService::class);

        /** @var JsonResponse $actual */
        $actual = $caseAssignmentConflictService->createUpdateAssignmentResponse(['6', '7'], $conflicts);

        $content = json_decode($actual->getContent());

        $this->assertEquals(Response::HTTP_OK, $actual->getStatusCode());
        $this->assertCount(1, $content);
        $this->assertEquals($caseId, $content[0]->caseId);
        $this->assertEquals($assignmentStatus, $content[0]->assignmentStatus);
    }

    public function testItCreatesAnUpdateAssignmentResponseForMultipleCasesWithConflict(): void
    {
        $caseId1 = '1234567';
        $case1 = $this->mockCase($caseId1);
        $history1 = $this->mockHistory($case1);
        $timeline1 = $this->mock(Timeline::class);
        $timelineCollection1 = new Collection([$timeline1]);
        $caseAssignmentStatus1 = 'abc123';
        $timelineDto1 = new TimelineDto('a', 'b', $caseAssignmentStatus1, 'd', 'e', 'f');

        $caseId2 = '1234567';
        $case2 = $this->mockCase($caseId2);
        $history2 = $this->mockHistory($case2);
        $timeline2 = $this->mock(Timeline::class);
        $timelineCollection2 = new Collection([$timeline2]);
        $caseAssignmentStatus2 = 'xyz789';
        $timelineDto2 = new TimelineDto('g', 'h', $caseAssignmentStatus2, 'i', 'j', 'k');

        $conflicts = new Collection([$history1, $history2]);

        $this->mock(
            TimelineService::class,
            static function (MockInterface $mock) use ($case1, $timelineCollection1, $case2, $timelineCollection2): void {
                $mock->expects('getTimeline')
                    ->with($case1)
                    ->andReturn($timelineCollection1);

                $mock->expects('getTimeline')
                    ->with($case2)
                    ->andReturn($timelineCollection2);
            },
        );

        $this->mock(
            TimelineDtoFactory::class,
            static function (MockInterface $mock) use ($history1, $timelineCollection1, $timelineDto1, $history2, $timelineCollection2, $timelineDto2): void {
                $mock->expects('fromConflictingCaseAssignmentHistory')
                    ->with($history1, $timelineCollection1)
                    ->andReturn($timelineDto1);

                $mock->expects('fromConflictingCaseAssignmentHistory')
                    ->with($history2, $timelineCollection2)
                    ->andReturn($timelineDto2);
            },
        );

        $caseAssignmentConflictService = $this->app->get(CaseAssignmentConflictService::class);

        /** @var JsonResponse $actual */
        $actual = $caseAssignmentConflictService->createUpdateAssignmentResponse(['8', '9'], $conflicts);

        $content = json_decode($actual->getContent());

        $this->assertEquals(Response::HTTP_CONFLICT, $actual->getStatusCode());
        $this->assertCount(2, $content);
        $this->assertEquals($caseId1, $content[0]->caseId);
        $this->assertEquals($caseAssignmentStatus1, $content[0]->assignmentStatus);
        $this->assertEquals($caseId2, $content[1]->caseId);
        $this->assertEquals($caseAssignmentStatus2, $content[1]->assignmentStatus);
    }

    private function mockCase(string $caseId): MockInterface
    {
        return $this->mock(
            EloquentCase::class,
            static function (MockInterface $mock) use ($caseId): void {
                $mock->expects('getAttribute')->atLeast()->once()->andReturn($caseId);
                $mock->expects('offsetExists')->atLeast()->once()->andReturn(true);
            },
        );
    }

    private function mockHistory(MockInterface $covidCase): MockInterface
    {
        return $this->mock(
            CaseAssignmentHistory::class,
            static function (MockInterface $mock) use ($covidCase): void {
                $mock->expects('getAttribute')->atLeast()->once()->andReturn($covidCase);
                $mock->expects('offsetExists')->atLeast()->once()->andReturn(true);
            },
        );
    }

    private function mockConflicts(string $caseId, string $assignmentStatus): Collection
    {
        $covidCase = $this->mockCase($caseId);
        $history = $this->mockHistory($covidCase);
        $timeline1 = $this->mock(Timeline::class);
        $timelineCollection1 = new Collection([$timeline1]);
        $timelineDto1 = new TimelineDto('a', 'b', $assignmentStatus, 'd', 'e', 'f');

        $this->mock(
            TimelineService::class,
            static function (MockInterface $mock) use ($covidCase, $timelineCollection1): void {
                $mock->expects('getTimeline')
                    ->atLeast()
                    ->once()
                    ->with($covidCase)
                    ->andReturn($timelineCollection1);
            },
        );

        $this->mock(
            TimelineDtoFactory::class,
            static function (MockInterface $mock) use ($history, $timelineCollection1, $timelineDto1): void {
                $mock->expects('fromConflictingCaseAssignmentHistory')
                    ->atLeast()
                    ->once()
                    ->with($history, $timelineCollection1)
                    ->andReturn($timelineDto1);
            },
        );

        return new Collection([$history]);
    }
}

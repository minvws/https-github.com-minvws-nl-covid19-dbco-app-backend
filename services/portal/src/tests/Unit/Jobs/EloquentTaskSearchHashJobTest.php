<?php

declare(strict_types=1);

namespace Tests\Unit\Jobs;

use App\Jobs\EloquentTaskSearchHashJob;
use App\Repositories\SearchHashTaskRepository;
use App\Services\SearchHash\SearchHasherFactory;
use Illuminate\Database\DatabaseManager;
use Mockery;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UnitTestCase;

#[Group('search-hash')]
final class EloquentTaskSearchHashJobTest extends UnitTestCase
{
    public function testItCanBeInitialized(): void
    {
        $this->assertInstanceOf(EloquentTaskSearchHashJob::class, new EloquentTaskSearchHashJob(taskUuid: ''));
    }

    public function testRetryConfiguration(): void
    {
        $job = new EloquentTaskSearchHashJob('');

        $this->assertSame(3, $job->tries);
        $this->assertSame([20, 40, 60], $job->backoff());
    }

    public function testItDoesNothingIfCaseCannotBeFound(): void
    {
        $taskUuid = 'non_existing';

        $searchHashCaseRepository = Mockery::mock(SearchHashTaskRepository::class);
        $searchHashCaseRepository->expects('getTaskByUuid')->with($taskUuid)->andReturnNull();

        $searchHasherFactory = Mockery::mock(SearchHasherFactory::class);
        $databaseManager = Mockery::mock(DatabaseManager::class);

        $job = new EloquentTaskSearchHashJob($taskUuid);
        $job->handle($searchHashCaseRepository, $searchHasherFactory, $databaseManager);
    }
}

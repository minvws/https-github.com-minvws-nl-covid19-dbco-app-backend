<?php

declare(strict_types=1);

namespace Tests\Unit\Jobs;

use App\Jobs\IndexSearchHashJob;
use App\Repositories\SearchHashCaseRepository;
use App\Services\SearchHash\SearchHasherFactory;
use Illuminate\Database\DatabaseManager;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UnitTestCase;

#[Group('search-hash')]
final class IndexSearchHashJobTest extends UnitTestCase
{
    public function testItCanBeInitialized(): void
    {
        $this->assertInstanceOf(IndexSearchHashJob::class, new IndexSearchHashJob(caseUuid: ''));
    }

    public function testRetryConfiguration(): void
    {
        $job = new IndexSearchHashJob('');

        $this->assertSame(3, $job->tries);
        $this->assertSame([20, 40, 60], $job->backoff());
    }

    public function testItDoesNothingIfCaseCannotBeFound(): void
    {
        $caseUuid = 'non_existing';

        /** @var SearchHashCaseRepository&MockInterface $searchHashCaseRepository */
        $searchHashCaseRepository = Mockery::mock(SearchHashCaseRepository::class);
        $searchHashCaseRepository->expects('getCaseByUuid')
            ->with($caseUuid, ['index'])
            ->andReturnNull();

        $searchHasherFactory = Mockery::mock(SearchHasherFactory::class);
        $databaseManager = Mockery::mock(DatabaseManager::class);

        $job = new IndexSearchHashJob($caseUuid);
        $job->handle($searchHashCaseRepository, $searchHasherFactory, $databaseManager);
    }
}

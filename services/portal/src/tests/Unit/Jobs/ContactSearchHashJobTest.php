<?php

declare(strict_types=1);

namespace Tests\Unit\Jobs;

use App\Jobs\ContactSearchHashJob;
use App\Repositories\SearchHashCaseRepository;
use App\Services\SearchHash\SearchHasherFactory;
use Illuminate\Database\DatabaseManager;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UnitTestCase;

#[Group('search-hash')]
final class ContactSearchHashJobTest extends UnitTestCase
{
    public function testItCanBeInitialized(): void
    {
        $this->assertInstanceOf(ContactSearchHashJob::class, new ContactSearchHashJob(caseUuid: ''));
    }

    public function testRetryConfiguration(): void
    {
        $job = new ContactSearchHashJob('');

        $this->assertSame(3, $job->tries);
        $this->assertSame([20, 40, 60], $job->backoff());
    }

    public function testItDoesNothingIfCaseCannotBeFound(): void
    {
        $caseUuid = 'non_existing';

        /** @var SearchHashCaseRepository&MockInterface $searchHashCaseRepository */
        $searchHashCaseRepository = Mockery::mock(SearchHashCaseRepository::class);
        $searchHashCaseRepository->expects('getCaseByUuid')
            ->with($caseUuid, ['index', 'contact'])
            ->andReturnNull();

        $searchHasherFactory = Mockery::mock(SearchHasherFactory::class);
        $databaseManager = Mockery::mock(DatabaseManager::class);

        $job = new ContactSearchHashJob(caseUuid: $caseUuid);
        $job->handle($searchHashCaseRepository, $searchHasherFactory, $databaseManager);
    }
}

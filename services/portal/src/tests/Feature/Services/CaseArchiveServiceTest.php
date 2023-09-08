<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Repositories\CaseRepository;
use App\Services\CaseArchiveService;
use Illuminate\Support\Collection;
use MinVWS\DBCO\Enum\Models\BCOStatus;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;
use Tests\Helpers\ConfigHelper;

class CaseArchiveServiceTest extends FeatureTestCase
{
    #[Group('policy')]
    public function testArchivingStaleCompletedCase(): void
    {
        $archiveStaleCompletedCasesInDays = $this->faker->randomDigit();
        ConfigHelper::set('misc.case.archiveStaleCompletedCasesInDays', $archiveStaleCompletedCasesInDays);
        ConfigHelper::disableFeatureFlag('osiris_send_case_enabled');

        $case = $this->createCase(['bco_status' => BCOStatus::completed()]);

        $caseRepository = $this->mock(CaseRepository::class, static function (MockInterface $mock) use (
            $archiveStaleCompletedCasesInDays,
            $case,
        ): void {
            $mock->expects('getStaleCasesByBCOStatus')
                ->once()
                ->with($archiveStaleCompletedCasesInDays, BCOStatus::completed())
                ->andReturn(new Collection([$case]));

            $mock->expects('archive')->with($case, $case->policy_version_uuid);
        });

        /** @var CaseArchiveService $caseArchiveService */
        $caseArchiveService = $this->app->make(CaseArchiveService::class, [
            'archiveStaleCompletedCasesInDays' => $archiveStaleCompletedCasesInDays,
            'caseRepository' => $caseRepository,
        ]);
        $archivedCasesCount = $caseArchiveService->archiveStaleCompleted();

        $this->assertEquals(1, $archivedCasesCount);
    }
}

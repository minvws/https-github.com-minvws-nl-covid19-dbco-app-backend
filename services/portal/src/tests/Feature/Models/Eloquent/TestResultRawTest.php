<?php

declare(strict_types=1);

namespace Tests\Feature\Models\Eloquent;

use App\Dto\TestResultReport\TestResultReport;
use App\Services\TestResult\TestResultImportService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Tests\DataProvider\TestResultDataProvider;
use Tests\Feature\FeatureTestCase;

use function json_decode;
use function sprintf;

class TestResultRawTest extends FeatureTestCase
{
    public function testDataIsEncryptedJsonWithCorrectPrefix(): void
    {
        $testResultPayload = TestResultDataProvider::payload();
        $testResultReport = TestResultReport::fromArray($testResultPayload);
        $organisation = $this->createOrganisation();

        $testResultImportService = $this->app->get(TestResultImportService::class);
        $testResultImportService->import($testResultReport, $organisation, null);

        $this->assertDatabaseCount('test_result_raw', 1);
        $databaseResult = DB::table('test_result_raw')
            ->select('data')
            ->first();
        $this->assertEquals(
            sprintf('store:VS%s', CarbonImmutable::now()->format('Ymd')),
            json_decode($databaseResult->data)->key,
        );
    }
}

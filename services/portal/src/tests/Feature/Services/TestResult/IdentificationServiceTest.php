<?php

declare(strict_types=1);

namespace Tests\Feature\Services\TestResult;

use App\Dto\TestResultReport\TestResultReport;
use App\Models\Metric\TestResult\IdentificationStatus;
use App\Repositories\Bsn\BsnException;
use App\Repositories\Bsn\Dto\PseudoBsn;
use App\Repositories\Metric\MetricRepository;
use App\Services\Bsn\BsnService;
use App\Services\TestResult\IdentificationService;
use Mockery;
use Mockery\MockInterface;
use Tests\DataProvider\TestResultDataProvider;
use Tests\Feature\FeatureTestCase;

final class IdentificationServiceTest extends FeatureTestCase
{
    public function testNullReturnedWhenRequestToRetrievePseudoBsnFailed(): void
    {
        $testResultReport = TestResultReport::fromArray(TestResultDataProvider::payload());

        $bsnService = $this->createMock(BsnService::class);
        $bsnService->expects($this->once())->method('convertBsnAndDateOfBirthToPseudoBsn')
            ->willThrowException(new BsnException($this->faker->word));
        $this->app->instance(BsnService::class, $bsnService);

        $this->mock(MetricRepository::class, static function (MockInterface $mock): void {
            $mock->expects('measureCounter')
                ->with(Mockery::on(static function (IdentificationStatus $identificationStatus): bool {
                    return $identificationStatus->getLabels() === ['status' => 'not identified'];
                }));
        });

        $identificationService = $this->app->get(IdentificationService::class);
        $pseudoBsn = $identificationService->identify($testResultReport, $this->createOrganisation());
        $this->assertNull($pseudoBsn);
    }

    public function testPseudoBsnReturnedWhenIdentificationSucceeds(): void
    {
        $expected = $this->createMock(PseudoBsn::class);

        $testResultReport = TestResultReport::fromArray(TestResultDataProvider::payload());

        $bsnService = $this->createMock(BsnService::class);
        $bsnService->expects($this->once())->method('convertBsnAndDateOfBirthToPseudoBsn')->willReturn($expected);
        $this->app->instance(BsnService::class, $bsnService);

        $this->mock(MetricRepository::class, static function (MockInterface $mock): void {
            $mock->expects('measureCounter')
                ->with(Mockery::on(static function (IdentificationStatus $identificationStatus): bool {
                    return $identificationStatus->getLabels() === ['status' => 'identified'];
                }));
        });

        $identificationService = $this->app->get(IdentificationService::class);
        $actual = $identificationService->identify($testResultReport, $this->createOrganisation());
        $this->assertSame($expected, $actual);
    }
}

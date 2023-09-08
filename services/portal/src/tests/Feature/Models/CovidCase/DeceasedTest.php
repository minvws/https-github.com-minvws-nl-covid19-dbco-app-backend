<?php

declare(strict_types=1);

namespace Tests\Feature\Models\CovidCase;

use App\Models\CovidCase\Deceased;
use App\Models\CovidCase\Index;
use App\Models\CovidCase\Job;
use App\Models\CovidCase\UnderlyingSuffering;
use App\Schema\Validation\ValidationRule;
use App\Services\CaseFragmentService;
use Carbon\CarbonImmutable;
use MinVWS\DBCO\Enum\Models\JobSector;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function app;

#[Group('osiris')]
#[Group('osiris-validation')]
class DeceasedTest extends FeatureTestCase
{
    private CaseFragmentService $caseFragmentService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->caseFragmentService = app(CaseFragmentService::class);
    }

    public function testDeceasedFragmentValidationPasses(): void
    {
        $case = $this->createCase([
            'date_of_test' => $this->faker->dateTimeBetween(endDate: '-4 days'),
            'index' => Index::newInstanceWithVersion(1, function (Index $index): void {
                $index->dateOfBirth = $this->faker->dateTimeBetween('-60 years', '-46 years');
            }),
            'deceased' => Deceased::newInstanceWithVersion(1, static function (Deceased $deceased): void {
                $deceased->isDeceased = YesNoUnknown::yes();
                $deceased->deceasedAt = new CarbonImmutable('-3 days');
            }),
            'underlying_suffering' => UnderlyingSuffering::newInstanceWithVersion(
                2,
                static function (UnderlyingSuffering $underlyingSuffering): void {
                    $underlyingSuffering->hasUnderlyingSufferingOrMedication = YesNoUnknown::yes();
                    $underlyingSuffering->hasUnderlyingSuffering = YesNoUnknown::yes();
                },
            ),
        ]);
        $validationResult = $this->caseFragmentService->validateAllFragments($case, [ValidationRule::TAG_OSIRIS_FINAL]);

        $this->assertEmpty($validationResult);
    }

    public function testDeceasedAtValidationNoticeFailsWhenCareProfessional(): void
    {
        $case = $this->createCase([
            'date_of_test' => $this->faker->dateTimeBetween(endDate: '-4 days'),
            'index' => Index::newInstanceWithVersion(1, function (Index $index): void {
                $index->dateOfBirth = $this->faker->dateTimeBetween('-60 years');
            }),
            'job' => Job::newInstanceWithVersion(1, static function (Job $job): void {
                $job->wasAtJob = YesNoUnknown::yes();
                $job->sectors = [
                    JobSector::ziekenhuis(),
                ];
            }),
            'deceased' => Deceased::newInstanceWithVersion(1, static function (Deceased $deceased): void {
                $deceased->isDeceased = YesNoUnknown::yes();
                $deceased->deceasedAt = new CarbonImmutable('-3 days');
            }),
        ]);

        $validationResult = $this->caseFragmentService->validateAllFragments($case, [ValidationRule::TAG_OSIRIS_FINAL]);

        $this->assertArrayHasKey('deceased', $validationResult);
        $this->assertArrayHasKey('notice', $validationResult['deceased']);
        $this->assertArrayHasKey('failed', $validationResult['deceased']['notice']);
        $this->assertArrayHasKey('isDeceased', $validationResult['deceased']['notice']['failed']);
        $this->assertArrayHasKey(
            'App\Rules\IsDeceasedCareProfessionalRule',
            $validationResult['deceased']['notice']['failed']['isDeceased'],
        );
    }

    public function testDeceasedAtValidationNoticeFailsYoungerThan45(): void
    {
        $case = $this->createCase([
            'date_of_test' => $this->faker->dateTimeBetween(endDate: '-4 days'),
            'index' => Index::newInstanceWithVersion(1, function (Index $index): void {
                $index->dateOfBirth = $this->faker->dateTimeBetween('-45 years');
            }),
            'deceased' => Deceased::newInstanceWithVersion(1, static function (Deceased $deceased): void {
                $deceased->isDeceased = YesNoUnknown::yes();
                $deceased->deceasedAt = new CarbonImmutable('-3 days');
            }),
        ]);

        $validationResult = $this->caseFragmentService->validateAllFragments($case, [ValidationRule::TAG_OSIRIS_FINAL]);

        $this->assertArrayHasKey('deceased', $validationResult);
        $this->assertArrayHasKey('notice', $validationResult['deceased']);
        $this->assertArrayHasKey('errors', $validationResult['deceased']['notice']);
        /** @var MessageBag $messageBag */
        $messageBag = $validationResult['deceased']['notice']['errors'];

        $this->assertArrayHasKey('deceasedAt', $messageBag->getMessages());
        $this->assertEquals('Deze persoon was jonger dan 45 jaar.', $messageBag->getMessages()['deceasedAt'][0]);
    }
}

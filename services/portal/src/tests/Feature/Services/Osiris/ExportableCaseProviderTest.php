<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Osiris;

use App\Events\Osiris\CaseValidationRaisesNotice;
use App\Events\Osiris\CaseValidationRaisesWarning;
use App\Events\Osiris\ExportableCaseNotFound;
use App\Models\Eloquent\EloquentCase;
use App\Models\Enums\Osiris\CaseExportType;
use App\Repositories\CaseRepository;
use App\Schema\Validation\ValidationRule;
use App\Schema\Validation\ValidationRules;
use App\Services\CaseFragmentsValidationService;
use App\Services\Osiris\Strategy\ExportableCaseProvider;
use Exception;
use Illuminate\Support\Facades\Event;
use Mockery;
use PHPUnit\Framework\Attributes\Group;
use Tests\Faker\WithFaker;
use Tests\Feature\FeatureTestCase;

#[Group('osiris')]
#[Group('osiris-case-export')]
final class ExportableCaseProviderTest extends FeatureTestCase
{
    use WithFaker;

    private readonly CaseRepository $caseRepository;
    private readonly CaseFragmentsValidationService $caseValidator;
    private readonly ExportableCaseProvider $exportableCaseProvider;

    protected function setUp(): void
    {
        parent::setUp();

        $this->caseRepository = Mockery::mock(CaseRepository::class);
        $this->caseValidator = Mockery::mock(CaseFragmentsValidationService::class);
        $this->exportableCaseProvider = new ExportableCaseProvider($this->caseRepository, $this->caseValidator);
    }

    public function testFindDispatchesEventAndReturnsNullIfCaseNotFound(): void
    {
        Event::fake();
        $caseUuid = $this->faker->uuid();
        $caseExportType = $this->faker->randomElement(CaseExportType::cases());
        $validationRuleTags = [$this->faker->randomElement([ValidationRule::TAG_OSIRIS_INITIAL, ValidationRule::TAG_OSIRIS_FINAL])];

        $this->caseRepository->expects('getCaseByUuid')
            ->with($caseUuid)
            ->andReturnNull();

        $this->caseValidator->expects('validateAllFragments')
            ->never();

        $result = $this->exportableCaseProvider->findValidCase($caseUuid, $caseExportType, $validationRuleTags);

        $this->assertNull($result);
        Event::assertDispatched(ExportableCaseNotFound::class);
    }

    public function testFindReturnsNullIfCaseValidatorThrowsException(): void
    {
        $case = Mockery::mock(EloquentCase::class);
        $caseUuid = $this->faker->uuid();
        $caseExportType = $this->faker->randomElement(CaseExportType::cases());
        $validationRuleTags = [$this->faker->randomElement([ValidationRule::TAG_OSIRIS_INITIAL, ValidationRule::TAG_OSIRIS_FINAL])];

        $this->caseRepository->expects('getCaseByUuid')
            ->with($caseUuid)
            ->andReturn($case);

        $this->caseValidator->expects('validateAllFragments')
            ->andThrows(new Exception());

        $result = $this->exportableCaseProvider->findValidCase($caseUuid, $caseExportType, $validationRuleTags);

        $this->assertNull($result);
    }

    public function testFindReturnsNullIfCaseValidationRaisedWarning(): void
    {
        Event::fake();
        $case = Mockery::mock(EloquentCase::class)->makePartial();
        $case->uuid = $this->faker->uuid();
        $caseExportType = $this->faker->randomElement(CaseExportType::cases());
        $validationRuleTags = [$this->faker->randomElement([ValidationRule::TAG_OSIRIS_INITIAL, ValidationRule::TAG_OSIRIS_FINAL])];

        $this->caseRepository->expects('getCaseByUuid')
            ->with($case->uuid)
            ->andReturn($case);

        $this->caseValidator->expects('validateAllFragments')
            ->andReturn([
                'validFragmentName' => [],
                'fragmentNameWithWarning' => [
                    ValidationRules::WARNING => [],
                ],
            ]);

        $result = $this->exportableCaseProvider->findValidCase($case->uuid, $caseExportType, $validationRuleTags);

        $this->assertNull($result);
        Event::assertDispatched(CaseValidationRaisesWarning::class);
    }

    public function testFindReturnsCaseIfCaseValidationRaisedNotice(): void
    {
        Event::fake();
        $case = Mockery::mock(EloquentCase::class)->makePartial();
        $case->uuid = $this->faker->uuid();
        $caseExportType = $this->faker->randomElement(CaseExportType::cases());
        $validationRuleTags = [$this->faker->randomElement([ValidationRule::TAG_OSIRIS_INITIAL, ValidationRule::TAG_OSIRIS_FINAL])];

        $this->caseRepository->expects('getCaseByUuid')
            ->with($case->uuid)
            ->andReturn($case);

        $this->caseValidator->expects('validateAllFragments')
            ->andReturn([
                'validFragmentName' => [],
                'fragmentNameWithNotice' => [
                    ValidationRules::NOTICE => [],
                ],
            ]);

        $result = $this->exportableCaseProvider->findValidCase($case->uuid, $caseExportType, $validationRuleTags);

        $this->assertSame($result, $case);
        Event::assertDispatched(CaseValidationRaisesNotice::class);
    }

    public function testFindReturnsCaseIfCaseValidationWasSuccessful(): void
    {
        Event::fake();
        $case = Mockery::mock(EloquentCase::class);
        $caseUuid = $this->faker->uuid();
        $caseExportType = $this->faker->randomElement(CaseExportType::cases());
        $validationRuleTags = [$this->faker->randomElement([ValidationRule::TAG_OSIRIS_INITIAL, ValidationRule::TAG_OSIRIS_FINAL])];

        $this->caseRepository->expects('getCaseByUuid')
            ->with($caseUuid)
            ->andReturn($case);

        $this->caseValidator->expects('validateAllFragments')
            ->andReturn([
                'validFragmentName' => [],
                'anotherValidFragmentName' => [],
            ]);

        $result = $this->exportableCaseProvider->findValidCase($caseUuid, $caseExportType, $validationRuleTags);

        $this->assertSame($result, $case);
    }
}

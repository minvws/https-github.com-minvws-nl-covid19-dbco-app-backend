<?php

declare(strict_types=1);

namespace Tests\Feature\Models\CovidCase;

use App\Http\Controllers\Api\Traits\ValidatesModels;
use App\Models\CovidCase\Index;
use App\Models\CovidCase\Test;
use App\Schema\Validation\ValidationRule;
use App\Services\CaseFragmentService;
use Illuminate\Support\Carbon;
use MinVWS\Codable\Decoder;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function app;
use function config;

#[Group('osiris')]
#[Group('osiris-validation')]
class TestTest extends FeatureTestCase
{
    use ValidatesModels;

    private CaseFragmentService $caseFragmentService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->caseFragmentService = app(CaseFragmentService::class);
    }

    #[Group('dateOfSymptomsOnsetDataProvider')]
    public function testDateOfSymptomOnsetValidationFailsWhenBeforeFirstAllowableDate(): void
    {
         $case = $this->createCase([
             'index' => Index::newInstanceWithVersion(1, function (Index $index): void {
                 $index->dateOfBirth = $this->faker->dateTimeBetween('-60 years');
             }),
             'test' => Test::newInstanceWithVersion(2, function (Test $test): void {
                 $test->dateOfSymptomOnset = (new Carbon(
                     $this->faker->dateTime(config('misc.validations.firstAllowableDateOfSymptomOnset')),
                 ))->sub(
                     '1 day',
                 )->format(
                     'Y-m-d',
                 );
             }),
         ]);

         $validationResult = $this->caseFragmentService->validateAllFragments($case, [ValidationRule::TAG_OSIRIS_FINAL]);

         $this->assertArrayHasKey('test', $validationResult);
         $this->assertArrayHasKey('warning', $validationResult['test']);
         $this->assertArrayHasKey('failed', $validationResult['test']['warning']);
         $this->assertArrayHasKey('dateOfSymptomOnset', $validationResult['test']['warning']['failed']);
         $this->assertArrayHasKey('AfterOrEqual', $validationResult['test']['warning']['failed']['dateOfSymptomOnset']);
    }

    #[DataProvider('caseNumberValidExampleProvider')]
    public function testCaseNumberValid(string $value): void
    {
        $validationResults = $this->validateModel(Test::class, [
            'previousInfectionCaseNumber' => $value,
        ]);

        $this->assertArrayNotHasKey('warning', $validationResults);
    }

    public static function caseNumberValidExampleProvider(): array
    {
        return [
            ['666666'],
            ['88888888'],
            ['AB1234567'],
            ['AB1 234 567'],
            ['AB1-234-567'],
        ];
    }

    #[DataProvider('caseNumberInvalidExampleProvider')]
    public function testCaseNumberInvalid(string $value): void
    {
        $validationResults = $this->validateModel(Test::class, [
            'previousInfectionCaseNumber' => $value,
        ]);
        $this->assertArrayHasKey('Regex', $validationResults['warning']['failed']['previousInfectionCaseNumber']);
    }

    public static function caseNumberInvalidExampleProvider(): array
    {
        return [
            ['55555'],
            ['999999999'],
            ['AB12 34567'],
            ['AB12345-67'],
            ['AB12345678'],
            ['ABC234567'],
        ];
    }

    #[DataProvider('caseNumberFormattingExamples')]
    public function testFormatCaseNumberCorrectly(string $from, string $to): void
    {
        $decoded = (new Decoder())->decode([
            'previousInfectionCaseNumber' => $from,
        ])->decodeObject(Test::class, Test::getSchema()->getVersion(4)->newInstance());

        $this->assertEquals($to, $decoded->previousInfectionCaseNumber);
    }

    public static function caseNumberFormattingExamples(): array
    {
        return [
            ['88888888', '88888888'],
            ['AB1-234-567', 'AB1-234-567'],
            ['AB1234567', 'AB1-234-567'],
            ['AB1 234 567', 'AB1-234-567'],
        ];
    }
}

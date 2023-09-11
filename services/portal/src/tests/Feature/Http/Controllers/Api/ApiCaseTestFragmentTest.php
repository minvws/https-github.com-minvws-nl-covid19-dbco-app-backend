<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\CovidCase\Test;
use App\Models\Eloquent\EloquentCase;
use Carbon\CarbonImmutable;
use MinVWS\DBCO\Enum\Models\HospitalReason;
use MinVWS\DBCO\Enum\Models\TestResult;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function config;

#[Group('case-fragment')]
#[Group('case-fragment-test')]
class ApiCaseTestFragmentTest extends FeatureTestCase
{
    /**
     * Test index fragment retrieval.
     */
    public function testGet(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);

        $response = $this->be($user)->get('/api/cases/' . $case->uuid . '/fragments/test');
        $response->assertStatus(200);

        $response = $this->be($user)->get('/api/cases/nonexisting/fragments/test');
        $response->assertStatus(404);
    }

    /**
     * Test index fragment storage.
     */
    public function testPost(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user, [
            'created_at' => CarbonImmutable::now()->format('Y-m-d'),
        ]);
        $case->symptoms->hasSymptoms = YesNoUnknown::yes();
        $case->general->hpzoneNumber = '1234567';
        $case->save();

        $dateOfSymptomOnset = CarbonImmutable::now()->subDay()->format('Y-m-d');
        $dateOfTest = CarbonImmutable::now()->subDay(2)->format('Y-m-d');
        // check storage
        $response = $this->be($user)->putJson('/api/cases/' . $case->uuid . '/fragments/test', [
            'dateOfSymptomOnset' => $dateOfSymptomOnset,
            'dateOfTest' => $dateOfTest,
        ]);
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertEquals($dateOfSymptomOnset, $data['data']['dateOfSymptomOnset']);
        $this->assertEquals($dateOfTest, $data['data']['dateOfTest']);

        // check if really stored
        $response = $this->be($user)->get('/api/cases/' . $case->uuid . '/fragments/test');
        $data = $response->json();
        $this->assertEquals($dateOfTest, $data['data']['dateOfTest']);

        // check if stored in case
        $this->assertDatabaseHas('covidcase', [
            'uuid' => $case->uuid,
            'date_of_symptom_onset' => $dateOfSymptomOnset,
            'date_of_test' => $dateOfTest,
        ]);
    }

    public function testMonsterNumberNotRequiredWhenHpzoneNumberIsSet(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user, [
            'created_at' => CarbonImmutable::now()->format('Y-m-d'),
        ]);
        $case->general->hpzoneNumber = '1234567';
        $case->symptoms->hasSymptoms = YesNoUnknown::yes();
        $case->save();

        $dateOfSymptomOnset = CarbonImmutable::now()->subDay()->format('Y-m-d');
        $dateOfTest = CarbonImmutable::now()->subDay(2)->format('Y-m-d');

        $response = $this->be($user)->putJson('/api/cases/' . $case->uuid . '/fragments/test', [
            'dateOfSymptomOnset' => $dateOfSymptomOnset,
            'dateOfTest' => $dateOfTest,
        ]);
        $response->assertStatus(200);
    }

    public function testDateOfTestInFutureShouldGiveWarning(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user, [
            'created_at' => CarbonImmutable::now()->format('Y-m-d'),
        ]);

        $dateOfTest = CarbonImmutable::now()->addDays(1)->format('Y-m-d');

        $response = $this->be($user)->putJson('/api/cases/' . $case->uuid . '/fragments/test', [
            'dateOfTest' => $dateOfTest,
            'monsterNumber' => '123A34567',
        ]);
        $response->assertStatus(200);

        $validationResult = $response->json('validationResult');
        $this->assertArrayHasKey('dateOfTest', $validationResult['warning']['failed']);
    }

    public function testDateOfTestToFarInPastShouldGiveWarning(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user, [
            'created_at' => CarbonImmutable::now()->format('Y-m-d'),
        ]);

        $dateOfTest = CarbonImmutable::now()->subDays(config('misc.validations.maxBeforeCaseCreationDateInDays') + 1)->format('Y-m-d');

        $response = $this->be($user)->putJson('/api/cases/' . $case->uuid . '/fragments/test', [
            'dateOfTest' => $dateOfTest,
            'monsterNumber' => '123A34567',
        ]);
        $response->assertStatus(200);

        $validationResult = $response->json('validationResult');
        $this->assertArrayHasKey('dateOfTest', $validationResult['warning']['failed']);
    }

    public function testSelfTestLabOptions(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);

        // no required fields
        $dateOfLabTest = CarbonImmutable::now()->subDay()->format('Y-m-d');
        $response = $this->be($user)->putJson('/api/cases/' . $case->uuid . '/fragments/test', [
            'selfTestLabTestDate' => $dateOfLabTest,
            'selfTestLabTestResult' => TestResult::positive(),
            'monsterNumber' => '123A34567',
        ]);
        $response->assertStatus(200);

        $data = $response->json();
        $this->assertEquals($dateOfLabTest, $data['data']['selfTestLabTestDate']);
        $this->assertEquals(TestResult::positive()->value, $data['data']['selfTestLabTestResult']);

        $response = $this->be($user)->get('/api/cases/' . $case->uuid . '/fragments/test');
        $data = $response->json();
        $this->assertEquals($dateOfLabTest, $data['data']['selfTestLabTestDate']);
        $this->assertEquals(TestResult::positive()->value, $data['data']['selfTestLabTestResult']);
    }

    public function testWithSelfTestLabTestDateBeforeDateOfTestShouldNotValidate(): void
    {
        $user = $this->createUser();
        $dateOfTest = CarbonImmutable::now()->format('Y-m-d');
        $case = $this->createCaseForUser($user, [
            'created_at' => CarbonImmutable::now()->format('Y-m-d'),
            'test' => Test::newInstanceWithVersion(1, static function (Test $test) use ($dateOfTest): void {
                $test->dateOfTest = $dateOfTest;
            }),
        ]);

        $response = $this->be($user)->putJson('/api/cases/' . $case->uuid . '/fragments/test', [
            'selfTestLabTestDate' => CarbonImmutable::now()->subDays(1)->format('Y-m-d'),
            'monsterNumber' => '123A34567',
        ]);
        $response->assertStatus(200);

        $validationResult = $response->json('validationResult');
        $this->assertArrayHasKey('AfterOrEqual', $validationResult['warning']['failed']['selfTestLabTestDate']);
        $this->assertEquals([$dateOfTest], $validationResult['warning']['failed']['selfTestLabTestDate']['AfterOrEqual']);
    }

    public function testWithSelfTestLabTestDateButWithoutTestDateAndBeforeMaxBeforeCaseCreationDateShouldNotValidate(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user, [
            'created_at' => CarbonImmutable::now()->format('Y-m-d'),
            'test' => Test::newInstanceWithVersion(1, static function (Test $test): void {
                $test->dateOfTest = null;
            }),
        ]);

        $maxBeforeCaseCreationDate = CarbonImmutable::now()->subDays(config('misc.validations.maxBeforeCaseCreationDateInDays'))->format(
            'Y-m-d',
        );
        $response = $this->be($user)->putJson('/api/cases/' . $case->uuid . '/fragments/test', [
            'selfTestLabTestDate' => CarbonImmutable::now()->subDays(
                config('misc.validations.maxBeforeCaseCreationDateInDays') + 1,
            )->format(
                'Y-m-d',
            ),
            'monsterNumber' => '123A34567',
        ]);
        $response->assertStatus(200);

        $validationResult = $response->json('validationResult');
        $this->assertArrayHasKey('AfterOrEqual', $validationResult['warning']['failed']['selfTestLabTestDate']);
        $this->assertEquals([$maxBeforeCaseCreationDate], $validationResult['warning']['failed']['selfTestLabTestDate']['AfterOrEqual']);
    }

    public function testIndexSubmittedDateOfTestShouldBeLoadedWhenNoStaffDateOfTest(): void
    {
        $today = CarbonImmutable::now()->format('Y-m-d');
        $yesterday = CarbonImmutable::yesterday()->format('Y-m-d');

        $user = $this->createUser();
        $case = $this->createCaseForUser($user, [
            'date_of_symptom_onset' => null,
            'date_of_test' => null,
            'index_submitted_date_of_symptom_onset' => $yesterday,
            'index_submitted_date_of_test' => $today,
        ]);
        $case->symptoms->hasSymptoms = YesNoUnknown::yes();
        $case->save();

        $case->refresh();
        $this->assertEquals($yesterday, $case->test->dateOfSymptomOnset->format('Y-m-d'));
        $this->assertEquals($today, $case->test->dateOfTest->format('Y-m-d'));

        $response = $this->be($user)->get('/api/cases/' . $case->uuid . '/fragments/test');
        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertEquals($yesterday, $data['dateOfSymptomOnset']);
        $this->assertEquals($today, $data['dateOfTest']);
    }

    public function testCaseDateOfTestShouldBeLoadedInTestFragment(): void
    {
        $today = CarbonImmutable::now()->format('Y-m-d');
        $yesterday = CarbonImmutable::yesterday()->format('Y-m-d');

        $user = $this->createUser();
        $case = $this->createCaseForUser($user, [
            'date_of_symptom_onset' => $yesterday,
            'date_of_test' => $today,
        ]);

        $case->refresh();
        $this->assertEquals($yesterday, $case->test->dateOfSymptomOnset->format('Y-m-d'));
        $this->assertEquals($today, $case->test->dateOfTest->format('Y-m-d'));
    }

    public function testAdmittedInICUAtWithEarlierReleasedAt(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user, [
            'created_at' => CarbonImmutable::now()->format('Y-m-d'),
        ]);

        $admittedAt = CarbonImmutable::now()->subDays(9)->format('Y-m-d');
        $releasedAt = CarbonImmutable::now()->subDays(3)->format('Y-m-d');
        $admittedInICUAt = CarbonImmutable::now()->subDays(1)->format('Y-m-d');

        $response = $this->be($user)->putJson('/api/cases/' . $case->uuid . '/fragments', [
            'hospital' => [
                'isAdmitted' => YesNoUnknown::yes(),
                'admittedAt' => $admittedAt,
                'releasedAt' => $releasedAt,
                'isInICU' => YesNoUnknown::yes(),
                'admittedInICUAt' => $admittedInICUAt,
            ],
        ]);

        $response->assertStatus(200);
        $validationResult = $response->json('validationResult');

        $this->assertArrayHasKey('admittedInICUAt', $validationResult['hospital']['warning']['failed']);
    }

    public function testAdmittedInICUAtWithoutReleasedAt(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user, [
            'created_at' => CarbonImmutable::now()->format('Y-m-d'),
            'test' => Test::newInstanceWithVersion(1, static function (Test $test): void {
                $test->dateOfTest = CarbonImmutable::now()->subDays(2)->format('Y-m-d');
            }),
        ]);

        $admittedAt = CarbonImmutable::now()->subDays(9)->format('Y-m-d');
        $admittedInICUAt = CarbonImmutable::now()->subDays(5)->format('Y-m-d');

        $response = $this->be($user)->putJson('/api/cases/' . $case->uuid . '/fragments', [
            'hospital' => [
                'isAdmitted' => YesNoUnknown::yes(),
                'admittedAt' => $admittedAt,
                'reason' => HospitalReason::covid(),
                'isInICU' => YesNoUnknown::yes(),
                'admittedInICUAt' => $admittedInICUAt,
            ],
        ]);
        $response->assertStatus(200);
        $this->assertArrayNotHasKey('validationResult', $response->json());
    }

    #[DataProvider('dateOfSymptomOnsetIsSetProvider')]
    public function testDateOfSymptomOnsetIsSet(YesNoUnknown $hasSymptoms): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user, [
            'created_at' => CarbonImmutable::now()->format('Y-m-d'),
        ]);

        $dateOfTest = CarbonImmutable::yesterday()->format('Y-m-d');
        $dateOfSymptomOnset = CarbonImmutable::parse('-2 day')->format('Y-m-d');

        $response = $this->be($user)->putJson('/api/cases/' . $case->uuid . '/fragments', [
            'symptoms' => [
                'hasSymptoms' => $hasSymptoms->value,
            ],
            'test' => [
                'dateOfTest' => $dateOfTest,
                'dateOfSymptomOnset' => $dateOfSymptomOnset,
            ],
        ]);

        $response->assertStatus(200);

        $result = $response->json();

        $this->assertSame($dateOfSymptomOnset, $result['data']['test']['dateOfSymptomOnset']);

        /** @var EloquentCase $case */
        $case = EloquentCase::find($case->uuid);

        $this->assertSame($dateOfSymptomOnset, $case->test->dateOfSymptomOnset->format('Y-m-d'));
    }

    public static function dateOfSymptomOnsetIsSetProvider(): array
    {
        return [
            [YesNoUnknown::yes()],
            [YesNoUnknown::unknown()],
            [YesNoUnknown::no()],
        ];
    }
}

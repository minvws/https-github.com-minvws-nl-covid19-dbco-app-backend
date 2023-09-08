<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api\Case\TestResult;

use DateTimeImmutable;
use MinVWS\DBCO\Enum\Models\TestResultResult;
use MinVWS\DBCO\Enum\Models\TestResultSource;
use MinVWS\DBCO\Enum\Models\TestResultTypeOfTest;
use Tests\Feature\FeatureTestCase;

use function sprintf;

class ApiCaseTestResultControllerTest extends FeatureTestCase
{
    public function testGetTestResultsSingle(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);

        $testResult = $this->createTestResultForCase($case);

        $response = $this->be($user)->get(sprintf('/api/cases/%s/testresults', $case->uuid));
        $response->assertStatus(200);

        $expectedResult = [
            [
                'uuid' => $testResult->uuid,
                'typeOfTest' => $testResult->type_of_test->value,
                'dateOfTest' => $testResult->dateOfTest->format('Y-m-d\TH:i:sp'),
                'dateOfResult' => $testResult->dateOfResult->format('Y-m-d\TH:i:sp'),
                'source' => $testResult->source->value,
                'receivedAt' => $testResult->receivedAt->format('Y-m-d\TH:i:sp'),
                'testLocation' => $testResult->general->testLocation,
                'sampleLocation' => $testResult->sample_location,
                'sampleNumber' => $testResult->monsterNumber,
                'result' => $testResult->result->value,
                'customTypeOfTest' => $testResult->customTypeOfTest,
                'laboratory' => $testResult->laboratory,
            ],
        ];
        $this->assertEquals($expectedResult, $response->json());
    }

    public function testGetTestResultsMultiple(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);

        $this->createTestResultForCase($case);
        $this->createTestResultForCase($case);

        $response = $this->be($user)->get(sprintf('/api/cases/%s/testresults', $case->uuid));
        $response->assertStatus(200);

        $this->assertCount(2, $response->json());
    }

    public function testCreateManualTestResult(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $monsterNumber = $this->faker->randomNumber(3, true) . $this->faker->randomLetter()
            . $this->faker->numberBetween(0, 999_999_999_999);

        $response = $this->be($user)->post(sprintf('/api/cases/%s/testresults', $case->uuid), [
            'typeOfTest' => TestResultTypeOfTest::antigen()->value,
            'result' => TestResultResult::negative()->value,
            'dateOfTest' => (new DateTimeImmutable())->format('Y-m-d\TH:i:sp'),
            'monsterNumber' => $monsterNumber,
        ]);
        $response->assertStatus(200);

        $response->assertJsonFragment([
            'source' => TestResultSource::manual()->value,
        ]);
    }

    public function testDeleteManualTestResult(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $testResult = $this->createTestResultForCase($case, [
            'source' => TestResultSource::manual(),
        ]);


        $response = $this->be($user)->delete(sprintf('/api/cases/%s/testresults/%s', $case->uuid, $testResult->uuid));
        $response->assertStatus(204);
    }

    public function testCannotDeleteOtherSourceTestResult(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $testResult = $this->createTestResultForCase($case, [
            'source' => TestResultSource::coronit(),
        ]);

        $response = $this->be($user)->delete(sprintf('/api/cases/%s/testresults/%s', $case->uuid, $testResult->uuid));
        $response->assertStatus(403);
    }
}

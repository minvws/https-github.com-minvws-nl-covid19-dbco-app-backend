<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Task\Test;
use App\Models\Versions\Task\Test\TestV1;
use Carbon\CarbonImmutable;
use MinVWS\DBCO\Enum\Models\TestResult;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use Tests\Feature\FeatureTestCase;

use function sprintf;

final class ApiTaskFragmentTestControllerTest extends FeatureTestCase
{
    public function testGet(): void
    {
        $test = Test::getSchema()->getVersion(1)->newInstance();
        $this->assertInstanceOf(TestV1::class, $test);
        $test->isTested = YesNoUnknown::yes();
        $test->testResult = TestResult::negative();
        $test->dateOfTest = CarbonImmutable::createFromFormat('d-m-Y', '21-5-2021');

        $user = $this->createUser();
        $case = $this->createCaseForUser($user, [
            'schema_version' => 1,
        ]);
        $task = $this->createTaskForCase($case, [
            'created_at' => CarbonImmutable::now(),
            'test' => $test,
        ]);

        $response = $this->be($user)->get(sprintf('/api/tasks/%s/fragments/test', $task->uuid));
        $response->assertStatus(200);

        $expectedResult = [
            'data' => [
                'schemaVersion' => 1,
                'isTested' => 'yes',
                'testResult' => 'negative',
                'dateOfTest' => '2021-05-21',
                'isReinfection' => null,
                'previousInfectionDateOfSymptom' => null,
                'previousInfectionReported' => null,
                'previousInfectionHpzoneNumber' => null,
            ],
        ];
        $this->assertEquals($expectedResult, $response->json());
    }

    public function testGetWithAddedFields(): void
    {
        $test = Test::getSchema()->getVersion(1)->newInstance();
        $this->assertInstanceOf(TestV1::class, $test);
        $test->isTested = YesNoUnknown::yes();
        $test->testResult = TestResult::negative();
        $test->dateOfTest = CarbonImmutable::createFromFormat('d-m-Y', '21-5-2021');
        $test->isReinfection = YesNoUnknown::no();
        $test->previousInfectionDateOfSymptom = CarbonImmutable::createFromFormat('d-m-Y', '17-02-2020');
        $test->previousInfectionReported = YesNoUnknown::yes();
        $test->previousInfectionHpzoneNumber = 'foobar';

        $user = $this->createUser();
        $case = $this->createCaseForUser($user, [
            'schema_version' => 1,
        ]);
        $task = $this->createTaskForCase($case, [
            'created_at' => CarbonImmutable::now(),
            'test' => $test,
        ]);

        $response = $this->be($user)->get(sprintf('/api/tasks/%s/fragments/test', $task->uuid));
        $response->assertStatus(200);

        $expectedResult = [
            'data' => [
                'schemaVersion' => 1,
                'isTested' => 'yes',
                'testResult' => 'negative',
                'dateOfTest' => '2021-05-21',
                'isReinfection' => 'no',
                'previousInfectionDateOfSymptom' => '2020-02-17',
                'previousInfectionReported' => 'yes',
                'previousInfectionHpzoneNumber' => 'foobar',
            ],
        ];
        $this->assertEquals($expectedResult, $response->json());
    }
}

<?php

declare(strict_types=1);

namespace Tests\Feature\Services\TestResult;

use App\Http\Controllers\Api\Case\TestResult\CreateManualTest;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\TestResult;
use App\Services\TestResult\TestResultNotDeletable;
use App\Services\TestResult\TestResultService;
use DateTimeImmutable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use MinVWS\DBCO\Enum\Models\TestResultResult;
use MinVWS\DBCO\Enum\Models\TestResultSource;
use MinVWS\DBCO\Enum\Models\TestResultTypeOfTest;
use Tests\Feature\FeatureTestCase;

use function app;

class TestResultServiceTest extends FeatureTestCase
{
    private TestResultService $testResultService;
    private EloquentCase $case;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testResultService = app(TestResultService::class);
        $this->case = $this->createCase();
    }

    public function testCreatingTestResult(): void
    {
        $create = new CreateManualTest();
        $create->typeOfTest = TestResultTypeOfTest::antigen();
        $create->customTypeOfTest = null;
        $create->dateOfTest = new DateTimeImmutable();
        $create->result = TestResultResult::negative();
        $create->monsterNumber = null;
        $create->laboratory = null;

        $uuid = $this->testResultService->createManualTestResult($this->case, $create)->uuid;
        $result = $this->testResultService->getByUuid($uuid);

        $this->assertInstanceOf(TestResult::class, $result);
        $this->assertEquals(null, $result->monsterNumber);
        $this->assertEquals(TestResultResult::negative(), $result->result);
        $this->assertEquals(TestResultTypeOfTest::antigen(), $result->type_of_test);

        // Source always is BCO portal
        $this->assertEquals(TestResultSource::manual(), $result->source);

        // Object is linked properly
        $this->assertEquals($this->case->organisation->uuid, $result->organisation->uuid);
        $this->assertEquals($this->case->uuid, $result->covidCase->uuid);
    }

    public function testDeletion(): void
    {
        $testResult = $this->createTestResultForCase($this->case, [
            'source' => TestResultSource::manual(),
        ]);

        $this->testResultService->deleteByUuid($testResult->uuid, $this->case);

        $this->expectException(ModelNotFoundException::class);
        $this->testResultService->getByUuid($testResult->uuid);
    }

    public function testDeleteForbiddenSource(): void
    {
        $testResult = $this->createTestResultForCase($this->case, [
            'source' => TestResultSource::meldportaal(),
        ]);

        $this->expectException(TestResultNotDeletable::class);
        $this->testResultService->deleteByUuid($testResult->uuid, $this->case);
    }

    public function testDeleteCaseMismatch(): void
    {
        $testResult = $this->createTestResult();

        $this->expectException(TestResultNotDeletable::class);
        $this->testResultService->deleteByUuid($testResult->uuid, $this->case);
    }

    public function testCustomTypeWithAdditionalData(): void
    {
        $testResult = $this->createTestResult();
        $custom = $this->faker->word();
        $testResult->setTypeOfTest(TestResultTypeOfTest::custom(), $custom);
        $testResult->save();

        $result = $this->testResultService->getByUuid($testResult->uuid);
        $this->assertEquals($result->customTypeOfTest, $custom);
    }
}

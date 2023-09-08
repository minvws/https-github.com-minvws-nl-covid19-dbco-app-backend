<?php

declare(strict_types=1);

namespace Tests\Feature\Repositories\TestResult;

use App\Repositories\TestResult\DbTestResultRepository;
use MinVWS\DBCO\Enum\Models\TestResult;
use MinVWS\DBCO\Enum\Models\TestResultType;
use Tests\Feature\FeatureTestCase;
use Tests\ModelCreator;

use function now;

class DbTestResultRepositoryTest extends FeatureTestCase
{
    use ModelCreator;

    public readonly DbTestResultRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->app->make(DbTestResultRepository::class);
    }

    public function testItGetsTheLatestPositiveTestResult(): void
    {
        $case = $this->createCase();
        $case->testResults()->saveMany([
            $latestPositiveTest = $this->createTestResult(
                ['result' => TestResult::positive(), 'date_of_test' => $this->faker->dateTimeThisMonth],
            ),
            $this->createTestResult(['result' => TestResult::positive(), 'date_of_test' => $this->faker->dateTime('1 month ago')]),
            $this->createTestResult(['result' => TestResult::negative(), 'date_of_test' => $this->faker->dateTime('1 month ago')]),
        ]);

        $test = $this->repository->latestPositiveForCase($case);
        $this->assertEquals($latestPositiveTest->dateOfTest, $test->dateOfTest);
        $this->assertEquals($latestPositiveTest->uuid, $test->uuid);
    }

    public function testItGetsTheFirstPositiveTestResultFromLab(): void
    {
        $case = $this->createCase();
        $case->testResults()->saveMany([
            $firstPositiveTest = $this->createTestResult(
                [
                    'result' => TestResult::positive(),
                    'type' => TestResultType::lab(),
                    'date_of_test' => $this->faker->dateTime(
                        '1 month ago',
                    )],
            ),
            $this->createTestResult(
                ['result' => TestResult::positive(), 'type' => TestResultType::lab(), 'date_of_test' => $this->faker->dateTimeThisMonth],
            ),
            $this->createTestResult(
                ['result' => TestResult::positive(), 'type' => TestResultType::selftest(), 'date_of_test' => $this->faker->dateTimeThisMonth],
            ),
            $this->createTestResult(['result' => TestResult::negative(), 'date_of_test' => $this->faker->dateTimeThisMonth]),
        ]);

        $test = $this->repository->firstPositiveResultByTypeForCase($case, TestResultType::lab());
        $this->assertEquals($firstPositiveTest->dateOfTest, $test->dateOfTest);
        $this->assertEquals($firstPositiveTest->uuid, $test->uuid);
    }

    public function testItReturnsNullWhenNoTestResultExists(): void
    {
        $case = $this->createCase();
        $this->assertNull($this->repository->latestPositiveForCase($case));
    }

    public function testItGetsTheLatestPositiveTestResultWhenAMoreRecentNegativeTestResultExists(): void
    {
        $case = $this->createCase();
        $case->testResults()->saveMany([
            $latestPositiveTest = $this->createTestResult(
                ['result' => TestResult::positive(), 'date_of_test' => $this->faker->dateTimeThisMonth],
            ),
            $this->createTestResult(['result' => TestResult::negative(), 'date_of_test' => now()]),
        ]);

        $test = $this->repository->latestPositiveForCase($case);
        $this->assertEquals($latestPositiveTest->dateOfTest, $test->dateOfTest);
        $this->assertEquals($latestPositiveTest->uuid, $test->uuid);
    }
}

<?php

declare(strict_types=1);

namespace Tests\Unit\Services\TestResult\Factories\Models\CovidCase;

use App\Dto\TestResultReport\TestResultReport;
use App\Dto\TestResultReport\TypeOfTest;
use App\Services\TestResult\Factories\Models\CovidCase\TestFactory;
use MinVWS\DBCO\Enum\Models\InfectionIndicator;
use MinVWS\DBCO\Enum\Models\SelfTestIndicator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\DataProvider\TestResultDataProvider;
use Tests\Unit\UnitTestCase;

class TestFactoryTest extends UnitTestCase
{
    #[DataProvider('dpMapTypeOfTest')]
    public function testMapTypeOfTest(
        ?TypeOfTest $typeOfTest,
        InfectionIndicator $expectedInfectionIndicator,
        ?SelfTestIndicator $expectedSelfTestIndicator,
    ): void {
        $payload = TestResultDataProvider::payload();
        $payload['test']['typeOfTest'] = $typeOfTest?->value;

        $testFragment = TestFactory::create(TestResultReport::fromArray($payload));
        $this->assertSame($expectedInfectionIndicator, $testFragment->infectionIndicator);
        $this->assertSame($expectedSelfTestIndicator, $testFragment->selfTestIndicator);
    }

    public static function dpMapTypeOfTest(): array
    {
        return [
            'self-test' => [
                TypeOfTest::SELF_TEST,
                InfectionIndicator::selfTest(),
                SelfTestIndicator::unknown(),
            ],
            'lab-test pcr' => [
                TypeOfTest::LAB_TEST_PCR,
                InfectionIndicator::labTest(),
                null,
            ],
            'lab-test antigen' => [
                TypeOfTest::LAB_TEST_ANTIGEN,
                InfectionIndicator::labTest(),
                null,
            ],
            'unknown' => [null, InfectionIndicator::unknown(), null],
        ];
    }
}

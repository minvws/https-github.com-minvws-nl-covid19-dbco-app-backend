<?php

declare(strict_types=1);

namespace Tests\Unit\Services\TestResult\Factories\Models;

use App\Dto\TestResultReport\TestResultReport;
use App\Services\TestResult\Factories\Models\NameAndAddressFactory;
use Tests\DataProvider\TestResultDataProvider;
use Tests\Unit\UnitTestCase;

final class NameAndAddressFactoryTest extends UnitTestCase
{
    public function testCreateFragmentWithInitials(): void
    {
        $initials = $this->faker->word();

        $payload = TestResultDataProvider::payload();
        $payload['person']['initials'] = $initials;

        $testResultReport = TestResultReport::fromArray($payload);

        $nameAndAddress = NameAndAddressFactory::create($testResultReport->person);
        $this->assertEquals($initials, $nameAndAddress->initials);
    }
}

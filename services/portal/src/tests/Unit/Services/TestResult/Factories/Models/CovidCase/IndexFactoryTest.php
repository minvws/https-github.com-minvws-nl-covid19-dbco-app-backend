<?php

declare(strict_types=1);

namespace Tests\Unit\Services\TestResult\Factories\Models\CovidCase;

use App\Dto\TestResultReport\TestResultReport;
use App\Repositories\Bsn\Dto\PseudoBsn;
use App\Services\TestResult\Factories\Models\CovidCase\IndexFactory;
use Tests\DataProvider\TestResultDataProvider;
use Tests\Unit\UnitTestCase;

final class IndexFactoryTest extends UnitTestCase
{
    public function testCreateFragmentWithInitials(): void
    {
        $initials = $this->faker->word();

        $payload = TestResultDataProvider::payload();
        $payload['person']['initials'] = $initials;

        $testResultReport = TestResultReport::fromArray($payload);

        $index = IndexFactory::create(
            $testResultReport->person,
            new PseudoBsn('', '', ''),
        );

        $this->assertEquals($initials, $index->initials);
    }
}

<?php

declare(strict_types=1);

namespace Tests\Unit\Dto\Admin;

use App\Dto\Admin\CreatePolicyVersionDto;
use Carbon\CarbonImmutable;
use MinVWS\DBCO\Enum\Models\PolicyVersionStatus;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UnitTestCase;

#[Group('policy')]
#[Group('policyVersion')]
class CreatePolicyVersionDtoTest extends UnitTestCase
{
    public function testItCanBeInitialized(): void
    {
        $dto = new CreatePolicyVersionDto(
            name: $this->faker->word(),
            startDate: CarbonImmutable::instance($this->faker->dateTimeBetween('-1 year', '+1 year')),
        );

        $this->assertInstanceOf(CreatePolicyVersionDto::class, $dto);
    }

    public function testDefaultValueForStatusIsDraft(): void
    {
        $dto = new CreatePolicyVersionDto(
            name: $this->faker->word(),
            startDate: CarbonImmutable::instance($this->faker->dateTimeBetween('-1 year', '+1 year')),
        );

        $this->assertSame(PolicyVersionStatus::draft(), $dto->status);
    }

    public function testToEloquentAttributes(): void
    {
        $dto = new CreatePolicyVersionDto(
            name: $name = $this->faker->word(),
            startDate: $startDate = CarbonImmutable::instance($this->faker->dateTimeBetween('-1 year', '+1 year')),
            status: $status = $this->faker->randomElement(PolicyVersionStatus::all()),
        );

        $this->assertEqualsCanonicalizing([
            'name' => $name,
            'start_date' => $startDate,
            'status' => $status,
        ], $dto->toEloquentAttributes());
    }
}

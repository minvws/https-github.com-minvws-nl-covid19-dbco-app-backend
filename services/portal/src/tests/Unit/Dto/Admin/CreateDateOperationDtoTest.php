<?php

declare(strict_types=1);

namespace Tests\Unit\Dto\Admin;

use App\Dto\Admin\CreateDateOperationDto;
use MinVWS\DBCO\Enum\Models\ContactOriginDate;
use MinVWS\DBCO\Enum\Models\DateOperationIdentifier;
use MinVWS\DBCO\Enum\Models\DateOperationMutation;
use MinVWS\DBCO\Enum\Models\IndexOriginDate;
use MinVWS\DBCO\Enum\Models\UnitOfTime;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UnitTestCase;

#[Group('policy')]
#[Group('dateOperation')]
final class CreateDateOperationDtoTest extends UnitTestCase
{
    public function testItCanBeInitialized(): void
    {
        $dto = new CreateDateOperationDto(
            identifier: $this->faker->randomElement(DateOperationIdentifier::all()),
            mutation: $this->faker->randomElement(DateOperationMutation::all()),
            amount: $this->faker->numberBetween(1, 100),
            unitOfTime: $this->faker->randomElement(UnitOfTime::all()),
            originDate: $this->faker->randomElement([...IndexOriginDate::all(), ...ContactOriginDate::all()]),
        );

        $this->assertInstanceOf(CreateDateOperationDto::class, $dto);
    }

    public function testToEloquentAttributes(): void
    {
        $dto = new CreateDateOperationDto(
            identifier: $identifier = $this->faker->randomElement(DateOperationIdentifier::all()),
            mutation: $mutation = $this->faker->randomElement(DateOperationMutation::all()),
            amount: $amount = $this->faker->numberBetween(1, 100),
            unitOfTime: $unitOfTime = $this->faker->randomElement(UnitOfTime::all()),
            originDate: $originDate = $this->faker->randomElement([...IndexOriginDate::all(), ...ContactOriginDate::all()]),
        );

        $this->assertEqualsCanonicalizing([
            'identifier_type' => $identifier,
            'mutation_type' => $mutation,
            'amount' => $amount,
            'unit_of_time_type' => $unitOfTime,
            'origin_date_type' => $originDate,
        ], $dto->toEloquentAttributes());
    }
}

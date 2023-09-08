<?php

declare(strict_types=1);

namespace Database\Factories\Eloquent;

use App\Models\Eloquent\Person;
use App\Models\Person\NameAndAddress;
use App\Models\Versions\Person\NameAndAddress\NameAndAddressV1;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Person>
 */
class PersonFactory extends Factory
{
    protected $model = Person::class;

    public function definition(): array
    {
        $dateOfBirth = $this->faker->dateTime();

        return [
            'uuid' => $this->faker->uuid(),
            'schema_version' => Person::getSchema()->getCurrentVersion()->getVersion(),
            'date_of_birth' => $dateOfBirth,
            'date_of_birth_encrypted' => $this->faker->optional()->passthrough(CarbonImmutable::instance($dateOfBirth)),
        ];
    }

    public function configure(): self
    {
        return $this->afterMaking(function (Person $person): void {
            $dateOfBirth = $person->date_of_birth ?? $this->faker->dateTime();

            /** @var NameAndAddressV1 $nameAndAddress */
            $nameAndAddress = NameAndAddress::getSchema()->getVersion(1)->newInstance();
            $nameAndAddress->firstname = $this->faker->firstName();
            $nameAndAddress->lastname = $this->faker->lastName();
            $nameAndAddress->dateOfBirth = $dateOfBirth;

            $person->nameAndAddress = $nameAndAddress;
            $person->date_of_birth = $dateOfBirth;
        });
    }
}

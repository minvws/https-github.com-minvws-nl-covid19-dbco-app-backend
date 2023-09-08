<?php

declare(strict_types=1);

namespace Tests\Feature\Models\Eloquent;

use App\Models\Eloquent\Person;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use Tests\Feature\FeatureTestCase;

class PersonTest extends FeatureTestCase
{
    public function testPersonDateOfBirth(): void
    {
        $dateOfBirth = $this->faker->dateTime();

        $uuid = Uuid::uuid4()->getBytes();

        DB::table('person')
            ->insert([
                'uuid' => $uuid,
                'schema_version' => 1,
                'date_of_birth' => $dateOfBirth,
                'date_of_birth_encrypted' => $this->faker->dateTime->format('Y-m-d H:i:s'),
                'created_at' => $this->faker->dateTime(),
                'updated_at' => $this->faker->dateTime(),
            ]);

        /** @var Person $person */
        $person = Person::query()
            ->where('uuid', $uuid)
            ->firstOrFail();

        self::assertTrue($person->date_of_birth->equalTo($dateOfBirth));
    }

    public function testPersonDateOfBirthUpdatesAlsoSavesEncrypted(): void
    {
        $uuid = Uuid::uuid4()->getBytes();

        DB::table('person')
            ->insert([
                'uuid' => $uuid,
                'schema_version' => 1,
                'date_of_birth' => $this->faker->dateTime(),
                'date_of_birth_encrypted' => $this->faker->dateTime->format('Y-m-d H:i:s'),
                'created_at' => $this->faker->dateTime(),
                'updated_at' => $this->faker->dateTime(),
            ]);

        /** @var Person $person */
        $person = Person::query()
            ->where('uuid', $uuid)
            ->firstOrFail();

        $dateOfBirth = $this->faker->dateTime();
        $person->date_of_birth = $dateOfBirth;
        $person->save();

        self::assertTrue($person->date_of_birth->equalTo($dateOfBirth));
        self::assertTrue($person->date_of_birth_encrypted->equalTo($dateOfBirth));
    }
}

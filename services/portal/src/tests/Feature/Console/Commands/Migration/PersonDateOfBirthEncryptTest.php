<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands\Migration;

use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use Tests\Feature\FeatureTestCase;

class PersonDateOfBirthEncryptTest extends FeatureTestCase
{
    public function testCommandUpdatesDateOfBirthEncrypted(): void
    {
        $uuid = Uuid::uuid4()->getBytes();

        DB::table('person')
            ->insert([
                'uuid' => $uuid,
                'schema_version' => 1,
                'date_of_birth' => $this->faker->dateTime(),
                'date_of_birth_encrypted' => null,
                'created_at' => $this->faker->dateTime(),
                'updated_at' => $this->faker->dateTime(),
            ]);

        $this->artisan('migrate:data:person-date-of-birth-encrypt')
            ->assertExitCode(0)
            ->execute();

        $this->assertDatabaseMissing('person', [
            'uuid' => $uuid,
            'date_of_birth_encrypted' => null,
        ]);
    }
}

<?php

declare(strict_types=1);

namespace Database\Factories\Eloquent;

use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentOrganisation;
use App\Models\Eloquent\Person;
use App\Models\Eloquent\TestResult;
use Illuminate\Database\Eloquent\Factories\Factory;
use MinVWS\DBCO\Enum\Models\TestResultLaboratory;
use MinVWS\DBCO\Enum\Models\TestResultResult;
use MinVWS\DBCO\Enum\Models\TestResultSource;
use MinVWS\DBCO\Enum\Models\TestResultType;
use MinVWS\DBCO\Enum\Models\TestResultTypeOfTest;

use function sprintf;

class TestResultFactory extends Factory
{
    protected $model = TestResult::class;

    public function definition(): array
    {
        $typeOfTest = $this->faker->randomElement(TestResultTypeOfTest::all());

        return [
            'uuid' => $this->faker->uuid(),
            'schema_version' => TestResult::getSchema()->getCurrentVersion()->getVersion(),
            'organisation_uuid' => EloquentOrganisation::factory(),
            'person_id' => Person::factory(),
            'case_uuid' => EloquentCase::factory(),
            'type' => $this->faker->randomElement(TestResultType::all()),
            'source' => $this->faker->randomElement(TestResultSource::all()),
            'source_id' => $this->faker->optional()->uuid(),
            'monster_number' => sprintf(
                '%s%s%s',
                $this->faker->randomNumber(3, true),
                $this->faker->randomLetter(),
                $this->faker->numberBetween(0, 999_999_999_999),
            ),
            'date_of_test' => $this->faker->dateTimeBetween('-1 month'),
            'date_of_symptom_onset' => $this->faker->dateTimeBetween('-1 month'),
            'date_of_result' => $this->faker->dateTimeBetween('-1 month'),
            'received_at' => $this->faker->dateTimeBetween('-1 week'),
            'message_id' => $this->faker->uuid(),
            'type_of_test' => $typeOfTest,
            'custom_type_of_test' => $typeOfTest === TestResultTypeOfTest::custom() ? $this->faker->word() : null,
            'sample_location' => $this->faker->optional()->city(),
            'result' => $this->faker->randomElement(TestResultResult::all()),
            'laboratory' => $this->faker->randomElement(TestResultLaboratory::all()),
        ];
    }
}

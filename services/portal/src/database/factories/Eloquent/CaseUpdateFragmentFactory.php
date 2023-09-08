<?php

declare(strict_types=1);

namespace Database\Factories\Eloquent;

use App\Models\Eloquent\CaseUpdateFragment;
use App\Schema\Fields\Field;
use App\Schema\Fields\SchemaVersionField;
use App\Schema\Types\SchemaType;
use App\Services\Intake\IntakeConfig;
use Database\Factories\Faker\RandomSample;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Factories\Factory;
use MinVWS\Codable\EncodingContainer;
use MinVWS\Codable\EncodingContext;
use stdClass;

use function array_filter;

class CaseUpdateFragmentFactory extends Factory
{
    protected $model = CaseUpdateFragment::class;

    public function configure(): self
    {
        $this->faker->addProvider(new RandomSample($this->faker));

        return parent::afterMaking(function (CaseUpdateFragment $fragmentUpdate): void {
            $caseSchemaVersion = $fragmentUpdate->caseUpdate->case->getSchemaVersion();

            $fragmentSchemaVersion = $caseSchemaVersion
                ->getExpectedField($fragmentUpdate->name)
                ->getExpectedType(SchemaType::class)
                ->getSchemaVersion();

            $fragmentUpdate->version = $fragmentSchemaVersion->getVersion();

            $fragment = $fragmentSchemaVersion->getTestFactory()->make();

            /** @var array<Field> $allFields */
            $allFields = array_filter($fragmentSchemaVersion->getFields(), static fn (Field $f) => !$f instanceof SchemaVersionField);

            /** @var array<Field> $fields */
            $fields = $this->faker->randomSample($allFields, 3);

            $data = new stdClass();
            $context = new EncodingContext();
            $context->setMode(EncodingContext::MODE_STORE);
            $container = new EncodingContainer($data, $context);

            $data = new stdClass();
            foreach ($fields as $field) {
                $field->encode($container, $fragment);
            }

            $fragmentUpdate->data = (array) $data;
        });
    }

    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(Container::getInstance()->get(IntakeConfig::class)->getAllowedCaseFragments()),
        ];
    }
}

<?php

declare(strict_types=1);

namespace Tests\Unit\Schema;

use App\Schema\Entity;
use App\Schema\Schema;
use App\Schema\Types\IntType;
use App\Schema\Validation\Validator;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('schema')]
#[Group('schema-validation')]
class SchemaValidationTest extends TestCase
{
    public static function validationProvider(): Generator
    {
        yield "Default - valid null" => [null, true, null, true];
        yield "Default - invalid null" => [null, false, null, false];
        yield "Default - valid number" => [null, true, 5, true];
        yield "Default - invalid fatal" => [null, true, 4, false];
        yield "Default - invalid warning" => [null, true, 11, false];

        yield "Fatal - valid null" => [[Validator::FATAL], true, null, true];
        yield "Fatal - invalid null" => [[Validator::FATAL], false, null, false];
        yield "Fatal - valid number" => [[Validator::FATAL], true, 5, true];
        yield "Fatal - invalid fatal" => [[Validator::FATAL], true, 4, false];
        yield "Fatal - invalid warning" => [[Validator::FATAL], true, 11, true];

        yield "Warning - valid null" => [[Validator::WARNING], true, null, true];
        yield "Warning - invalid null" => [[Validator::WARNING], false, null, false];
        yield "Warning - valid number" => [[Validator::WARNING], true, 5, true];
        yield "Warning - invalid fatal" => [[Validator::WARNING], true, 4, true];
        yield "Warning - invalid warning" => [[Validator::WARNING], true, 11, false];
    }

    #[DataProvider('validationProvider')]
    public function testValidation(?array $levels, bool $allowsNull, ?int $value, bool $valid): void
    {
        $schema = new Schema(Entity::class);
        $schema->add(IntType::createField('field'))
            ->setAllowsNull($allowsNull)
            ->getValidationRules()
            ->addFatal('min:5')
            ->addWarning('max:10');

        $validator = new Validator(null, $schema->getCurrentVersion()->getValidationRules());
        if ($levels !== null) {
            $validator->setLevels($levels);
        }

        $data = ['field' => $value];
        $result = $validator->validate($data);
        $this->assertEquals($valid, $result->isValid());
    }
}

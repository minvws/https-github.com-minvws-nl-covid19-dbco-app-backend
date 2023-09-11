<?php

declare(strict_types=1);

namespace Tests\Unit\Services\JsonSchema;

use App\Exceptions\JsonSchemaValidationException;
use App\Services\JsonSchema\SwaggestJsonSchemaValidator;
use Mockery;
use stdClass;
use Swaggest\JsonSchema\Exception;
use Swaggest\JsonSchema\Schema;
use Swaggest\JsonSchema\SchemaContract;
use Tests\Unit\UnitTestCase;

final class SwaggestJsonSchemaValidatorTest extends UnitTestCase
{
    /**
     * @throws JsonSchemaValidationException
     */
    public function testValidationFailureThrowsCorrectException(): void
    {
        $data = new stdClass();
        $schema = new stdClass();

        $schemaContract = Mockery::mock(SchemaContract::class);
        $schemaContract->expects('in')->andThrow(new Exception('fail'));

        $swaggestSchema = Mockery::mock(Schema::class);
        $swaggestSchema->expects('import')->andReturn($schemaContract);

        $swaggestJsonSchemaValidator = new SwaggestJsonSchemaValidator($swaggestSchema);

        $this->expectException(JsonSchemaValidationException::class);

        $swaggestJsonSchemaValidator->validate($data, $schema);
    }

    /**
     * @throws JsonSchemaValidationException
     */
    public function testValidationSuccessful(): void
    {
        $data = new stdClass();
        $schema = new stdClass();

        $schemaContract = Mockery::mock(SchemaContract::class);
        $schemaContract->expects('in');

        $swaggestSchema = Mockery::mock(Schema::class);
        $swaggestSchema->expects('import')->andReturn($schemaContract);

        $swaggestJsonSchemaValidator = new SwaggestJsonSchemaValidator($swaggestSchema);
        $swaggestJsonSchemaValidator->validate($data, $schema);
    }
}

<?php

declare(strict_types=1);

namespace App\Services\JsonSchema;

use App\Exceptions\JsonSchemaValidationException;
use Swaggest\JsonSchema\Schema;
use Throwable;

final class SwaggestJsonSchemaValidator implements JsonSchemaValidator
{
    public function __construct(
        private readonly Schema $schema,
    ) {
    }

    /**
     * @throws JsonSchemaValidationException
     */
    public function validate(mixed $data, mixed $schema): void
    {
        try {
            $schema = $this->schema::import($schema);
            $schema->in($data);
        } catch (Throwable $throwable) {
            throw JsonSchemaValidationException::fromThrowable($throwable);
        }
    }
}

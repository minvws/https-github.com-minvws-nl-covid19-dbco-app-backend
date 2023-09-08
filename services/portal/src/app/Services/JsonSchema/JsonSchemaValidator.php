<?php

declare(strict_types=1);

namespace App\Services\JsonSchema;

use App\Exceptions\JsonSchemaValidationException;

interface JsonSchemaValidator
{
    /**
     * @throws JsonSchemaValidationException
     */
    public function validate(mixed $data, mixed $schema): void;
}

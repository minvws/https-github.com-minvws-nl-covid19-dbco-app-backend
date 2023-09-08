<?php

declare(strict_types=1);

namespace App\Schema\Update;

use App\Schema\Fields\Field;
use App\Schema\SchemaObject;
use App\Schema\SchemaVersion;
use App\Schema\Validation\ValidationContext;
use App\Schema\Validation\ValidationResult;
use App\Schema\Validation\ValidationRules;
use App\Schema\Validation\Validator;
use Generator;
use MinVWS\Codable\CodableException;
use MinVWS\Codable\DecodingContainer;
use MinVWS\Codable\DecodingContext;

use function in_array;

/**
 * Represents a (partial) update for a certain schema version.
 */
class Update
{
    private SchemaVersion $schemaVersion;
    private array $data;

    /** @var array<string>|null */
    private ?array $fields = null;

    private ?DecodingContext $decodingContext = null;
    private ?DecodingContainer $decodingContainer = null;
    private ?ValidationContext $validationContext = null;

    public function __construct(SchemaVersion $schemaVersion, array $data)
    {
        $this->schemaVersion = $schemaVersion;
        $this->data = $data;
    }

    public function getSchemaVersion(): SchemaVersion
    {
        return $this->schemaVersion;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getDecodingContext(): DecodingContext
    {
        if ($this->decodingContext === null) {
            $this->decodingContext = new DecodingContext();
        }

        return $this->decodingContext;
    }

    public function setDecodingContext(DecodingContext $decodingContext): void
    {
        $this->decodingContext = $decodingContext;
    }

    public function getDecodingContainer(): DecodingContainer
    {
        if ($this->decodingContainer === null) {
            $this->decodingContainer = new DecodingContainer($this->data, $this->getDecodingContext());
        }

        return $this->decodingContainer;
    }

    /**
     * Only apply the update to the given fields.
     *
     * @param array<string>|null $fields
     */
    public function setFields(?array $fields = null): void
    {
        $this->fields = $fields;
    }

    protected function willUpdateField(Field $field): bool
    {
        if ($this->fields !== null && !in_array($field->getName(), $this->fields, true)) {
            return false;
        }

        return $field->willDecode($this->getDecodingContainer());
    }

    /**
     * @return Generator<Field>
     */
    public function filteredFields(): Generator
    {
        foreach ($this->getSchemaVersion()->getFields() as $field) {
            if ($this->willUpdateField($field)) {
                yield $field;
            }
        }
    }

    /**
     * @throws UpdateException
     */
    private function addFieldToDiff(SchemaObject $object, Field $field, UpdateDiff $diff): void
    {
        $oldValue = $field->assignedValue($object);
        $fieldContainer = $this->getDecodingContainer()->nestedContainer($field->getName());

        try {
            $newValue = $field->decodeValue($fieldContainer, null);
        } catch (CodableException $e) {
            throw new UpdateException($e->getMessage(), 0, $e);
        }

        if (!$field->valuesEqual($oldValue, $newValue)) {
            $diff->addFieldDiff(new UpdateFieldDiff($field, $oldValue, $newValue));
        }
    }

    /**
     * @throws UpdateValidationException|UpdateException
     */
    public function getDiff(SchemaObject $object): UpdateDiff
    {
        $this->validateWithException();

        $diff = new UpdateDiff($this);

        foreach ($this->filteredFields() as $field) {
            $this->addFieldToDiff($object, $field, $diff);
        }

        return $diff;
    }

    public function getValidationContext(): ValidationContext
    {
        if ($this->validationContext === null) {
            $this->validationContext = new ValidationContext();
        }

        return $this->validationContext;
    }

    public function setValidationContext(ValidationContext $validationContext): void
    {
        $this->validationContext = $validationContext;
    }

    public function validate(): ValidationResult
    {
        $rules = new ValidationRules();

        foreach ($this->filteredFields() as $field) {
            $rules->addChild($field->getValidationRules(), $field->getName());
        }

        $validator = new Validator($this->getValidationContext(), $rules);
        return $validator->validate($this->getData());
    }

    /**
     * Validates the data and throws an exception if there is a fatal-level data error.
     *
     * @throws UpdateValidationException
     */
    private function validateWithException(): void
    {
        $validationResult = $this->validate();
        if (!$validationResult->isLevelValid(ValidationContext::FATAL)) {
            throw new UpdateValidationException('Update contains invalid data!', $validationResult);
        }
    }

    /**
     * @throws UpdateValidationException|UpdateException
     */
    public function apply(SchemaObject $object): void
    {
        $this->validateWithException();

        try {
            foreach ($this->filteredFields() as $field) {
                /** @var Field $field */
                $field->decode($this->getDecodingContainer(), $object);
            }
        } catch (CodableException $e) {
            throw new UpdateException($e->getMessage(), 0, $e->getPrevious());
        }
    }
}

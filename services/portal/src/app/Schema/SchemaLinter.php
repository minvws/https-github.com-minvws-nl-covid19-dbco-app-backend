<?php

declare(strict_types=1);

namespace App\Schema;

use App\Schema\Fields\Field;
use Exception;
use RuntimeException;

use function array_key_exists;
use function array_merge;
use function array_unique;
use function is_a;
use function sprintf;

class SchemaLinter
{
    /**
     * @param array<SchemaProvider> $classes
     *
     * @return array<string>
     *
     * @throws Exception
     */
    public function lintClasses(array $classes): array
    {
        $errors = [];
        foreach ($classes as $class) {
            if (!is_a($class, SchemaProvider::class, true)) {
                throw new RuntimeException(
                    sprintf(
                        "%s does not implement %s interface!",
                        $class::class,
                        SchemaProvider::class,
                    ),
                );
            }

            $schema = $class::getSchema();
            $schemaName = $schema->getClass();
            $errors = array_merge($errors, $this->validateNoOverlappingFields($schema, $schemaName));
            $errors = array_merge($errors, $this->validateFieldSpecifications($schema, $schemaName));
        }
        return $errors;
    }

    /**
     * @return array<string>
     */
    public function validateNoOverlappingFields(Schema $schema, string $schemaName): array
    {
        $fieldMap = $this->createFieldMapFromSchema($schema);
        return $this->validateFieldMap($fieldMap, $schemaName);
    }

    /**
     * @return array<string>
     */
    public function validateFieldSpecifications(Schema $schema, string $schemaName): array
    {
        $errors = [];
        $schemaVersion = $schema->getCurrentVersion()->getVersion();
        foreach ($schema->getFields() as $field) {
            $errors = array_merge(
                $errors,
                $this->validateFieldMinSpecificationAgainstCurrentSchemaVersion($field, $schemaVersion, $schemaName),
            );
            $errors = array_merge(
                $errors,
                $this->validateFieldMaxSpecificationAgainstCurrentSchemaVersion($field, $schemaVersion, $schemaName),
            );
            $errors = array_merge($errors, $this->validateFieldMinMaxSpecification($field, $schemaName));
        }
        return $errors;
    }

    public function validateFieldMinSpecificationAgainstCurrentSchemaVersion(Fields\Field $field, int $schemaVersion, string $schemaName): array
    {
        if ($field->getMinVersion() > $schemaVersion) {
            return ["$schemaName has field {$field->getName()} with higher min version than the schema itself"];
        }
        return [];
    }

    public function validateFieldMaxSpecificationAgainstCurrentSchemaVersion(Fields\Field $field, int $schemaVersion, string $schemaName): array
    {
        if ($field->getMaxVersion() > $schemaVersion) {
            return ["$schemaName has field {$field->getName()} with higher max version than the schema itself"];
        }
        return [];
    }

    public function validateFieldMinMaxSpecification(Fields\Field $field, string $schemaName): array
    {
        if ($field->getMaxVersion() !== null && $field->getMinVersion() > $field->getMaxVersion()) {
            return ["$schemaName has field {$field->getName()} with higher min version than it's own max"];
        }
        return [];
    }

    public function validateFieldsDontOverlapOnMaxAndMin(Field $fieldA, Field $fieldB, string $schemaName): array
    {
        if (
            $fieldA->getMinVersion() <= $fieldB->getMinVersion() &&
            ($fieldA->getMaxVersion() === null ||
             $fieldA->getMaxVersion() >= $fieldB->getMinVersion())
        ) {
            $fieldAMax = $fieldA->getMaxVersion() ?? "∞";
            return ["$schemaName has field {$fieldA->getName()} with overlapping specifications: A ({$fieldA->getMinVersion()}-{$fieldAMax}) & B ({$fieldB->getMinVersion()}-...)"];
        }
        return [];
    }

    /**
     * @return array<array<Fields\Field>>
     */
    private function createFieldMapFromSchema(Schema $schema): array
    {
        /** @var array<string,array<Field>> $fieldMap */
        $fieldMap = [];
        foreach ($schema->getFields() as $field) {
            $fieldName = $field->getName();
            if (array_key_exists($fieldName, $fieldMap)) {
                $fieldMap[$fieldName][] = $field;
            } else {
                $fieldMap[$fieldName] = [$field];
            }
        }
        return $fieldMap;
    }

    /**
     * @param array<array<Fields\Field>> $fieldMap
     *
     * @return array<int, string>
     */
    private function validateFieldMap(array $fieldMap, string $schemaName): array
    {
        $errors = [];
        foreach ($fieldMap as $fields) {
            foreach ($fields as $fieldA) {
                foreach ($fields as $fieldB) {
                    if ($fieldA === $fieldB) {
                        continue;
                    }
                    $errors = array_merge($errors, $this->validateFieldsDontOverlapOnMaxAndMin($fieldA, $fieldB, $schemaName));
                }
            }
        }
        // Remove possible duplicates due to nested loop
        return array_unique($errors);
    }
}

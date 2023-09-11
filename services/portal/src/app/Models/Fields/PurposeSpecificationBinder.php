<?php

declare(strict_types=1);

namespace App\Models\Fields;

use App\Schema\Purpose\PurposeSpecification;
use App\Schema\Purpose\PurposeSpecificationBuilder;
use App\Schema\Schema;
use Exception;

use function assert;
use function count;
use function explode;

class PurposeSpecificationBinder
{
    /** @var array<string, array<string,PurposeSpecification>> $content */
    public array $content;

    public function __construct(
        private ?ProvidesPurposeSpecificationArray $contentRetriever = null,
    ) {
        if ($contentRetriever === null) {
            $this->contentRetriever = new PlainCSVContentRetriever(__DIR__ . '/../../../resources/data/datacatalog.csv');
        }

        assert($this->contentRetriever !== null);
        $this->content = $this->contentRetriever->getContent();
    }

    public function bind(Schema $schema): Schema
    {
        foreach ($schema->getFields() as $field) {
            if ($field->hasPurposeSpecification()) {
                continue;
            }

            $identifier = $field->getDocumentationIdentifier();
            [$class, $fieldIdentifier] = self::splitIdentifier($identifier);

            if ($this->purposeSpecificationDoesNotExistInContentArray($class, $fieldIdentifier)) {
                continue;
            }

            $specificationForField = $this->content[$class][$fieldIdentifier];
            $field->specifyPurpose(static function (PurposeSpecificationBuilder $builder) use ($specificationForField): void {
                $builder->copyFromPurposeSpecification($specificationForField);
            });
        }

        return $schema;
    }

    /**
     * @throws Exception
     */
    private static function splitIdentifier(string $identifier): array
    {
        $explodedIdentifier = explode('.', $identifier);
        return match (count($explodedIdentifier)) {
            2 => [$explodedIdentifier[0], $explodedIdentifier[1]],
            3 => [$explodedIdentifier[0] . '.' . $explodedIdentifier[1], $explodedIdentifier[2]],
            default => throw new Exception(count($explodedIdentifier) . ' level nesting is not supported'),
        };
    }

    private function purposeSpecificationDoesNotExistInContentArray(
        string $class,
        string $fieldIdentifier,
    ): bool {
        return !isset($this->content[$class]) || !isset($this->content[$class][$fieldIdentifier]);
    }
}

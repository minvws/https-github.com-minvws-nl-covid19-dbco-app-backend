<?php

declare(strict_types=1);

namespace App\Http\Responses\Api\Catalog;

use App\Schema\Fields\Field;
use App\Schema\SchemaDiff;
use MinVWS\Codable\DecodingContext;
use MinVWS\Codable\EncodingContainer;
use MinVWS\Codable\EncodingContext;

use function array_filter;
use function assert;
use function count;

class FieldDecorator implements CatalogDecorator
{
    public function encode(object $value, EncodingContainer $container): void
    {
        assert($value instanceof Field);

        $container->name = $value->getName();
        $container->label = $value->getDocumentation()->getLabel();
        $container->shortDescription = $value->getDocumentation()->getShortDescription();
        $container->description = $value->getDocumentation()->getDescription();
        $container->type = $value->getType();
        $container->purposeSpecification = $value->getPurposeSpecification();

        $this->addConditions($value, $container);
        $this->addDiffResult($value, $container);
    }

    private function addConditions(Field $field, EncodingContainer $container): void
    {
        $conditions = [
            EncodingContext::MODE_STORE => $field->getEncodingCondition(EncodingContext::MODE_STORE),
            EncodingContext::MODE_OUTPUT => $field->getEncodingCondition(EncodingContext::MODE_OUTPUT),
            EncodingContext::MODE_EXPORT => $field->getEncodingCondition(EncodingContext::MODE_EXPORT),
            DecodingContext::MODE_LOAD => $field->getDecodingCondition(DecodingContext::MODE_LOAD),
            DecodingContext::MODE_INPUT => $field->getDecodingCondition(DecodingContext::MODE_INPUT),
        ];

        $conditions = array_filter($conditions);
        if (count($conditions) === 0) {
            return;
        }

        if (count($conditions) === 4) {
            $container->condition->all = (string) $conditions[EncodingContext::MODE_STORE];
        } else {
            foreach ($conditions as $type => $condition) {
                $container->condition->$type = (string) $condition;
            }
        }
    }

    private function addDiffResult(Field $field, EncodingContainer $container): void
    {
        $diff = $container->getContext()->getValue(self::DIFF);
        if (!$diff instanceof SchemaDiff) {
            return;
        }

        $container->diffResult = $diff->getResultForField($field);
    }
}

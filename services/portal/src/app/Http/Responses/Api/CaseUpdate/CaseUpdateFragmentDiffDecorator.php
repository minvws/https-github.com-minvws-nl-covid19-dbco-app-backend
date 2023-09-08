<?php

declare(strict_types=1);

namespace App\Http\Responses\Api\CaseUpdate;

use App\Models\CaseUpdate\CaseUpdateFragmentDiff;
use MinVWS\Codable\EncodableDecorator;
use MinVWS\Codable\EncodingContainer;

use function array_values;
use function assert;

class CaseUpdateFragmentDiffDecorator implements EncodableDecorator
{
    public function encode(object $value, EncodingContainer $container): void
    {
        assert($value instanceof CaseUpdateFragmentDiff);

        $schemaVersion = $value->getDiff()->getUpdate()->getSchemaVersion();

        $container->name = $value->getFragmentName();
        $container->label = $schemaVersion->getDocumentation()->getLabel();

        $fieldsContainer = $container->nestedContainer('fields');
        $fieldsContainer->getContext()->setValue(UpdateFieldDiffDecorator::ID_PREFIX, $value->getKey() . '.');
        $fieldsContainer->encodeArray(array_values($value->getDiff()->getFieldDiffs()));
    }
}

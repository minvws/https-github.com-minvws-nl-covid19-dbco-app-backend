<?php

declare(strict_types=1);

namespace App\Schema\JSONSchema\Decoders;

use App\Schema\Fields\Field;
use App\Schema\Purpose\PurposeSpecificationBuilder;
use App\Schema\Types\Type;
use App\Schema\Validation\ValidationRules;
use MinVWS\Codable\DecodableDecorator;
use MinVWS\Codable\DecodingContainer;

use function assert;
use function is_string;

class FieldDecoder implements DecodableDecorator
{
    private function decodePurposeSpecification(DecodingContainer $container, PurposeSpecificationBuilder $builder): void
    {
    }

    private function decodeValidationRules(DecodingContainer $container, ValidationRules $rules): void
    {
        if (!$container->contains('validationRules')) {
            return;
        }

        $levels = [ValidationRules::FATAL, ValidationRules::WARNING, ValidationRules::NOTICE];
        foreach ($levels as $level) {
            $levelRules = $container->{'validationRules'}->nestedContainer($level)->decodeArrayIfPresent('string') ?? [];
            foreach ($levelRules as $levelRule) {
                $rules->addRule($levelRule, $level);
            }
        }
    }

    public function decode(string $class, DecodingContainer $container, ?object $object = null): object
    {
        $name = $container->getKey();
        assert(is_string($name));

        $type = $container->decodeObject(Type::class);
        assert($type instanceof Type);

        $field = new Field($name, $type);

        $field->specifyPurpose(function (PurposeSpecificationBuilder $builder) use ($container): void {
            $this->decodePurposeSpecification($container, $builder);
        });

        $field->modifyValidationRules(function (ValidationRules $rules) use ($container): void {
            $this->decodeValidationRules($container, $rules);
        });

        return $field;
    }
}

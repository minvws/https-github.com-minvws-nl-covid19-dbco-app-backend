<?php

declare(strict_types=1);

namespace App\Schema\JSONSchema\Misc;

use App\Schema\Generator\JSONSchema\Context;
use App\Schema\Types\Type;
use MinVWS\Codable\DecodingContainer;
use RuntimeException;

class RefType extends Type
{
    public function __construct(public readonly string $defName, public readonly TypeDefs $typeDefs)
    {
        parent::__construct();
    }

    public function getType(): Type
    {
        $type = $this->typeDefs->get($this->defName);

        if ($type === null) {
            throw new RuntimeException('Referencing unknown type "' . $this->defName . '"!');
        }

        return $type;
    }

    public function isOfType(mixed $value): bool
    {
        return $this->getType()->isOfType($value);
    }

    public function decode(DecodingContainer $container, mixed $current): mixed
    {
        return $this->getType()->decode($container, $current);
    }

    public function getAnnotationType(): string
    {
        return $this->getType()->getAnnotationType();
    }

    public function getTypeScriptAnnotationType(): string
    {
        return $this->getType()->getTypeScriptAnnotationType();
    }

    public function toJSONSchema(Context $context): array
    {
        return $this->getType()->toJSONSchema($context);
    }
}

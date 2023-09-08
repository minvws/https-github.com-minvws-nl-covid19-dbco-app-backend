<?php

declare(strict_types=1);

namespace App\Schema\Fields;

use App\Schema\SchemaObject;
use App\Schema\Types\Type;
use Closure;
use MinVWS\Codable\EncodingContainer;
use MinVWS\Codable\EncodingContext;

use function call_user_func;

/**
 * A derived field is only included when encoding an object and uses a custom value callback for determining its value.
 * The value callback will be given the full source object.
 */
class DerivedField extends Field
{
    /**
     * @param Closure(SchemaObject, EncodingContext): mixed $valueCallback
     */
    public function __construct(string $name, Type $type, private readonly Closure $valueCallback)
    {
        parent::__construct($name, $type);

        $this->setIncludedInDecode(false);
        $this->setIncludedInValidate(false);
    }

    public function assign(object $target, mixed $value): void
    {
        // ignore
    }

    protected function encodeValue(EncodingContainer $container, mixed $value, SchemaObject $source): void
    {
        parent::encodeValue($container, call_user_func($this->valueCallback, $source, $container->getContext()), $source);
    }
}

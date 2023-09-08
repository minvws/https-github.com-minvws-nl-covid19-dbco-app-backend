<?php

declare(strict_types=1);

namespace App\Schema\Fields;

use App\Schema\SchemaObject;
use App\Schema\Types\ArrayType;
use App\Schema\Types\SchemaType;
use App\Schema\Types\Type;
use App\Schema\Validation\ValidationRules;
use Closure;
use RuntimeException;

/**
 * A field that holds an array of elements of a certain type.
 */
class ArrayField extends Field
{
    private ValidationRules $elementValidationRules;

    public function __construct(string $name, Type $elementType)
    {
        parent::__construct($name, new ArrayType($elementType));

        $this->elementValidationRules = new ValidationRules();

        $this->getValidationRules()->addChild($this->getElementValidationRules(), '*');
    }

    /**
     * The element type.
     */
    public function getElementType(): Type
    {
        /** @var ArrayType $type */
        $type = $this->getType();
        return $type->getElementType();
    }

    /**
     * The element validation rules.
     */
    public function getElementValidationRules(): ValidationRules
    {
        return $this->elementValidationRules;
    }

    /**
     * Modify the element validation rules using the given closure.
     *
     * @param Closure $modifier Modifier will be called with the element validation rules instance.
     *
     * @return $this
     */
    public function modifyElementValidationRules(Closure $modifier): self
    {
        $modifier($this->getElementValidationRules());
        return $this;
    }

    public function newInstance(): SchemaObject
    {
        $type = $this->getElementType();
        if (!($type instanceof SchemaType)) {
            throw new RuntimeException(__METHOD__ . ' called on non-schema type!');
        }

        return $type->getSchemaVersion()->newInstance();
    }
}

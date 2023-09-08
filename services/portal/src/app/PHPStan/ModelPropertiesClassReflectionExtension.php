<?php

declare(strict_types=1);

namespace App\PHPStan;

use App\Schema\Schema;
use App\Schema\SchemaProvider;
use Illuminate\Database\Eloquent\Model;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\PropertiesClassReflectionExtension;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\TrinaryLogic;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;

/**
 * PHPStan reflection extension so we don't have to register Eloquent models as universal object crate as this
 * interferes with documented properties.
 *
 * Should be removed once all models have their properties properly documented using a schema.
 */
class ModelPropertiesClassReflectionExtension implements PropertiesClassReflectionExtension
{
    public function hasProperty(ClassReflection $classReflection, string $propertyName): bool
    {
        if (!$classReflection->isSubclassOf(Model::class)) {
            return false;
        }

        if (!$classReflection->implementsInterface(SchemaProvider::class)) {
            return true;
        }

        $class = $classReflection->getName();
        /** @var Schema $schema */
        $schema = $class::getSchema();
        foreach ($schema->getFields() as $field) {
            if ($field->getName() === $propertyName) {
                // property should already have a phpdoc based type description
                return false;
            }
        }

        return true;
    }

    public function getProperty(ClassReflection $classReflection, string $propertyName): PropertyReflection
    {
        return new class ($classReflection) implements PropertyReflection
        {
            public function __construct(private ClassReflection $declaringClass)
            {
            }

            public function getDeclaringClass(): ClassReflection
            {
                return $this->declaringClass;
            }

            public function isStatic(): bool
            {
                return false;
            }

            public function isPrivate(): bool
            {
                return false;
            }

            public function isPublic(): bool
            {
                return true;
            }

            public function getDocComment(): ?string
            {
                return null;
            }

            public function getReadableType(): Type
            {
                return new MixedType();
            }

            public function getWritableType(): Type
            {
                return new MixedType();
            }

            public function canChangeTypeAfterAssignment(): bool
            {
                return false;
            }

            public function isReadable(): bool
            {
                return true;
            }

            public function isWritable(): bool
            {
                return true;
            }

            public function isDeprecated(): TrinaryLogic
            {
                return TrinaryLogic::createNo();
            }

            public function getDeprecatedDescription(): ?string
            {
                return null;
            }

            public function isInternal(): TrinaryLogic
            {
                return TrinaryLogic::createNo();
            }
        };
    }
}

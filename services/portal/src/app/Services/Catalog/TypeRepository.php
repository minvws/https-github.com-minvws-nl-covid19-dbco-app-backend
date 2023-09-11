<?php

declare(strict_types=1);

namespace App\Services\Catalog;

use App\Models\Catalog\Category;
use App\Models\Catalog\Filter;
use App\Models\Catalog\Options;
use App\Schema\Entity;
use App\Schema\Fragment;
use App\Schema\FragmentModel;
use App\Schema\Purpose\Purpose;
use App\Schema\SchemaProvider;
use App\Schema\Types\EnumVersionType;
use App\Schema\Types\SchemaType;
use App\Schema\Types\Type;
use Illuminate\Database\Eloquent\Model;
use MelchiorKokernoot\LaravelAutowireConfig\Config\Config;
use MinVWS\DBCO\Enum\Models\Enum;

use function array_filter;
use function array_keys;
use function array_merge;
use function assert;
use function in_array;
use function is_a;
use function property_exists;
use function strcmp;
use function stripos;
use function usort;

class TypeRepository
{
    /** @var ?array<Type> */
    private ?array $types = null;

    public function __construct(
        private readonly EnumTypeRepository $enumTypeRepository,
        /** @var array<int,class-string> */
        #[Config('schema.root')]
        private readonly array $schemaRoots,
        /** @var array<int,class-string> */
        #[Config('schema.classes')]
        private readonly array $schemaClasses,
    ) {
    }

    /**
     * @return array<EnumVersionType>
     */
    private function getEnumTypes(): array
    {
        return $this->enumTypeRepository->getEnumTypes();
    }

    /**
     * @return array<SchemaType>
     */
    private function getSchemaTypes(): array
    {
        $result = [];

        foreach ($this->schemaClasses as $class) {
            assert(is_a($class, SchemaProvider::class, true));
            $schema = $class::getSchema();
            $result[$class] = new SchemaType($schema->getCurrentVersion());
        }

        return $result;
    }

    /**
     * @return array<Type>
     */
    private function loadTypes(): array
    {
        if (!isset($this->types)) {
            $this->types = array_merge($this->getEnumTypes(), $this->getSchemaTypes());
        }

        return $this->types;
    }

    /**
     * @return array<Type>
     */
    public function getTypes(?Options $options = null): array
    {
        $elements = array_filter(
            $this->loadTypes(),
            fn(Type $type) => $options === null || $this->typeMatches($type, $options)
        );

        usort($elements, fn(Type $t1, Type $t2) => $this->typeCompare($t1, $t2));

        return $elements;
    }

    /**
     * @return array<string>
     */
    public function getClasses(): array
    {
        return array_keys($this->loadTypes());
    }

    private function typeMatches(Type $type, Options $options): bool
    {
        return
            $this->typeMatchesFilter($type, $options->filter) &&
            $this->typeMatchesQuery($type, $options->query) &&
            $this->typeMatchesPurpose($type, $options->purpose) &&
            $this->typeMatchesCategories($type, $options->categories);
    }

    private function typeMatchesFilter(Type $type, Filter $filter): bool
    {
        return match ($filter->value) {
            Filter::All->value => true,
            Filter::Main->value => in_array(
                match ($type::class) {
                    EnumVersionType::class => $type->getEnumVersion()->getEnumClass(),
                    SchemaType::class => $type->getSchemaVersion()->getSchema()->getClass(),
                    default => null,
                },
                $this->schemaRoots,
                true,
            )
        };
    }

    private function typeMatchesQuery(Type $type, ?string $query): bool
    {
        if ($query === null) {
            return true;
        }

        $haystacks = [];
        if ($type instanceof SchemaType) {
            $haystacks[] = $type->getSchemaVersion()->getSchema()->getName();
            $haystacks[] = $type->getSchemaVersion()->getSchema()->getDocumentation()->getLabel();
        } elseif ($type instanceof EnumVersionType) {
            $enumSchema = $type->getEnumVersion()->getSchema();
            assert(property_exists($enumSchema, 'phpClass'));

            $haystacks[] = $enumSchema->phpClass;
        }

        $haystacks = array_filter($haystacks, static fn($h) => !empty($h));
        foreach ($haystacks as $haystack) {
            if (stripos($haystack, $query) !== false) {
                return true;
            }
        }

        return false;
    }

    private function typeMatchesPurpose(Type $type, ?Purpose $purpose): bool
    {
        if ($purpose === null) {
            return true;
        }

        if (!$type instanceof SchemaType) {
            return false;
        }

        foreach ($type->getSchemaVersion()->getSchema()->getFields() as $field) {
            if ($field->getPurposeSpecification()->hasPurpose($purpose)) {
                return true;
            }
        }

        return false;
    }

    private function typeMatchesCategories(Type $type, ?array $categories): bool
    {
        if (empty($categories)) {
            return true;
        }

        if ($type instanceof EnumVersionType && in_array(Category::Enum, $categories, true)) {
            return true;
        }

        if (
            $type instanceof SchemaType
            && in_array(Category::Fragment, $categories, true)
            && (
                is_a($type->getSchemaVersion()->getSchema()->getClass(), Fragment::class, true)
                || is_a(
                    $type->getSchemaVersion()->getSchema()->getClass(),
                    FragmentModel::class,
                    true,
                )
            )
        ) {
            return true;
        }

        if (
            $type instanceof SchemaType
            && in_array(Category::Model, $categories, true)
            && is_a($type->getSchemaVersion()->getSchema()->getClass(), Model::class, true)
            && !is_a($type->getSchemaVersion()->getSchema()->getClass(), FragmentModel::class, true)
        ) {
            return true;
        }

        return
            $type instanceof SchemaType
            && in_array(Category::Entity, $categories, true)
            && is_a($type->getSchemaVersion()->getSchema()->getClass(), Entity::class, true)
            && !is_a($type->getSchemaVersion()->getSchema()->getClass(), Fragment::class, true);
    }

    private function typeCompare(Type $type1, Type $type2): int
    {
        return strcmp($this->getTypeName($type1), $this->getTypeName($type2));
    }

    private function getTypeName(Type $type): string
    {
        if ($type instanceof EnumVersionType) {
            $enumSchema = $type->getEnumVersion()->getSchema();
            assert(property_exists($enumSchema, 'phpClass'));

            return $enumSchema->phpClass;
        }

        if ($type instanceof SchemaType) {
            return $type->getSchemaVersion()->getSchema()->getName();
        }

        return $type->getAnnotationType();
    }

    public function getType(string $class, ?int $version): ?Type
    {
        if (is_a($class, SchemaProvider::class, true)) {
            $schema = $class::getSchema();
            $schemaVersion = $version === null ? $schema->getCurrentVersion() : $schema->getVersion($version);
            return new SchemaType($schemaVersion);
        }

        if (is_a($class, Enum::class, true)) {
            $enumVersion = $version === null ? $class::getCurrentVersion() : $class::getVersion($version);
            return new EnumVersionType($enumVersion);
        }

        return null;
    }
}

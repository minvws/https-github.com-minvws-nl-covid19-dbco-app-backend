<?php

declare(strict_types=1);

namespace App\Services\Export;

use App\Schema\Generator\JSONSchema\Config;
use App\Schema\Generator\JSONSchema\UseCompoundSchemas;
use App\Schema\Generator\JSONSchemaGenerator;
use App\Schema\SchemaProvider;
use App\Services\Catalog\EnumTypeRepository;
use Generator;
use MinVWS\DBCO\Enum\Models\Enum;
use RuntimeException;

use function array_keys;
use function assert;
use function base_path;
use function config;
use function count;
use function file_exists;
use function file_get_contents;
use function is_a;
use function is_array;
use function is_string;

class JSONSchemaService
{
    public function __construct(
        private readonly JSONSchemaGenerator $jsonSchemaGenerator,
        private readonly EnumTypeRepository $enumTypeRepository,
    ) {
    }

    /**
     * @codeCoverageIgnore Mocked in tests and don't want to write test data to this mounted path.
     */
    public function getJSONSchemaForPath(string $path): ?string
    {
        $fullPath = base_path(config('schema.output.json') . '/' . $path . '.schema.json');
        if (!file_exists($fullPath)) {
            return null;
        }

        $schema = file_get_contents($fullPath);
        return is_string($schema) ? $schema : null;
    }

    public function generateJSONSchemas(Config $config): Generator
    {
        return $this->generateJSONSchemasForClasses(
            $this->getSchemaClasses($config) + $this->getEnumClasses($config),
            function (string $class) use ($config): void {
                if (is_a($class, SchemaProvider::class, true)) {
                    $this->generateJSONSchemaForSchema($class, $config);
                } elseif (is_a($class, Enum::class, true)) {
                    $this->generateJSONSchemaForEnum($class, $config);
                }
            },
        );
    }

    public function generateJSONSchemasForSchemas(Config $config): Generator
    {
        return $this->generateJSONSchemasForClasses(
            $this->getSchemaClasses($config),
            fn (string $c) => $this->generateJSONSchemaForSchema($c, $config)
        );
    }

    public function generateJSONSchemasForEnums(Config $config): Generator
    {
        return $this->generateJSONSchemasForClasses(
            $this->getEnumClasses($config),
            fn (string $c) => $this->generateJSONSchemaForEnum($c, $config)
        );
    }

    /**
     * @return array<int,class-string<SchemaProvider>>
     */
    private function getSchemaClasses(Config $config): array
    {
        $classes = $config->getUseCompoundSchemas() !== UseCompoundSchemas::No ? config('schema.root') : config('schema.classes');

        assert(is_array($classes));

        $result = [];
        foreach ($classes as $class) {
            if (!is_string($class) || !is_a($class, SchemaProvider::class, true)) {
                throw new RuntimeException("$class does not implement " . SchemaProvider::class . " interface!");
            }

            $result[] = $class;
        }

        return $result;
    }

    /**
     * @return array<int,class-string<Enum>>
     */
    private function getEnumClasses(Config $config): array
    {
        if ($config->getUseCompoundSchemas() !== UseCompoundSchemas::No) {
            return [];
        }

        /** @var array<int,class-string<Enum>> $classes */
        $classes = array_keys($this->enumTypeRepository->getEnumTypes());
        return $classes;
    }

    /**
     * @param class-string<SchemaProvider> $class
     */
    private function generateJSONSchemaForSchema(string $class, Config $config): void
    {
        $schema = $class::getSchema();
        $this->jsonSchemaGenerator->generateForSchema($schema, $config);
    }

    /**
     * @param class-string<Enum> $class
     */
    private function generateJSONSchemaForEnum(string $class, Config $config): void
    {
        $this->jsonSchemaGenerator->generateForEnum($class, $config);
    }

    /**
     * @template T
     *
     * @param array<class-string<T>> $classes
     * @param callable(class-string<T> $class): void $callback
     */
    private function generateJSONSchemasForClasses(array $classes, callable $callback): Generator
    {
        foreach ($classes as $i => $class) {
            yield [$i, count($classes), $class];
            $callback($class);
        }

        yield [count($classes), count($classes), null];
    }
}

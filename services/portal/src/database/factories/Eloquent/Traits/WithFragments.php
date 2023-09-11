<?php

declare(strict_types=1);

namespace Database\Factories\Eloquent\Traits;

use App\Schema\Fragment;
use App\Schema\FragmentModel;
use App\Schema\SchemaVersion;
use App\Schema\Types\SchemaType;

use function array_merge;
use function collect;
use function is_a;

trait WithFragments
{
    protected function expandAttributes(array $definition): array
    {
        $fragments = collect($definition)->filter(static fn ($a) => $a instanceof FragmentModel)->all();
        return array_merge(parent::expandAttributes($definition), $fragments);
    }

    public function withFragments(): self
    {
        $class = $this->modelName();
        $schema = $class::getSchema();
        return $this->state(function (array $attrs) use ($schema) {
            $version = $attrs['schema_version'];
            $schemaVersion = $schema->getVersion($version);
            return $this->generateFragments($schemaVersion);
        });
    }

    protected function generateFragments(SchemaVersion $schemaVersion): array
    {
        $data = [];
        foreach ($schemaVersion->getFields() as $field) {
            $type = $field->getType();

            if (!$type instanceof SchemaType) {
                continue;
            }

            if (
                is_a($type->getSchemaVersion()->getSchema()->getClass(), Fragment::class, true) ||
                is_a($type->getSchemaVersion()->getSchema()->getClass(), FragmentModel::class, true)
            ) {
                $data[$field->getName()] = $type->getSchemaVersion()->getTestFactory()->make();
            }
        }

        return $data;
    }
}

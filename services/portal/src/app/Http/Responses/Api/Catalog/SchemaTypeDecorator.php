<?php

declare(strict_types=1);

namespace App\Http\Responses\Api\Catalog;

use App\Models\Catalog\Category;
use App\Schema\Fields\Field;
use App\Schema\Fragment;
use App\Schema\FragmentModel;
use App\Schema\Purpose\Purpose;
use App\Schema\SchemaVersion;
use App\Schema\Types\SchemaType;
use App\Schema\Types\Type;
use Illuminate\Database\Eloquent\Model;
use MinVWS\Codable\EncodingContainer;

use function array_filter;
use function array_values;
use function assert;
use function is_a;
use function route;
use function strcmp;
use function usort;

class SchemaTypeDecorator extends TypeDecorator
{
    public function encode(object $value, EncodingContainer $container): void
    {
        assert($value instanceof SchemaType);

        parent::encode($value, $container);

        $purpose = null;
        if ($container->getContext()->getValue(self::PURPOSE) instanceof Purpose) {
            $purpose = $container->getContext()->getValue(self::PURPOSE);
        }

        $this->encodeBase($value->getSchemaVersion(), $container, $purpose);

        if ($container->getContext()->getMode() === self::MODE_FULL) {
            $this->encodeFull($value->getSchemaVersion(), $container, $purpose);
        }
    }

    protected function getType(Type $type): string
    {
        return $this->getCategory($type)?->value ?? parent::getType($type);
    }

    protected function getCategory(Type $type): ?Category
    {
        assert($type instanceof SchemaType);
        $class = $type->getSchemaVersion()->getSchema()->getClass();

        if (is_a($class, Fragment::class, true) || is_a($class, FragmentModel::class, true)) {
            return Category::Fragment;
        }

        if (is_a($class, Model::class, true)) {
            return Category::Model;
        }

        return Category::Entity;
    }

    private function encodeBase(SchemaVersion $version, EncodingContainer $container, ?Purpose $purpose): void
    {
        $container->class = $version->getSchema()->getClass();
        $container->version = $version->getVersion();
        $container->name = $version->getSchema()->getName();

        if ($container->getContext()->getMode() === self::MODE_INDEX) {
            $container->label = $version->getSchema()->getDocumentation()->getLabel() ?? $version->getSchema()->getName();
            $container->shortDescription = $version->getSchema()->getDocumentation()->getShortDescription();
        } else {
            $container->label = $version->getDocumentation()->getLabel() ?? $version->getSchema()->getName();
            $container->version = $version->getVersion();
            $container->shortDescription = $version->getDocumentation()->getShortDescription();
        }

        $diffToType = $container->getContext()->getValue(self::DIFF_TO_TYPE);
        if ($diffToType instanceof SchemaType) {
            $container->diffToVersion = $diffToType->getSchemaVersion()->getVersion();
            $container->_links->self = $this->getVersionLink($version, $diffToType->getSchemaVersion(), $purpose);
        } else {
            $container->_links->self = $this->getVersionLink($version, null, $purpose);
        }
    }

    private function encodeFull(SchemaVersion $version, EncodingContainer $container, ?Purpose $purpose): void
    {
        $container->description = $version->getDocumentation()->getDescription();

        $diffToType = $container->getContext()->getValue(self::DIFF_TO_TYPE);
        $diff = null;

        if ($diffToType instanceof SchemaType) {
            $diff = $diffToType->getSchemaVersion()->diff($version);
            $fields = $diff->getAllFields();
        } else {
            $fields = array_values($version->getFields());
        }

        if ($purpose !== null) {
            $fields = $this->filterFieldsByPurpose($fields, $purpose);
        }

        usort($fields, fn (Field $a, Field $b) => $this->compareFields($a, $b));

        $fieldsContainer = $container->nestedContainer('fields');
        $fieldsContainer->getContext()->setMode(self::MODE_SUMMARY);
        $fieldsContainer->getContext()->setValue(self::DIFF, $diff);
        $fieldsContainer->encodeArray($fields);

        $this->encodeVersions($version, $container, $purpose);
    }

    /**
     * @param array<Field> $fields
     *
     * @return array<Field>
     */
    private function filterFieldsByPurpose(array $fields, Purpose $purpose): array
    {
        return array_filter($fields, static fn (Field $field) => $field->getPurposeSpecification()->hasPurpose($purpose));
    }

    private function compareFields(Field $a, Field $b): int
    {
        $result = strcmp($a->getName(), $b->getName());
        if ($result !== 0) {
            return $result;
        }

        if ($a->getMinVersion() < $b->getMinVersion()) {
            return -1;
        }

        return 1;
    }

    private function encodeVersions(SchemaVersion $version, EncodingContainer $container, ?Purpose $purpose): void
    {
        $this->encodeVersionInfo(
            $version->getSchema()->getCurrentVersion(),
            $version,
            $container->nestedContainer('currentVersion'),
            $purpose,
        );
        $this->encodeVersionInfo($version->getSchema()->getMinVersion(), $version, $container->nestedContainer('minVersion'), $purpose);
        $this->encodeVersionInfo($version->getSchema()->getMaxVersion(), $version, $container->nestedContainer('maxVersion'), $purpose);

        $diffableVersions = [];
        for ($v = $version->getSchema()->getMinVersion()->getVersion(); $v < $version->getVersion(); $v++) {
            $diffableVersions[] = $version->getSchema()->getVersion($v);
        }

        $container->nestedContainer('diffableVersions')->encodeArray(
            $diffableVersions,
            fn (EncodingContainer $c, SchemaVersion $v) => $this->encodeVersionInfo($v, $version, $c, $purpose)
        );
    }

    private function encodeVersionInfo(SchemaVersion $version, SchemaVersion $mainVersion, EncodingContainer $container, ?Purpose $purpose): void
    {
        $container->version = $version->getVersion();
        $container->_links->self = $this->getVersionLink($version, null, $purpose);

        if ($version->getVersion() < $mainVersion->getVersion()) {
            $container->_links->diff = $this->getVersionLink($mainVersion, $version, $purpose);
        }
    }

    private function getVersionLink(SchemaVersion $version, ?SchemaVersion $diffToVersion = null, ?Purpose $purpose = null): string
    {
        $params = [
            'class' => $version->getSchema()->getClass(),
            'version' => $version->getVersion(),
        ];

        if ($diffToVersion !== null) {
            $params['diffToVersion'] = $diffToVersion->getVersion();
        }

        if ($purpose !== null) {
            $params['purpose'] = $purpose->getIdentifier();
        }

        return route('api-catalogType', $params);
    }
}

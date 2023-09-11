<?php

declare(strict_types=1);

namespace App\Http\Responses\Api\Catalog;

use App\Models\Catalog\Category;
use App\Schema\Types\EnumVersionType;
use App\Schema\Types\Type;
use MinVWS\Codable\EncodingContainer;
use MinVWS\DBCO\Enum\Models\Enum;
use MinVWS\DBCO\Enum\Models\EnumVersion;

use function assert;
use function route;

class EnumTypeDecorator extends TypeDecorator
{
    public function encode(object $value, EncodingContainer $container): void
    {
        assert($value instanceof EnumVersionType);

        parent::encode($value, $container);

        $this->encodeBase($value->getEnumVersion(), $container);

        if ($container->getContext()->getMode() === self::MODE_FULL) {
            $this->encodeFull($value->getEnumVersion(), $container);
        }
    }

    protected function getType(Type $type): string
    {
        return Category::Enum->value;
    }

    protected function getCategory(Type $type): ?Category
    {
        return Category::Enum;
    }

    private function encodeBase(EnumVersion $version, EncodingContainer $container): void
    {
        $container->class = $version->getEnumClass();
        $container->version = $version->getVersion();

        $container->name = $version->getSchema()->phpClass;
        $container->label = $version->getSchema()->phpClass;
        $container->shortDescription = $version->getSchema()->description ?? null;

        $diffToType = $container->getContext()->getValue(self::DIFF_TO_TYPE);
        if ($diffToType instanceof EnumVersionType) {
            $container->diffToVersion = $diffToType->getEnumVersion()->getVersion();
            $container->_links->self = $this->getVersionLink($version, $diffToType->getEnumVersion());
        } else {
            $container->_links->self = $this->getVersionLink($version);
        }
    }

    private function encodeFull(EnumVersion $version, EncodingContainer $container): void
    {
        $fieldsContainer = $container->nestedContainer('options');
        $fieldsContainer->getContext()->setMode(self::MODE_SUMMARY);
        $fieldsContainer->encodeArray(
            $version->all(),
            fn (EncodingContainer $c, Enum $o) => $this->encodeOption($o, $c)
        );

        $this->encodeVersions($version, $container);
    }

    private function encodeVersions(EnumVersion $version, EncodingContainer $container): void
    {
        $class = $version->getEnumClass();

        $this->encodeVersion($class::getCurrentVersion(), $version, $container->nestedContainer('currentVersion'));
        $this->encodeVersion($class::getMinVersion(), $version, $container->nestedContainer('minVersion'));
        $this->encodeVersion($class::getMaxVersion(), $version, $container->nestedContainer('maxVersion'));

        $diffableVersions = [];
        for ($v = $class::getMinVersion()->getVersion(); $v < $version->getVersion(); $v++) {
            $diffableVersions[] = $class::getVersion($v);
        }

        $container->nestedContainer('diffableVersions')->encodeArray(
            $diffableVersions,
            fn (EncodingContainer $c, EnumVersion $v) => $this->encodeVersion($v, $version, $c)
        );
    }

    private function encodeVersion(EnumVersion $version, EnumVersion $mainVersion, EncodingContainer $container): void
    {
        $container->version = $version->getVersion();
        $container->_links->self = $this->getVersionLink($version);

        if ($version->getVersion() < $mainVersion->getVersion()) {
            $container->_links->diff = $this->getVersionLink($mainVersion, $version);
        }
    }

    private function getVersionLink(EnumVersion $version, ?EnumVersion $diffToVersion = null): string
    {
        $params = [
            'class' => $version->getEnumClass(),
            'version' => $version->getVersion(),
        ];

        if ($diffToVersion !== null) {
            $params['diffToVersion'] = $diffToVersion->getVersion();
        }

        return route('api-catalogType', $params);
    }

    private function encodeOption(Enum $option, EncodingContainer $container): void
    {
        $container->label = $option->label;
        $container->value = $option->value;

        if ($container->getContext()->getValue(self::DIFF_TO_TYPE)) {
            $container->diffResult = $this->diffResult($option, $container->getContext()->getValue(self::DIFF_TO_TYPE));
        }
    }

    private function diffResult(Enum $option, EnumVersionType $diffToType): string
    {
        $diffToVersion = $diffToType->getEnumVersion();

        if ($option->isInVersion($diffToVersion->getVersion())) {
            return 'unmodified';
        }

        if ($diffToVersion->tryFrom($option->value) !== null) {
            return 'modified';
        }

        if ($option->minVersion > $diffToVersion->getVersion()) {
            return 'added';
        }

        return 'removed';
    }
}

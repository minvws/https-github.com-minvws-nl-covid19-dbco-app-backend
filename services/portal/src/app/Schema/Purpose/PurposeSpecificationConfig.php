<?php

declare(strict_types=1);

namespace App\Schema\Purpose;

use App\Schema\Types\Type;

class PurposeSpecificationConfig
{
    private static ?self $config;

    private array $fallbackSubPurposeOverrideForType = [];

    /**
     * @param class-string<Purpose> $purposeType
     * @param class-string<SubPurpose> $subPurposeType
     */
    public function __construct(private readonly string $purposeType, private readonly string $subPurposeType)
    {
    }

    public static function getConfig(): self
    {
        if (!isset(self::$config)) {
            throw new PurposeSpecificationException('Config is not set');
        }

        return self::$config;
    }

    public static function setConfig(?self $config): void
    {
        self::$config = $config;
    }

    /**
     * @return class-string<Purpose>
     */
    public function getPurposeType(): string
    {
        return $this->purposeType;
    }

    /**
     * @return class-string<SubPurpose>
     */
    public function getSubPurposeType(): string
    {
        return $this->subPurposeType;
    }

    public function getFallbackSubPurposeOverrideForType(string $class): ?SubPurpose
    {
        return $this->fallbackSubPurposeOverrideForType[$class] ?? null;
    }

    /**
     * @param class-string<Type> $class
     */
    public function setFallbackSubPurposeOverrideForType(string $class, SubPurpose $subPurpose): void
    {
        $this->fallbackSubPurposeOverrideForType[$class] = $subPurpose;
    }
}

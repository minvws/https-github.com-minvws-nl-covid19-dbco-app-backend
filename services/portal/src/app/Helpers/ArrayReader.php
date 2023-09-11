<?php

declare(strict_types=1);

namespace App\Helpers;

use Webmozart\Assert\Assert;

final class ArrayReader
{
    public static function getArrayByKey(mixed $data, string|int $key): array
    {
        $value = self::getValueByKey($data, $key);
        Assert::isArray($value);

        return $value;
    }

    public static function getIntegerByKey(mixed $data, string|int $key): int
    {
        $value = self::getValueByKey($data, $key);
        Assert::integer($value);

        return $value;
    }

    public static function getIntegerOrNullByKey(mixed $data, string|int $key): ?int
    {
        $value = self::getValueByKey($data, $key);
        Assert::nullOrInteger($value);

        return $value;
    }

    public static function getStringByKey(mixed $data, string|int $key): string
    {
        $value = self::getValueByKey($data, $key);
        Assert::string($value);

        return $value;
    }

    public static function getStringOrNullByKey(mixed $data, string|int $key): ?string
    {
        $value = self::getValueByKey($data, $key);
        Assert::nullOrString($value);

        return $value;
    }

    private static function getValueByKey(mixed $data, string|int $key): mixed
    {
        Assert::isArray($data);
        Assert::keyExists($data, $key);

        return $data[$key];
    }
}

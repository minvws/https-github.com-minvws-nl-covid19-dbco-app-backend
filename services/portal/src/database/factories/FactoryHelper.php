<?php

declare(strict_types=1);

namespace Database\Factories;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\App;
use JsonException;
use MinVWS\Codable\JSONEncoder;
use MinVWS\Codable\ValueTypeMismatchException;
use MinVWS\DBCO\Encryption\Security\EncryptionHelper;
use MinVWS\DBCO\Encryption\Security\StorageTerm;

use function collect;
use function is_string;

class FactoryHelper
{
    /**
     * @throws JsonException
     * @throws ValueTypeMismatchException
     */
    public static function sealStoreValue(string|array $value, StorageTerm $storageTerm): string
    {
        $encryptionHelper = self::getEncryptionHelper();

        if (!is_string($value)) {
            $encoder = self::getEncoder();
            $value = $encoder->encode($value);
        }

        return $encryptionHelper->sealStoreValue($value, $storageTerm, CarbonImmutable::now());
    }

    /**
     * @throws JsonException
     * @throws ValueTypeMismatchException
     */
    public static function sealStoreValueVeryShort(string|array $value): string
    {
        return self::sealStoreValue($value, StorageTerm::veryShort());
    }

    /**
     * @throws JsonException
     * @throws ValueTypeMismatchException
     */
    public static function sealStoreValueShort(string|array $value): string
    {
        return self::sealStoreValue($value, StorageTerm::short());
    }

    /**
     * @throws JsonException
     * @throws ValueTypeMismatchException
     */
    public static function sealStoreValueLong(string|array $value): string
    {
        return self::sealStoreValue($value, StorageTerm::long());
    }

    public static function sealValuesVeryShort(object $model, array $values): object
    {
        return collect($values)->map(static function (string $value) use ($model): void {
            if ($model->$value !== null) {
                $model->$value = self::sealStoreValueVeryShort($model->$value);
            }
        });
    }

    public static function sealValuesShort(object $model, array $values): object
    {
        return collect($values)->map(static function (string $value) use ($model): void {
            if ($model->$value !== null) {
                $model->$value = self::sealStoreValueShort($model->$value);
            }
        });
    }

    public static function sealValuesLong(object $model, array $values): object
    {
        return collect($values)->map(static function (string $value) use ($model): void {
            if ($model->$value !== null) {
                $model->$value = self::sealStoreValueLong($model->$value);
            }
        });
    }

    private static function getEncryptionHelper(): EncryptionHelper
    {
        return App::make(EncryptionHelper::class);
    }

    private static function getEncoder(): JsonEncoder
    {
        return App::make(JSONEncoder::class);
    }
}

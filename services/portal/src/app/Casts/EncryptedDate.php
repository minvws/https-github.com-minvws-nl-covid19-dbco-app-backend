<?php

declare(strict_types=1);

namespace App\Casts;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use MinVWS\DBCO\Encryption\Security\EncryptionHelper;
use MinVWS\DBCO\Encryption\Security\StorageTerm;
use SodiumException;
use Webmozart\Assert\Assert;

use function app;

class EncryptedDate implements CastsAttributes
{
    /**
     * @inheritdoc
     *
     * @throws SodiumException
     */
    public function get($model, string $key, $value, array $attributes): ?CarbonImmutable
    {
        if ($value === null) {
            return null;
        }

        Assert::string($value);

        $unencryptedData = $this->getEncryptionHelper()->unsealStoreValue($value);

        return CarbonImmutable::fromSerialized($unencryptedData);
    }

    /**
     * @inheritdoc
     */
    public function set($model, string $key, $value, array $attributes): ?array
    {
        if ($value === null) {
            return null;
        }

        Assert::isInstanceOf($value, CarbonImmutable::class);

        $createdAtColumn = $model->getCreatedAtColumn();
        $createdAt = $model->{$createdAtColumn} ?? CarbonImmutable::now();
        $encryptedDate = $this->getEncryptionHelper()->sealStoreValue(
            $value->serialize(),
            StorageTerm::short(),
            $createdAt,
        );

        return [
            $createdAtColumn => $createdAt,
            $key => $encryptedDate,
        ];
    }

    private function getEncryptionHelper(): EncryptionHelper
    {
        return app(EncryptionHelper::class);
    }
}

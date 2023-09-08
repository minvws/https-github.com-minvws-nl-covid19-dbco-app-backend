<?php

namespace MinVWS\DBCO\Encryption\Security;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class SealedWithKey implements CastsAttributes
{
    private EncryptionHelper $encryptionHelper;

    public function __construct(private string $keyIdentifier)
    {
        $this->encryptionHelper = app(EncryptionHelper::class);
    }

    public function get($model, string $key, $value, array $attributes)
    {
        return $this->encryptionHelper->unsealOptionalStoreValue($value);
    }

    public function set($model, string $key, $value, array $attributes)
    {
        if (!is_string($value)) {
            return null;
        }

        return $this->encryptionHelper->sealStoreValueWithKey($value, $this->keyIdentifier);
    }
}

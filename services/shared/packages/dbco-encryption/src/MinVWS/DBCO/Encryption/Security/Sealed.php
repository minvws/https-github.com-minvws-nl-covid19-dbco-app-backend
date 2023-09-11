<?php

namespace MinVWS\DBCO\Encryption\Security;

use DateTimeImmutable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use RuntimeException;
use SodiumException;

/**
 * Cast for applications that use Eloquent.
 */
class Sealed implements CastsAttributes
{
    private StorageTerm $storageTerm;
    private string $referenceDateTimeAttributeName;
    private EncryptionHelper $encryptionHelper;

    /**
     * Create a new cast class instance.
     *
     * @param string $storageTerm
     * @param string $referenceDateTimeAttributeName
     */
    public function __construct(string $storageTerm, string $referenceDateTimeAttributeName)
    {
        switch ($storageTerm) {
            case StorageTerm::LONG:
                $this->storageTerm = StorageTerm::long();
                break;
            case StorageTerm::SHORT:
                $this->storageTerm = StorageTerm::short();
                break;
            case StorageTerm::VERY_SHORT:
                $this->storageTerm = StorageTerm::veryShort();
                break;
            default:
                throw new EncryptionException('invalid storageTerm');
        }

        $this->referenceDateTimeAttributeName = $referenceDateTimeAttributeName;
    }

    /**
     * @inheritDoc
     */
    public function get($model, string $key, $value, array $attributes)
    {
        try {
            return app(EncryptionHelper::class)->unsealOptionalStoreValue($value);
        } catch (SodiumException $e) {
            return null;
        } catch (EncryptionException $e) {
            // not a valid key anymore?
            return null;
        }
    }

    /**
     * Property path using "." notation.
     *
     * @param object|array $data
     * @param string       $path
     *
     * @return mixed|null
     */
    private function getValueForPath($data, string $path)
    {
        return array_reduce(
            explode('.', $path),
            function ($d, $p) {
                if (is_object($d)) {
                    return $d->$p ?? null;
                }

                if (is_array($d)) {
                    return $d[$p] ?? null;
                }

                return null;
            },
            $data
        );
    }

    /**
     * @inheritDoc
     */
    public function set($model, string $key, $value, array $attributes)
    {
        try {
            $referenceDateTime = $this->getValueForPath($attributes, $this->referenceDateTimeAttributeName);

            if ($referenceDateTime === null) {
                $referenceDateTime = $this->getValueForPath($model, $this->referenceDateTimeAttributeName);
            }

            if (is_string($referenceDateTime)) {
                $referenceDateTime = new DateTimeImmutable($referenceDateTime);
            }

            if ($referenceDateTime === null) {
                throw new RuntimeException('No value for reference date time attribute');
            }

            /** @var EncryptionHelper $app */
            $app = app(EncryptionHelper::class);
            return $app->sealOptionalStoreValue(
                $value,
                $this->storageTerm,
                $referenceDateTime
            );
        } catch (EncryptionException $e) {
            // not a valid key anymore?
            return null;
        }
    }
}

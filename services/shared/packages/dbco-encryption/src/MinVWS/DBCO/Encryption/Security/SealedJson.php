<?php

namespace MinVWS\DBCO\Encryption\Security;

/**
 * Seal/unseal JSON.
 */
class SealedJson extends Sealed
{
    public const DECODE_OBJECTS_AS_ASSOCIATIVE_ARRAY = 'associativeArray';
    public const DECODE_OBJECTS_AS_STDCLASS          = 'stdClass';

    private bool $associative;

    /**
     * Create a new cast class instance.
     *
     * @param string $storageTerm
     * @param string $referenceDateTimeAttributeName
     * @param string $associative
     */
    public function __construct(string $storageTerm, string $referenceDateTimeAttributeName, string $decodeObjectsAs = self::DECODE_OBJECTS_AS_STDCLASS)
    {
        parent::__construct($storageTerm, $referenceDateTimeAttributeName);
        $this->associative = $decodeObjectsAs === self::DECODE_OBJECTS_AS_ASSOCIATIVE_ARRAY;
    }

    /**
     * @inheritDoc
     */
    public function get($model, string $key, $value, array $attributes)
    {
        $json = parent::get($model, $key, $value, $attributes);
        if ($json === null) {
            return null;
        }

        return json_decode($json, $this->associative);
    }

    /**
     * @inheritDoc
     */
    public function set($model, string $key, $value, array $attributes)
    {
        $json = $value === null ? null : json_encode($value);
        return parent::set($model, $key, $json, $attributes);
    }
}

<?php

declare(strict_types=1);

namespace App\Schema\Types;

use App\Schema\Generator\JSONSchema\Context;
use App\Schema\Validation\ValidationRules;
use MinVWS\Codable\CodableException;
use MinVWS\Codable\DecodingContainer;
use MinVWS\Codable\EncodingContainer;

/**
 * Represents a field type
 */
abstract class Type
{
    private ValidationRules $validationRules;

    /** @var callable */
    private $encoder = null;

    /** @var array<callable> */
    private array $encoderByMode = [];

    /** @var callable */
    private $decoder = null;

    /** @var array<callable> */
    private array $decoderByMode = [];

    public function __construct()
    {
        $this->validationRules = new ValidationRules();
    }

    abstract public function isOfType(mixed $value): bool;

    /**
     * Set customer value encoder.
     *
     * @param callable $encoder Encoder.
     * @param string|null $mode EncodingContext::MODE_STORE / EncodingContext::MODE_OUTPUT
     */
    public function setEncoder(callable $encoder, ?string $mode = null): self
    {
        if ($mode === null) {
            $this->encoder = $encoder;
        } else {
            $this->encoderByMode[$mode] = $encoder;
        }

        return $this;
    }

    /**
     * Returns the encoder for the given mode.
     *
     * @param string|null $mode Encoding mode.
     * @param bool $fallback Fallback to general encoder (if registered).
     */
    public function getEncoder(?string $mode, bool $fallback = true): ?callable
    {
        if ($mode !== null && isset($this->encoderByMode[$mode])) {
            return $this->encoderByMode[$mode];
        }

        if ($mode !== null && !$fallback) {
            return null;
        }

        return $this->encoder;
    }

    /**
     * Encode the given value
     *
     * @throws CodableException
     */
    public function encode(EncodingContainer $container, mixed $value): void
    {
        $encoder = $this->getEncoder($container->getContext()->getMode());
        if ($encoder !== null) {
            $encoder($container, $value);
        } else {
            $container->encode($value);
        }
    }

    /**
     * Set decoder.
     *
     * @param callable $decoder Decoder.
     * @param string|null $mode DecodingContext::MODE_LOAD / DecodingContext::MODE_INPUT
     *
     * @return $this
     */
    public function setDecoder(callable $decoder, ?string $mode = null): self
    {
        if ($mode === null) {
            $this->decoder = $decoder;
        } else {
            $this->decoderByMode[$mode] = $decoder;
        }

        return $this;
    }

    /**
     * Get decoder for the given mode.
     */
    public function getDecoder(?string $mode): ?callable
    {
        return $this->decoderByMode[$mode] ?? $this->decoder;
    }

    /**
     * Decode value.
     *
     * @throws CodableException
     */
    public function decode(DecodingContainer $container, mixed $current): mixed
    {
        $decoder = $this->getDecoder($container->getContext()->getMode());
        if ($decoder !== null) {
            return $decoder($container, $current);
        }

        return $container->decodeIfPresent();
    }

    /**
     * The validation rules for this type.
     */
    public function getValidationRules(): ValidationRules
    {
        return $this->validationRules;
    }

    /**
     * The PHP type used in property annotations for this type.
     */
    abstract public function getAnnotationType(): string;

    /**
     * The TypeScript type used in property annotations for this type.
     */
    abstract public function getTypeScriptAnnotationType(): string;

    /**
     * Compare 2 values for this type.
     */
    public function valuesEqual(mixed $value1, mixed $value2): bool
    {
        if ($value1 !== null && !$this->isOfType($value1)) {
            return false;
        }

        if ($value2 !== null && !$this->isOfType($value2)) {
            return false;
        }

        // phpcs:ignore SlevomatCodingStandard.Operators.DisallowEqualOperators.DisallowedEqualOperator
        return $value1 == $value2;
    }

    /**
     * Describe this field type as JSON Schema.
     */
    abstract public function toJSONSchema(Context $context): array;
}

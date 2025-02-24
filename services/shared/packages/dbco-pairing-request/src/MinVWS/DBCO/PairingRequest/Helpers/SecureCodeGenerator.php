<?php

declare(strict_types=1);

namespace MinVWS\DBCO\PairingRequest\Helpers;

/**
 * Secure token generator.
 *
 * Uses PHP's random_int function which generates secure pseudo-random integers.
 *
 * @package DBCO\Shared\Application\Helpers
 */
final class SecureCodeGenerator implements CodeGenerator
{
    /**
     * @var string
     */
    protected string $allowedChars;

    /**
     * @var int
     */
    protected int $length;

    /**
     * Constructor.
     *
     * @param string $allowedChars
     * @param int    $length
     */
    public function __construct(string $allowedChars, int $length)
    {
        $this->allowedChars = $allowedChars;
        $this->length = $length;
    }

    /**
     * Generate token.
     *
     * @return string
     */
    public function generateCode(): string
    {
        try {
            $chars = [];

            $max = mb_strlen($this->allowedChars, '8bit') - 1;
            for ($i = 0; $i < $this->length; $i++) {
                $chars [] = $this->allowedChars[random_int(0, $max)];
            }

            return implode('', $chars);
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage(), $e->getCode(), $e);
        }
    }
}

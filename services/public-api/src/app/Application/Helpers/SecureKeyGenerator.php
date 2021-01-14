<?php
declare(strict_types=1);

namespace DBCO\PublicAPI\Application\Helpers;

use Exception;
use RuntimeException;

/**
 * Secure key generator.
 *
 * @package DBCO\PublicAPI\Application\Helpers
 */
final class SecureKeyGenerator implements KeyGenerator {
    /**
     * @var int
     */
    private int $length;

    /**
     * Constructor.
     *
     * @param int $length Key length.
     */
    public function __construct(int $length)
    {
        $this->length = $length;
    }

    /**
     * Generate key.
     *
     * @return string
     */
    public function generateKey(): string
    {
        try {
            $bytes = random_bytes($this->length);
            return base64_encode($bytes);
        } catch (Exception $e) {
            throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
        }
    }
}

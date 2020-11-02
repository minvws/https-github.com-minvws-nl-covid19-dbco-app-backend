<?php
declare(strict_types=1);

namespace DBCO\PrivateAPI\Application\Helpers;

/**
 * Token generator, mainly used to generate pairing codes.
 *
 * @package DBCO\PrivateAPI\Application\Helpers
 */
interface TokenGenerator
{
    /**
     * Generate token.
     *
     * @return string
     */
    public function generateToken(): string;
}

<?php
declare(strict_types=1);

namespace App\Application\Helpers;

/**
 * Token generator, mainly used to generate pairing codes.
 *
 * @package App\Application\Helpers
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

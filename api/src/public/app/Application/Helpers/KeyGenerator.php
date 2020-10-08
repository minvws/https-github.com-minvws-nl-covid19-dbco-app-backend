<?php
declare(strict_types=1);

namespace App\Application\Helpers;

/**
 * Key generator, mainly used to generate signing keys.
 *
 * @package App\Application\Helpers
 */
interface KeyGenerator
{
    /**
     * Generate key.
     *
     * @return string
     */
    public function generateKey(): string;
}

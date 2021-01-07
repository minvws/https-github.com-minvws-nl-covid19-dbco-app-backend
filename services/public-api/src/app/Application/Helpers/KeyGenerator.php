<?php
declare(strict_types=1);

namespace DBCO\PublicAPI\Application\Helpers;

/**
 * Key generator, mainly used to generate signing keys.
 *
 * @package DBCO\PublicAPI\Application\Helpers
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

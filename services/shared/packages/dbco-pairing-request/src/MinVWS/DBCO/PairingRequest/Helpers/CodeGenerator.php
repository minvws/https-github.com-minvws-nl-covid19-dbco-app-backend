<?php

declare(strict_types=1);

namespace MinVWS\DBCO\PairingRequest\Helpers;

/**
 * Code generator, mainly used to generate pairing codes.
 */
interface CodeGenerator
{
    /**
     * Generate token.
     *
     * @return string
     */
    public function generateCode(): string;
}

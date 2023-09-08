<?php

declare(strict_types=1);

namespace App\Services\Assignment\TokenEncoder;

/**
 * @template TEncodeObject of object
 */
interface TokenEncoder
{
    /**
     * @param TEncodeObject $payload
     */
    public function encode(object $payload): string;
}

<?php

declare(strict_types=1);

namespace App\Services\Assignment\TokenDecoder;

/**
 * @template TDecodeObject of object
 */
interface TokenDecoder
{
    /**
     * @return TDecodeObject
     */
    public function decode(string $token): object;
}

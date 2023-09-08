<?php

declare(strict_types=1);

namespace App\Services\Assignment\TokenProcessor;

/**
 * @template TPayloadObject of object
 * @template TFromPayloadReturnValue of object
 */
interface TokenProcessor
{
    /**
     * @param TPayloadObject $payload
     *
     * @return TFromPayloadReturnValue
     */
    public function fromPayload(object $payload): object;
}

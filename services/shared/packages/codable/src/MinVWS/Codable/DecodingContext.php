<?php

namespace MinVWS\Codable;

/**
 * Decoding context.
 *
 * Can be used to register decodable decorators and
 * contextual values for decoding.
 *
 * @package MinVWS\Codable
 */
class DecodingContext extends Context
{
    public const MODE_INPUT = 'input';
    public const MODE_LOAD  = 'load';

    public function createChildContext(): self
    {
        return new self($this);
    }
}

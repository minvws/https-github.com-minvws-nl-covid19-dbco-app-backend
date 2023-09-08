<?php

namespace MinVWS\Codable;

use JsonException;

/**
 * JSON decoder.
 *
 * @package MinVWS\Codable
 */
class JSONDecoder
{
    /**
     * @var Decoder
     */
    private Decoder $decoder;

    /**
     * Constructor.
     *
     * @param DecodingContext|null $context
     */
    public function __construct(?DecodingContext $context = null)
    {
        $this->decoder = new Decoder($context);
    }

    /**
     * Returns the decoder.
     *
     * @return Decoder
     */
    public function getDecoder(): Decoder
    {
        return $this->decoder;
    }

    /**
     * Returns the context.
     *
     * @return DecodingContext
     */
    public function getContext(): DecodingContext
    {
        return $this->decoder->getContext();
    }

    /**
     * Decode JSON string.
     *
     * @param string $json
     * @param bool   $associative
     * @param int    $depth
     * @param int    $flags
     *
     * @return DecodingContainer
     *
     * @throws CodableException
     * @throws JsonException
     */
    public function decode(string $json, bool $associative = false, int $depth = 512, int $flags = 0): DecodingContainer
    {
        $data = json_decode($json, $associative, $depth, $flags | JSON_THROW_ON_ERROR);
        return $this->decoder->decode($data);
    }
}

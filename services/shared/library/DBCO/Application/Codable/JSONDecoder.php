<?php
namespace DBCO\Shared\Application\Codable;

use JsonException;

/**
 * JSON decoder.
 *
 * @package DBCO\Shared\Application\Codable
 */
class JSONDecoder
{
    /**
     * @var Context
     */
    private Context $context;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->context = new Context();
    }

    /**
     * Returns the context.
     *
     * @return Context
     */
    public function getContext(): Context
    {
        return $this->context;
    }

    /**
     * Get value.
     *
     * @param string $data
     *
     * @return DecodingContainer
     *
     * @throws DecodeException
     */
    public function decode(string $data): DecodingContainer
    {
        try {
            $value = json_decode($data, false, 512, JSON_THROW_ON_ERROR);
            return new DecodingContainer($value, $this->context);
        } catch (JsonException $e) {
            throw new DecodeException('Invalid JSON', 0, $e);
        }
    }
}
<?php

namespace MinVWS\Codable;

use DateTimeInterface;
use stdClass;

/**
 * Decoder.
 *
 * @package MinVWS\Codable
 */
class Decoder
{
    /**
     * @var DecodingContext
     */
    private DecodingContext $context;

    /**
     * Built-in decorator for date time objects.
     *
     * @var DecodableDecorator
     */
    private DecodableDecorator $dateTimeDecorator;

    /**
     * Constructor.
     *
     * @param DecodingContext|null $context
     */
    public function __construct(?DecodingContext $context = null)
    {
        $this->context = $context ?? new DecodingContext();


        $this->dateTimeDecorator = new class implements DecodableDecorator {
            /**
             * @inheritDoc
             */
            public function decode(string $class, DecodingContainer $container, ?object $object = null): object
            {
                return $container->decodeDateTime(null, null, $class);
            }
        };

        $this->context->registerDecorator(DateTimeInterface::class, $this->dateTimeDecorator);
    }

    /**
     * Returns the context.
     *
     * @return DecodingContext
     */
    public function getContext(): DecodingContext
    {
        return $this->context;
    }

    /**
     * Decode data.
     *
     * @param array|stdClass Data to decode.
     *
     * @return DecodingContainer
     *
     * @throws CodableException
     */
    public function decode($data): DecodingContainer
    {
        return new DecodingContainer($data, $this->context);
    }
}

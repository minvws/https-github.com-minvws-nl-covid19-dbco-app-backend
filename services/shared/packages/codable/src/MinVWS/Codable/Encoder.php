<?php

namespace MinVWS\Codable;

use DateTimeInterface;
use Traversable;

/**
 * Encoder.
 *
 * @package MinVWS\Codable
 */
class Encoder
{
    /**
     * @var EncodingContext
     */
    private EncodingContext $context;

    /**
     * @var EncodableDecorator
     */
    private EncodableDecorator $dateTimeDecorator;

    /**
     * @var EncodableDecorator
     */
    private EncodableDecorator $traversableDecorator;

    /**
     * Constructor.
     *
     * @param EncodingContext|null $context
     */
    public function __construct(?EncodingContext $context = null)
    {
        $this->context = $context ?? new EncodingContext();

        $this->dateTimeDecorator = new class implements EncodableDecorator {
            /**
             * @inheritDoc
             */
            public function encode(object $value, EncodingContainer $container): void
            {
                $container->encodeDateTime($value);
            }
        };

        $this->traversableDecorator = new class implements EncodableDecorator {
            /**
             * @inheritDoc
             */
            public function encode(object $value, EncodingContainer $container): void
            {
                $container->encodeArray(iterator_to_array($value));
            }
        };

        $this->context->registerDecorator(DateTimeInterface::class, $this->dateTimeDecorator);
        $this->context->registerDecorator(Traversable::class, $this->traversableDecorator);
    }

    /**
     * Returns the context.
     *
     * @return EncodingContext
     */
    public function getContext(): EncodingContext
    {
        return $this->context;
    }

    /**
     * Encodes the given data
     *
     * @param $data
     *
     * @return mixed
     *
     * @throws ValueTypeMismatchException
     */
    public function encode($data)
    {
        $value = null;
        $container = new EncodingContainer($value, $this->context);
        $container->encode($data);
        return $value;
    }
}

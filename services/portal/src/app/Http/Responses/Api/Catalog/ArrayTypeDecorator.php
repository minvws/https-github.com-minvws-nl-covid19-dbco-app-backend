<?php

declare(strict_types=1);

namespace App\Http\Responses\Api\Catalog;

use App\Schema\Types\ArrayType;
use MinVWS\Codable\CodableException;
use MinVWS\Codable\EncodingContainer;

use function assert;

class ArrayTypeDecorator extends TypeDecorator
{
    /**
     * @throws CodableException
     */
    public function encode(object $value, EncodingContainer $container): void
    {
        assert($value instanceof ArrayType);

        parent::encode($value, $container);

        $container->elementType = $value->getElementType();
    }
}

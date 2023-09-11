<?php

declare(strict_types=1);

namespace App\Http\Responses\Api\Catalog;

use App\Models\Catalog\Category;
use App\Schema\Types\ScalarType;
use App\Schema\Types\Type;
use MinVWS\Codable\EncodingContainer;

use function assert;
use function preg_match;
use function strtolower;

class TypeDecorator implements CatalogDecorator
{
    public function encode(object $value, EncodingContainer $container): void
    {
        assert($value instanceof Type);

        $container->type = $this->getType($value);
        $category = $this->getCategory($value);

        if ($category !== null) {
            $container->category = $category->value;
        }
    }

    protected function getType(Type $type): string
    {
        if ($type instanceof ScalarType) {
            return $type->getScalarType();
        }

        if (preg_match('/\\\([a-zA-Z]+)Type$/', $type::class, $matches)) {
            return strtolower($matches[1]);
        }

        return 'unknown';
    }

    protected function getCategory(Type $type): ?Category
    {
        return null;
    }
}

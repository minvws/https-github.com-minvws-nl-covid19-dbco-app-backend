<?php

declare(strict_types=1);

namespace App\Schema\Documentation;

use RuntimeException;

use function is_array;
use function trans;

class LaravelDocumentationProvider implements DocumentationProvider
{
    private string $resource;

    private ?string $locale;

    public function __construct(string $resource = 'schema', ?string $locale = null)
    {
        $this->resource = $resource;
        $this->locale = $locale;
    }

    public function getDocumentation(string $identifier, string $key): ?string
    {
        $fullKey = $this->resource . '.' . $identifier . '.' . $key;
        $result = trans($fullKey, [], $this->locale);

        if (is_array($result)) {
            throw new RuntimeException("Unexpected translation result for \"$fullKey\"");
        }

        return $result !== $fullKey ? $result : null;
    }
}

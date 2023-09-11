<?php

declare(strict_types=1);

namespace App\Models\Catalog;

use App\Schema\Purpose\Purpose;
use MinVWS\Codable\Encodable;
use MinVWS\Codable\EncodingContainer;

use function array_map;
use function count;
use function implode;

class Options implements Encodable
{
    public ?string $query = null;

    /** @var array<Category> */
    public ?array $categories = [];
    public ?Purpose $purpose = null;
    public Filter $filter;

    public function encode(EncodingContainer $container): void
    {
        $container->query = $this->query;
        $container->purpose = $this->purpose;
        $container->categories = $this->categories;
        $container->filter = $this->filter;
    }

    public function asQueryParams(): array
    {
        $params = [];

        if (isset($this->query)) {
            $params['query'] = $this->query;
        }

        if (isset($this->purpose)) {
            $params['purpose'] = $this->purpose->getIdentifier();
        }

        if (isset($this->categories) && count($this->categories) > 0) {
            $params['categories'] = implode(',', array_map(static fn (Category $c) => $c->value, $this->categories));
        }

        if (isset($this->filter)) {
            $params['filter'] = $this->filter;
        }

        return $params;
    }

    public static function exampleQueryParams(): array
    {
        return ['query' => '{?query}', 'purpose' => '{?purpose}', 'categories' => '{?categories}'];
    }
}

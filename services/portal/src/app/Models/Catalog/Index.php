<?php

declare(strict_types=1);

namespace App\Models\Catalog;

use MinVWS\Codable\Encodable;
use MinVWS\Codable\EncodingContainer;

use function route;

class Index implements Encodable
{
    private Options $options;
    private array $elements;

    public function __construct(Options $options, array $elements)
    {
        $this->options = $options;
        $this->elements = $elements;
    }

    public function encode(EncodingContainer $container): void
    {
        $container->options = $this->options;
        $container->elements = $this->elements;

        $container->_links->self = route('api-catalog', $this->options->asQueryParams());
        $container->_links->all = route('api-catalog');
        $container->_links->filter = route('api-catalog', Options::exampleQueryParams());
    }
}

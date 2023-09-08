<?php

declare(strict_types=1);

namespace App\Models\Place\Cases;

use MinVWS\Codable\Decodable;
use MinVWS\Codable\DecodingContainer;

class ListOptions implements Decodable
{
    public int $perPage = 20;
    public int $page = 1;
    public ?string $sort = null;
    public ?string $order = null;

    /**
     * @inheritDoc
     */
    public static function decode(DecodingContainer $container, ?object $object = null)
    {
        $options = $object ?? new self();

        if ($container->contains('perPage')) {
            $options->perPage = (int) $container->perPage->decodeStringIfPresent();
        }

        if ($container->contains('page')) {
            $options->page = (int) $container->page->decodeStringIfPresent();
        }

        if ($container->contains('sort')) {
            $options->sort = $container->sort->decodeStringIfPresent();
        }

        if ($container->contains('order')) {
            $options->order = $container->order->decodeStringIfPresent();
        }

        return $options;
    }
}

<?php

declare(strict_types=1);

namespace App\Models\CallToAction;

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
            $options->perPage = (int) $container->perPage->decode();
        }

        if ($container->contains('page')) {
            $options->page = (int) $container->page->decode();
        }

        if ($container->contains('sort')) {
            $options->sort = (string) $container->sort->decode();
        }

        if ($container->contains('order')) {
            $options->order = (string) $container->order->decode();
        }

        return $options;
    }
}

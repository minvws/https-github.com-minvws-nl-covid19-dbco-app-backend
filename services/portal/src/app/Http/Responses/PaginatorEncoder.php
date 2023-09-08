<?php

declare(strict_types=1);

namespace App\Http\Responses;

use Illuminate\Contracts\Pagination\Paginator;
use MinVWS\Codable\EncodingContainer;
use MinVWS\Codable\StaticEncodableDecorator;

class PaginatorEncoder implements StaticEncodableDecorator
{
    /**
     * @param object&Paginator $object
     */
    public static function encode(object $object, EncodingContainer $container): void
    {
        $container->from = $object->firstItem();
        $container->to = $object->lastItem();
        $container->currentPage = $object->currentPage();
        $container->data = $object->items();
    }
}

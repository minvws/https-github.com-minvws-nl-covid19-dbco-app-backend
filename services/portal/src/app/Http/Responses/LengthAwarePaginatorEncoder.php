<?php

declare(strict_types=1);

namespace App\Http\Responses;

use Illuminate\Pagination\LengthAwarePaginator;
use MinVWS\Codable\EncodingContainer;
use MinVWS\Codable\StaticEncodableDecorator;

class LengthAwarePaginatorEncoder implements StaticEncodableDecorator
{
    /**
     * @param object&LengthAwarePaginator $object
     */
    public static function encode(object $object, EncodingContainer $container): void
    {
        $container->from = $object->firstItem();
        $container->to = $object->lastItem();
        $container->total = $object->total();
        $container->currentPage = $object->currentPage();
        $container->lastPage = $object->lastPage();
        $container->data = $object->all();
    }
}

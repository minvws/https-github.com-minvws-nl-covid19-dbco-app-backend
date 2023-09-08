<?php

declare(strict_types=1);

namespace App\Http\Responses\Sync;

use App\Models\Eloquent\EloquentCase;
use MinVWS\Codable\EncodableDecorator;
use MinVWS\Codable\EncodingContainer;

class SyncEncoder implements EncodableDecorator
{
    public function encode(object $value, EncodingContainer $container): void
    {
        if (!$value instanceof EloquentCase) {
            return;
        }

        $container->uuid = $value->uuid;
        $container->hpZoneNumber = $value->hpzoneNumber;

        $this->encodeFragments($value, $container->nestedContainer('fragments'));
    }

    private function encodeFragments(EloquentCase $value, EncodingContainer $nestedContainer): void
    {
        $nestedContainer->encodeArray($value->fragments);
    }
}

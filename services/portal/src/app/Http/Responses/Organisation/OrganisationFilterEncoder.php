<?php

declare(strict_types=1);

namespace App\Http\Responses\Organisation;

use App\Models\Eloquent\EloquentOrganisation;
use MinVWS\Codable\EncodableDecorator;
use MinVWS\Codable\EncodingContainer;

class OrganisationFilterEncoder implements EncodableDecorator
{
    public function encode(object $value, EncodingContainer $container): void
    {
        if (!$value instanceof EloquentOrganisation) {
            return;
        }

        $container->uuid = $value->uuid;
        $container->name = $value->name;
    }
}

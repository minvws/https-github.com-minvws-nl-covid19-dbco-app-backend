<?php

declare(strict_types=1);

namespace App\Http\Responses\Organisation;

use App\Models\Eloquent\EloquentOrganisation;
use MinVWS\Codable\EncodableDecorator;
use MinVWS\Codable\EncodingContainer;

class CurrentOrganisationEncoder implements EncodableDecorator
{
    public function encode(object $value, EncodingContainer $container): void
    {
        if (!$value instanceof EloquentOrganisation) {
            return;
        }

        $container->uuid = $value->uuid;
        $container->abbreviation = $value->abbreviation;
        $container->name = $value->name;
        $container->hasOutsourceToggle = $value->has_outsource_toggle;
        $container->isAvailableForOutsourcing = $value->has_outsource_toggle && $value->is_available_for_outsourcing;
        $container->bcoPhase = $value->bco_phase;
        $container->type = $value->type;
    }
}

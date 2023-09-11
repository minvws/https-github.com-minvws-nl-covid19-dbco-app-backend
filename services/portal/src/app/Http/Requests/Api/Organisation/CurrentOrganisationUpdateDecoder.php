<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Organisation;

use App\Models\Eloquent\EloquentOrganisation as Organisation;
use MinVWS\Codable\CodableException;
use MinVWS\Codable\DecodableDecorator;
use MinVWS\Codable\DecodingContainer;
use MinVWS\DBCO\Enum\Models\BCOPhase;

/**
 * @property Organisation $organisation
 */
class CurrentOrganisationUpdateDecoder implements DecodableDecorator
{
    /**
     * @inheritDoc
     *
     * @param Organisation $object
     */
    public function decode(string $class, DecodingContainer $container, ?object $object = null): object
    {
        if ($object === null) {
            throw new CodableException('Decoder can only be used for updates!');
        }

        if (
            $container->contains(CurrentOrganisationUpdateRequest::FIELD_IS_AVAILABLE_FOR_OUTSOURCING) &&
            $container->{CurrentOrganisationUpdateRequest::FIELD_IS_AVAILABLE_FOR_OUTSOURCING} !== null
        ) {
            $object->is_available_for_outsourcing = $container
                ->{CurrentOrganisationUpdateRequest::FIELD_IS_AVAILABLE_FOR_OUTSOURCING}
                ->decodeBool();
        }

        if ($container->contains(CurrentOrganisationUpdateRequest::FIELD_BCO_PHASE)) {
            $object->bco_phase = BCOPhase::from(
                $container->{CurrentOrganisationUpdateRequest::FIELD_BCO_PHASE}->decodeString(),
            );
        }

        return $object;
    }
}

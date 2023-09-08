<?php

declare(strict_types=1);

namespace App\Http\Responses\Context;

use App\Helpers\PostalCodeHelper;
use App\Models\Eloquent\Context;
use App\Models\Eloquent\Moment;
use App\Models\Eloquent\Place;
use Illuminate\Database\Eloquent\Collection;
use MinVWS\Codable\EncodableDecorator;
use MinVWS\Codable\EncodingContainer;

final class ContextEncoder implements EncodableDecorator
{
    public function encode(object $value, EncodingContainer $container): void
    {
        if (!$value instanceof Context) {
            return;
        }

        $container->uuid = $value->uuid;
        $container->label = $value->label;
        $container->explanation = $value->explanation;
        $container->detailedExplanation = $value->detailed_explanation;
        $container->remarks = $value->remarks;
        $container->placeUuid = $value->place_uuid;
        $this->encodeMoments($value->moments, $container->nestedContainer('moments'));
        $container->relationship = $value->relationship;
        $container->otherRelationship = $value->other_relationship;
        $container->isSource = $value->is_source;
        $this->encodePlace($value->place, $container->nestedContainer('place'));
    }

    /**
     * @param Collection<int, Moment> $moments
     */
    private function encodeMoments(Collection $moments, EncodingContainer $nestedContainer): void
    {
        $nestedContainer->encodeArray($moments->map(static function (Moment $moment) {
            return $moment->day->format('Y-m-d');
        }));
    }

    private function encodePlace(?Place $place, EncodingContainer $container): void
    {
        if ($place === null) {
            $container->encodeNull();
        } else {
            $container->uuid = $place->uuid;
            $container->label = $place->label;
            $container->category = $place->category;
            $this->encodePlaceAddress($place, $container->nestedContainer('address'));
            $container->editable = $place->contexts()->count() === 1;
            $container->isVerified = $place->is_verified;
        }
    }

    private function encodePlaceAddress(Place $place, EncodingContainer $container): void
    {
        $container->postalCode = $place->postalcode ? PostalCodeHelper::normalize($place->postalcode) : null;
        $container->street = $place->street;
        $container->houseNumber = $place->housenumber;
        $container->houseNumberSuffix = $place->housenumber_suffix;
        $container->town = $place->town;
        $container->country = $place->country;
    }
}

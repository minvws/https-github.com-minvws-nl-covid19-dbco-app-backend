<?php

declare(strict_types=1);

namespace App\Http\Responses\Place;

use App\Helpers\PostalCodeHelper;
use App\Models\Eloquent\EloquentSituation;
use App\Models\Eloquent\Place;
use App\Models\Place\PlaceSource;
use App\Services\PlaceService;
use Carbon\CarbonImmutable;
use MinVWS\Codable\EncodableDecorator;
use MinVWS\Codable\EncodingContainer;
use MinVWS\DBCO\Enum\Models\ContextCategory;
use stdClass;

use function assert;
use function sprintf;
use function trim;

class PlaceEncoder implements EncodableDecorator
{
    public function __construct(
        private readonly PlaceService $placeService,
    ) {
    }

    public function encode(object $value, EncodingContainer $container): void
    {
        assert($value instanceof Place || $value instanceof stdClass);

        $container->uuid = $value->uuid;
        $container->label = $value->label;
        $container->category = $value->category;
        $contextCategory = !$value->category instanceof ContextCategory ? ContextCategory::tryFromOptional($value->category) : null;
        $container->categoryLabel = $contextCategory?->label;
        $container->organisationUuid = $value->organisation_uuid;
        $container->organisationUuidByPostalCode = $this->encodePostalCodeOrganisationUuid($value->postalcode);

        $this->encodeAddress($value, $container->nestedContainer('address'));
        $container->addressLabel = $this->addressLabel($value);

        $this->encodeCounters($value, $container);

        $container->isVerified = (bool) $value->is_verified;
        $container->editable = true;
        $container->source = $value->location_id ? PlaceSource::external()->value : PlaceSource::manual()->value;

        $this->encodeGgd($value, $container->nestedContainer('ggd'));
        $this->encodeSituationNumbers($value, $container->nestedContainer('situationNumbers'));

        $container->createdAt = $value->created_at;
        $container->updatedAt = $value->updated_at;
    }

    private function encodeAddress(Place|stdClass $value, EncodingContainer $container): void
    {
        $container->street = $value->street;
        $container->houseNumber = $value->housenumber;
        $container->houseNumberSuffix = $value->housenumber_suffix;
        $container->postalCode = $this->normalizePostalCode($value->postalcode);
        $container->town = $value->town;
        $container->country = $value->country;
    }

    private function encodePostalCodeOrganisationUuid(?string $postalCode): ?string
    {
        return $this->placeService->determineOrganisationUuid(null, $this->normalizePostalCode($postalCode));
    }

    private function addressLabel(Place|stdClass $value): string
    {
        return trim(sprintf(
            '%s %s, %s %s',
            $value->street,
            $this->completeHouseNumber($value),
            $this->normalizePostalCode($value->postalcode),
            $value->town,
        ));
    }

    private function completeHouseNumber(Place|stdClass $value): string
    {
        if ($value->housenumber === null) {
            return $value->housenumber_suffix ?? '';
        }

        return trim($value->housenumber . ' ' . ($value->housenumber_suffix ?? ''));
    }

    private function normalizePostalCode(?string $postalCode): ?string
    {
        if ($postalCode !== null) {
            return PostalCodeHelper::normalize($postalCode);
        }

        return null;
    }

    private function encodeGgd(Place|stdClass $value, EncodingContainer $nestedContainer): void
    {
        $nestedContainer->code = $value->ggd_code;
        $nestedContainer->municipality = $value->ggd_municipality;
    }

    private function encodeCounters(Place|stdClass $value, EncodingContainer $container): void
    {
        // These values come from the `place_counters` table & are joined together with the use of the `PlaceCounters` relation
        $container->indexCount = $value->index_count;
        $container->indexCountSinceReset = $value->index_count_since_reset;
        $container->indexCountResetAt = $value->index_count_reset_at;
        $container->lastIndexPresence = $value->last_index_presence
            ? CarbonImmutable::parse($value->last_index_presence)->format("Y-m-d")
            : null;
        $container->countersUpdatedAt = $value->counters_updated_at;
    }

    // phpcs:ignore Generic.Commenting.Todo.TaskFound -- baseline
    // TODO: Should be reverted with ticket BOOST-46
    // https://ggdcontact.atlassian.net/browse/BOOST-46
    private function encodeSituationNumbers(Place|stdClass $value, EncodingContainer $nestedContainer): void
    {
        /** @var ?Place $place */
        $place = Place::with('situations')->find($value->uuid);
        $situations = $place?->situations;

        if ($situations === null) {
            $nestedContainer->encodeNull();
            return;
        }

        $nestedContainer->encodeArray($situations->map(static function (EloquentSituation $situation) {
            return [
                'uuid' => $situation->uuid,
                'name' => $situation->name,
                'value' => $situation->hpzone_number,
            ];
        }));
    }
}

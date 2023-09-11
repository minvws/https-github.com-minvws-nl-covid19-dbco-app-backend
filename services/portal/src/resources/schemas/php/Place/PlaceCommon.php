<?php

namespace App\Models\Versions\Place;

/**
 * *** WARNING: This code is auto-generated. Any changes will be reverted by generating the schema! ***
 *
 * @property string $uuid
 * @property ?string $pseudoId
 * @property \DateTimeInterface $createdAt
 * @property \DateTimeInterface $updatedAt
 * @property ?array<\App\Models\Versions\Section\SectionV1> $sections
 * @property \App\Models\Versions\Organisation\OrganisationV1 $organisation
 * @property string $label
 * @property ?string $locationId
 * @property ?string $category
 * @property ?string $street
 * @property ?string $housenumber
 * @property ?string $housenumberSuffix
 * @property ?string $postalcode
 * @property ?string $town
 * @property string $country
 * @property ?string $ggdCode
 * @property ?string $ggdMunicipality
 * @property bool $isVerified
 */
interface PlaceCommon
{
}


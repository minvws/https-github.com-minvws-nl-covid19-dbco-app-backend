<?php

declare(strict_types=1);

namespace App\Models\Eloquent;

use App\Helpers\PostalCodeHelper;
use App\Models\Eloquent\Traits\CamelCaseAttributes;
use App\Models\Fields\IdFieldsHelper;
use App\Models\Fields\PurposeSpecificationBinder;
use App\Models\Versions\Place\PlaceCommon;
use App\Observers\PlaceObserver;
use App\Schema\Eloquent\Traits\HasSchema;
use App\Schema\Schema;
use App\Schema\SchemaObject;
use App\Schema\SchemaProvider;
use App\Schema\Types\BoolType;
use App\Schema\Types\DateTimeType;
use App\Schema\Types\StringType;
use App\Scopes\OrganisationAuthScope;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;
use MinVWS\DBCO\Enum\Models\ContextCategory;

use function app;
use function sprintf;
use function trim;

/**
 * @property string $uuid
 * @property ?string $organisation_uuid
 * @property string $label
 * @property ?string $location_id
 * @property ?ContextCategory $category
 * @property ?string $street
 * @property ?string $housenumber
 * @property ?string $housenumber_suffix
 * @property ?string $postalcode
 * @property ?string $town
 * @property string $country
 * @property CarbonInterface $created_at
 * @property CarbonInterface $updated_at
 * @property ?string $ggd_code
 * @property ?string $ggd_municipality
 * @property bool $is_verified
 * @property int $schema_version
 * @property ?CarbonInterface $index_count_reset_at
 *
 * @property Collection<int, Context> $contexts
 * @property ?EloquentOrganisation $organisation
 * @property ?PlaceCounters $placeCounters
 * @property Collection<int, Section> $sections
 * @property Collection<int, EloquentSituation> $situations
 */
class Place extends EloquentBaseModel implements SchemaObject, SchemaProvider, PlaceCommon
{
    use CamelCaseAttributes;
    use HasFactory;
    use HasSchema;

    protected $table = 'place';
    protected $casts = [
        'category' => ContextCategory::class,
        'is_verified' => 'boolean',
        'index_count_reset_at' => 'datetime',
    ];

    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class);

        $schema->setUseVersionedClasses(true);
        $schema->setName('Place');
        $schema->setVersionedNamespace('App\\Models\\Versions\\Place');
        $schema->setCurrentVersion(1);

        IdFieldsHelper::addIdFieldsToSchema($schema);
        $schema->add(DateTimeType::createField('createdAt'))->setAllowsNull(false);
        $schema->add(DateTimeType::createField('updatedAt'))->setAllowsNull(false);

        $schema->add(Section::getSchema()->getVersion(1)->createArrayField('sections'));

        $schema->add(EloquentOrganisation::getSchema()->getVersion(1)->createField('organisation'))->setAllowsNull(false);

        $schema->add(StringType::createField('label'))->setAllowsNull(false);
        $schema->add(StringType::createField('locationId'));
        $schema->add(StringType::createField('category'));
        $schema->add(StringType::createField('street'));
        $schema->add(StringType::createField('housenumber'));
        $schema->add(StringType::createField('housenumberSuffix'));
        $schema->add(StringType::createField('postalcode'));
        $schema->add(StringType::createField('town'));
        $schema->add(StringType::createField('country'))->setAllowsNull(false);
        $schema->add(StringType::createField('ggdCode'));
        $schema->add(StringType::createField('ggdMunicipality'));
        $schema->add(BoolType::createField('isVerified'))->setAllowsNull(false);

        return app(PurposeSpecificationBinder::class)->bind($schema);
    }

    protected static function boot(): void
    {
        parent::boot();

        self::observe(PlaceObserver::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class, 'place_uuid', 'uuid');
    }

    public function situations(): BelongsToMany
    {
        return $this->belongsToMany(EloquentSituation::class, 'situation_place', 'place_uuid', 'situation_uuid');
    }

    public function contexts(): HasMany
    {
        return $this->hasMany(Context::class, 'place_uuid', 'uuid');
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(EloquentOrganisation::class)
            ->withoutGlobalScope(OrganisationAuthScope::class);
    }

    public function placeCounters(): HasOne
    {
        return $this->hasOne(PlaceCounters::class, 'place_uuid', 'uuid');
    }

    public function completeHouseNumber(): string
    {
        return $this->housenumber . ($this->housenumber_suffix ? ' ' . $this->housenumber_suffix : '');
    }

    public function addressLabel(): string
    {
        return trim(sprintf(
            '%s %s, %s %s',
            $this->street,
            $this->completeHouseNumber(),
            $this->normalizePostalCode($this->postalcode),
            $this->town,
        ));
    }

    public function toResult(): array
    {
        return [
            'uuid' => $this->uuid,
            'label' => $this->label,
            'indexCount' => 0,
            'category' => $this->category,
            'addressLabel' => $this->addressLabel(),
            'address' => [
                'street' => $this->street,
                'houseNumber' => $this->housenumber,
                'houseNumberSuffix' => $this->housenumber_suffix,
                'postalCode' => $this->normalizePostalCode($this->postalcode),
                'town' => $this->town,
            ],
            'ggd' => [
                'code' => $this->ggd_code,
                'municipality' => $this->ggd_municipality,
            ],
            'isVerified' => $this->is_verified,
        ];
    }

    private function normalizePostalCode(?string $postalCode): ?string
    {
        if ($postalCode !== null) {
            return PostalCodeHelper::normalize($postalCode);
        }

        return null;
    }
}

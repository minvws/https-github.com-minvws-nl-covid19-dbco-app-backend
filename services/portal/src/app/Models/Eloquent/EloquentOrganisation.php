<?php

declare(strict_types=1);

namespace App\Models\Eloquent;

use App\Models\Eloquent\Traits\CamelCaseAttributes;
use App\Models\Fields\PurposeSpecificationBinder;
use App\Models\Organisation;
use App\Models\OrganisationType;
use App\Models\Versions\Organisation\OrganisationCommon;
use App\Schema\Eloquent\Traits\HasSchema;
use App\Schema\Schema;
use App\Schema\SchemaObject;
use App\Schema\SchemaProvider;
use App\Schema\Types\BoolType;
use App\Schema\Types\StringType;
use App\Schema\Types\UUIDType;
use App\Scopes\OrganisationAuthScope;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use MinVWS\DBCO\Enum\Models\BCOPhase;

use function app;
use function in_array;

/**
 * @property string $uuid
 * @property ?OrganisationType $type
 * @property string $external_id
 * @property ?string $hp_zone_code
 * @property string $name
 * @property ?string $phone_number
 * @property CarbonImmutable $created_at
 * @property CarbonImmutable $updated_at
 * @property ?string $abbreviation
 * @property bool $has_outsource_toggle
 * @property bool $is_available_for_outsourcing
 * @property BCOPhase $bco_phase
 * @property ?string $parent_organisation
 * @property bool $is_allowed_to_report_test_results
 *
 * @property Collection<int, CaseLabel> $caseLabels
 * @property Collection<int, CaseList> $caseLists
 * @property Collection<int, static> $outsourceOrganisations
 * @property ?EloquentOrganisation $parentOrganisation
 * @property Collection<int, static> $regionalOrganisations
 * @property Collection<int, EloquentUser> $users
 */
class EloquentOrganisation extends EloquentBaseModel implements SchemaObject, SchemaProvider, OrganisationCommon
{
    use HasSchema;
    use CamelCaseAttributes;
    use HasFactory;

    protected $table = 'organisation';

    protected $casts = [
        'bco_phase' => BCOPhase::class,
        'type' => OrganisationType::class,
        'has_outsource_toggle' => 'bool',
        'is_available_for_outsourcing' => 'bool',
        'is_allowed_to_report_test_results' => 'bool',
    ];

    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class);

        $schema->setUseVersionedClasses(true);
        $schema->setName('Organisation');
        $schema->setVersionedNamespace('App\\Models\\Versions\\Organisation');
        $schema->setCurrentVersion(1);

        $schema->add(UUIDType::createField('uuid'))->setAllowsNull(false);
        $schema->add(StringType::createField('name'))->setAllowsNull(false);
        $schema->add(StringType::createField('abbreviation'));
        $schema->add(StringType::createField('hpZoneCode'));
        $schema->add(BCOPhase::getVersion(1)->createField('bcoPhase'));
        $schema->add(BoolType::createField('isAllowedToReportTestResults')->setAllowsNull(false)->setDefaultValue(false))->setExcluded();
        $schema->add(StringType::createField('externalId'))->setAllowsNull(false)->setExcluded();

        return app(PurposeSpecificationBinder::class)->bind($schema);
    }

    public function caseLabels(): BelongsToMany
    {
        return $this->belongsToMany(CaseLabel::class, 'case_label_organisation', 'organisation_uuid')
            ->orderByPivot('sortorder', 'desc');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(EloquentUser::class, 'user_organisation', 'organisation_uuid', 'user_uuid')->withTimestamps();
    }

    public function caseLists(): HasMany
    {
        return $this->hasMany(CaseList::class, 'organisation_uuid');
    }

    public function parentOrganisation(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_organisation');
    }

    public function outsourceOrganisations(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            'organisation_outsource',
            'organisation_uuid',
            'outsources_to_organisation_uuid',
        )->withoutGlobalScope(OrganisationAuthScope::class);
    }

    public function isOutsourceOrganisation(): bool
    {
        return in_array($this->type, [
            OrganisationType::outsourceDepartment(),
            OrganisationType::outsourceOrganisation(),
        ], true);
    }

    public function regionalOrganisations(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'organisation_outsource', 'outsources_to_organisation_uuid', 'organisation_uuid');
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(app()->make(OrganisationAuthScope::class));
    }

    /**
     * Convert to Eloquent-less organisation object.
     *
     * As we are going to use the Eloquent objects more and more directly this is something temporary,
     * hence marked as deprecated!
     *
     * @deprecated Placeholder: No description was set at the time.
     */
    public function toOrganisation(): Organisation
    {
        $organisation = new Organisation();
        $organisation->uuid = $this->uuid;
        $organisation->abbreviation = $this->abbreviation;
        $organisation->externalId = $this->external_id;
        $organisation->hpZoneCode = $this->hp_zone_code;
        $organisation->name = $this->name;
        $organisation->phoneNumber = $this->phone_number;
        $organisation->bcoPhase = $this->bco_phase;
        return $organisation;
    }

    public function scopeByExternalId(Builder $query, string $externalId): Builder
    {
        return $query->where('external_id', $externalId);
    }

    public function isOrganisationThatOutsourcesTo(EloquentOrganisation $outsourceOrganisation): bool
    {
        return $this->outsourceOrganisations->contains($outsourceOrganisation->uuid);
    }

    public function isOrganisationForUser(EloquentUser $user): bool
    {
        return $this->users->contains($user->uuid);
    }
}

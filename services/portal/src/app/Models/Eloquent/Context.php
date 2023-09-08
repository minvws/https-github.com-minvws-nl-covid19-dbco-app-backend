<?php

declare(strict_types=1);

namespace App\Models\Eloquent;

use App\Models\Context\Circumstances;
use App\Models\Context\Contact;
use App\Models\Context\General;
use App\Models\Eloquent\Traits\CamelCaseAttributes;
use App\Models\Fields\IdFieldsHelper;
use App\Models\Fields\PurposeSpecificationBinder;
use App\Models\Versions\Context\ContextCommon;
use App\Observers\ContextObserver;
use App\Schema\Eloquent\Traits\HasSchema;
use App\Schema\Fields\PseudonomizedField;
use App\Schema\Schema;
use App\Schema\SchemaObject;
use App\Schema\SchemaProvider;
use App\Schema\Types\UUIDType;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use MinVWS\Codable\EncodingContext;
use MinVWS\DBCO\Encryption\Security\StorageTerm;
use MinVWS\DBCO\Enum\Models\ContextRelationship;

use function app;

/**
 * @property string $uuid
 * @property ?string $label
 * @property string $covidcase_uuid
 * @property ?string $place_uuid
 * @property ?ContextRelationship $relationship
 * @property ?string $other_relationship
 * @property ?string $explanation
 * @property ?string $detailed_explanation
 * @property ?string $remarks
 * @property bool $is_source
 * @property CarbonImmutable $created_at
 * @property CarbonImmutable $updated_at
 * @property Circumstances $circumstances
 * @property Contact $contact
 * @property int $schema_version
 * @property General $general
 * @property ?CarbonInterface $place_added_at
 *
 * @property EloquentCase $case
 * @property Collection<int, Moment> $moments
 * @property ?Place $place
 * @property Collection<int, Section> $sections
 */
class Context extends EloquentBaseModel implements SchemaObject, SchemaProvider, ContextCommon
{
    use CamelCaseAttributes;
    use HasFactory;
    use HasSchema;

    /** @var array<string, string> $casts */
    protected $casts = [
        'is_source' => 'boolean',
        'relationship' => ContextRelationship::class,
        'general' => General::class . ':' . StorageTerm::LONG . ',created_at',
        'circumstances' => Circumstances::class . ':' . StorageTerm::LONG . ',created_at',
        'contact' => Contact::class . ':' . StorageTerm::LONG . ',created_at',
        'place_added_at' => 'datetime',
    ];

    protected $table = 'context';

    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class);

        $schema->setUseVersionedClasses(true);
        $schema->setVersionedNamespace('App\\Models\\Versions\\Context');
        $schema->setCurrentVersion(1);

        $schema->add(UUIDType::createField('placeUuid'))
            ->setIncludedInEncode(false, EncodingContext::MODE_EXPORT);

        // Pseudo fields
        IdFieldsHelper::addIdFieldsToSchema($schema);
        $schema->add(PseudonomizedField::createFromField('pseudoPlaceId', 'placeUuid'));

        $schema->add(Section::getSchema()->getVersion(1)->createArrayField('sections'));

        $schema->add(General::getSchema()->getVersion(1)->createField('general')->setAllowsNull(false));
        $schema->add(Circumstances::getSchema()->getVersion(1)->createField('circumstances')->setAllowsNull(false));
        $schema->add(Contact::getSchema()->getVersion(1)->createField('contact')->setAllowsNull(false));

        return app(PurposeSpecificationBinder::class)->bind($schema);
    }

    protected static function boot(): void
    {
        parent::boot();

        self::observe(ContextObserver::class);
    }

    public function case(): BelongsTo
    {
        return $this->belongsTo(EloquentCase::class, 'covidcase_uuid');
    }

    public function sections(): BelongsToMany
    {
        return $this->belongsToMany(Section::class, 'context_section', 'context_uuid', 'section_uuid');
    }

    public function place(): HasOne
    {
        return $this->hasOne(Place::class, 'uuid', 'place_uuid');
    }

    public function moments(): HasMany
    {
        return $this
            ->hasMany(Moment::class, 'context_uuid', 'uuid')
            ->orderBy('moment.day')
            ->orderBy('moment.start_time');
    }
}

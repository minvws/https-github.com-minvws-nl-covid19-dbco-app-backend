<?php

declare(strict_types=1);

namespace App\Models\Eloquent;

use App\Models\CovidCase\Intake\Abroad;
use App\Models\CovidCase\Intake\Contact;
use App\Models\CovidCase\Intake\Housemates;
use App\Models\CovidCase\Intake\Job;
use App\Models\CovidCase\Intake\Pregnancy;
use App\Models\CovidCase\Intake\RecentBirth;
use App\Models\CovidCase\Intake\SourceEnvironments;
use App\Models\CovidCase\Intake\Symptoms;
use App\Models\CovidCase\Intake\Test;
use App\Models\CovidCase\Intake\UnderlyingSuffering;
use App\Models\CovidCase\Intake\Vaccination;
use App\Models\Eloquent\Traits\CamelCaseAttributes;
use App\Models\Fields\PurposeSpecificationBinder;
use App\Models\Versions\Intake\IntakeCommon;
use App\Schema\Eloquent\Traits\HasSchema;
use App\Schema\Schema;
use App\Schema\SchemaObject;
use App\Schema\SchemaProvider;
use App\Schema\Types\DateTimeType;
use App\Schema\Types\IntType;
use App\Schema\Types\StringType;
use App\Schema\Types\UUIDType;
use App\Scopes\IntakeOrganisationAuthScope;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use MinVWS\Codable\Decoder;
use MinVWS\DBCO\Encryption\Security\StorageTerm;
use MinVWS\DBCO\Enum\Models\Gender;
use MinVWS\DBCO\Enum\Models\IntakeType;

use function app;
use function collect;

/**
 * @property string $uuid
 * @property string $organisation_uuid
 * @property IntakeType $type
 * @property string $source
 * @property string $identifier_type
 * @property string $identifier
 * @property string $pseudo_bsn_guid
 * @property ?int $cat1_count
 * @property ?int $estimated_cat2_count
 * @property ?string $firstname
 * @property ?string $prefix
 * @property ?string $lastname
 * @property CarbonImmutable $date_of_birth
 * @property ?CarbonImmutable $date_of_symptom_onset
 * @property CarbonImmutable $date_of_test
 * @property CarbonImmutable $received_at
 * @property CarbonImmutable $created_at
 * @property ?CarbonImmutable $deleted_at
 * @property string $pc3
 * @property Gender $gender
 *
 * @property Collection<int, CaseLabel> $caseLabels
 * @property Collection<int, IntakeContact> $contacts
 * @property Collection<int, IntakeFragment> $fragments
 * @property EloquentOrganisation $organisation
 *
 * @property ?Abroad $abroad
 * @property ?Contact $contact
 * @property string $fullname
 * @property ?Housemates $housemates
 * @property ?Job $job
 * @property ?Pregnancy $pregnancy
 * @property ?RecentBirth $recentBirth
 * @property ?SourceEnvironments $sourceEnvironments
 * @property ?Symptoms $symptoms
 * @property ?Test $test
 * @property ?UnderlyingSuffering $underlyingSuffering
 * @property ?Vaccination $vaccination
 */
class Intake extends EloquentBaseModel implements SchemaObject, SchemaProvider, IntakeCommon
{
    use HasFactory;
    use CamelCaseAttributes;
    use HasSchema;
    use SoftDeletes;

    protected $table = 'intake';

    public $timestamps = false;

    protected $casts = [
        'type' => IntakeType::class . ':' . StorageTerm::LONG . ',created_at',
        'gender' => Gender::class . ':' . StorageTerm::LONG . ',created_at',
        'received_at' => 'datetime',
        'created_at' => 'datetime',
        'date_of_birth' => 'datetime',
        'date_of_symptom_onset' => 'datetime',
        'date_of_test' => 'datetime',
    ];

    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class);

        $schema->setUseVersionedClasses(true);
        $schema->setVersionedNamespace('App\\Models\\Versions\\Intake');
        $schema->setCurrentVersion(1);

        // Common fields
        $schema->add(UUIDType::createField('uuid'))->setAllowsNull(false);
        $schema->add(IntakeType::getVersion(1)->createField('type'))->setAllowsNull(false);
        $schema->add(StringType::createField('source'))->setAllowsNull(false);
        $schema->add(StringType::createField('identifierType'))->setAllowsNull(false);
        $schema->add(StringType::createField('identifier'))->setAllowsNull(false);
        $schema->add(StringType::createField('pseudoBsnGuid'))->setAllowsNull(false);
        $schema->add(IntType::createField('cat1Count'))->setAllowsNull(true);
        $schema->add(IntType::createField('estimatedCat2Count'))->setAllowsNull(true);
        $schema->add(StringType::createField('firstname'))->setAllowsNull(true);
        $schema->add(StringType::createField('prefix'))->setAllowsNull(true);
        $schema->add(StringType::createField('lastname'))->setAllowsNull(true);
        $schema->add(DateTimeType::createField('dateOfBirth'))->setAllowsNull(false);
        $schema->add(DateTimeType::createField('dateOfSymptomOnset'))->setAllowsNull(true);
        $schema->add(DateTimeType::createField('dateOfTest'))->setAllowsNull(false);
        $schema->add(DateTimeType::createField('receivedAt'))->setAllowsNull(false);
        $schema->add(DateTimeType::createField('createdAt'))->setAllowsNull(false);
        $schema->add(IntakeFragment::getSchema()->getVersion(1)->createArrayField('fragments'));
        $schema->add(IntakeContact::getSchema()->getVersion(1)->createArrayField('contacts'));
        $schema->add(StringType::createField('pc3'))->setAllowsNull(false)
            ->getValidationRules()
            ->addFatal('max:3');
        $schema->add(Gender::getVersion(1)->createField('gender'))->setAllowsNull(false);

        return app(PurposeSpecificationBinder::class)->bind($schema);
    }

    public function fragments(): HasMany
    {
        return $this->hasMany(IntakeFragment::class, 'intake_uuid')->orderBy('name');
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(IntakeContact::class, 'intake_uuid')->orderBy('uuid');
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(EloquentOrganisation::class, 'organisation_uuid');
    }

    public function caseLabels(): BelongsToMany
    {
        return $this->belongsToMany(CaseLabel::class, 'intake_label', 'intake_uuid', 'label_uuid');
    }

    protected static function booted(): void
    {
        static::addGlobalScope(app()->make(IntakeOrganisationAuthScope::class));
    }

    public function getFullnameAttribute(): string
    {
        return collect([
            $this->firstname,
            $this->prefix,
            $this->lastname,
        ])->whereNotNull()->implode(' ');
    }

    public function getContactAttribute(): ?Contact
    {
        return $this->fragments->where('name', 'contact')->map(static function (IntakeFragment $intakeFragment) {
            $decoder = new Decoder();
            $container = $decoder->decode($intakeFragment->data);
            return Contact::getSchema()->getCurrentVersion()->decode($container);
        })->first();
    }

    public function getHousematesAttribute(): ?Housemates
    {
        return $this->fragments->where('name', 'housemates')->map(static function (IntakeFragment $intakeFragment) {
            $decoder = new Decoder();
            $container = $decoder->decode($intakeFragment->data);
            return Housemates::getSchema()->getCurrentVersion()->decode($container);
        })->first();
    }

    public function getJobAttribute(): ?Job
    {
        return $this->fragments->where('name', 'job')->map(static function (IntakeFragment $intakeFragment) {
            $decoder = new Decoder();
            $container = $decoder->decode($intakeFragment->data);
            return Job::getSchema()->getCurrentVersion()->decode($container);
        })->first();
    }

    public function getPregnancyAttribute(): ?Pregnancy
    {
        return $this->fragments->where('name', 'pregnancy')->map(static function (IntakeFragment $intakeFragment) {
            $decoder = new Decoder();
            $container = $decoder->decode($intakeFragment->data);
            return Pregnancy::getSchema()->getCurrentVersion()->decode($container);
        })->first();
    }

    public function getRecentBirthAttribute(): ?RecentBirth
    {
        return $this->fragments->where('name', 'recentBirth')->map(static function (IntakeFragment $intakeFragment) {
            $decoder = new Decoder();
            $container = $decoder->decode($intakeFragment->data);
            return RecentBirth::getSchema()->getCurrentVersion()->decode($container);
        })->first();
    }

    public function getSymptomsAttribute(): ?Symptoms
    {
        return $this->fragments->where('name', 'symptoms')->map(static function (IntakeFragment $intakeFragment) {
            $decoder = new Decoder();
            $container = $decoder->decode($intakeFragment->data);
            return Symptoms::getSchema()->getCurrentVersion()->decode($container);
        })->first();
    }

    public function getTestAttribute(): ?Test
    {
        return $this->fragments->where('name', 'test')->map(static function (IntakeFragment $intakeFragment) {
            $decoder = new Decoder();
            $container = $decoder->decode($intakeFragment->data);
            return Test::getSchema()->getCurrentVersion()->decode($container);
        })->first();
    }

    public function getUnderlyingSufferingAttribute(): ?UnderlyingSuffering
    {
        return $this->fragments->where('name', 'underlyingSuffering')->map(static function (IntakeFragment $intakeFragment) {
            $decoder = new Decoder();
            $container = $decoder->decode($intakeFragment->data);
            return UnderlyingSuffering::getSchema()->getCurrentVersion()->decode($container);
        })->first();
    }

    public function getAbroadAttribute(): ?Abroad
    {
        return $this->fragments->where('name', 'abroad')->map(static function (IntakeFragment $intakeFragment) {
            $decoder = new Decoder();
            $container = $decoder->decode($intakeFragment->data);
            return Abroad::getSchema()->getCurrentVersion()->decode($container);
        })->first();
    }

    public function getVaccinationAttribute(): ?Vaccination
    {
        return $this->fragments->where('name', 'vaccination')->map(static function (IntakeFragment $intakeFragment) {
            $decoder = new Decoder();
            $container = $decoder->decode($intakeFragment->data);
            return Vaccination::getSchema()->getCurrentVersion()->decode($container);
        })->first();
    }

    public function getSourceEnvironmentsAttribute(): ?SourceEnvironments
    {
        return $this->fragments->where('name', 'sourceEnvironments')->map(static function (IntakeFragment $intakeFragment) {
            $decoder = new Decoder();
            $container = $decoder->decode($intakeFragment->data);
            return SourceEnvironments::getSchema()->getCurrentVersion()->decode($container);
        })->first();
    }
}

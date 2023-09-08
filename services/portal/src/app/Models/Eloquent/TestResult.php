<?php

declare(strict_types=1);

namespace App\Models\Eloquent;

use App\Models\Eloquent\Traits\CamelCaseAttributes;
use App\Models\Fields\PurposeSpecificationBinder;
use App\Models\TestResult\General;
use App\Models\Versions\TestResult\TestResultCommon;
use App\Observers\CaseRelationCountObserver;
use App\Schema\Eloquent\Traits\HasFragment;
use App\Schema\Eloquent\Traits\HasSchema;
use App\Schema\Schema;
use App\Schema\SchemaObject;
use App\Schema\SchemaProvider;
use App\Schema\Types\DateTimeType;
use App\Schema\Types\StringType;
use App\Schema\Types\UUIDType;
use Carbon\CarbonImmutable;
use DomainException;
use Dyrynda\Database\Casts\EfficientUuid;
use Dyrynda\Database\Support\GeneratesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use MinVWS\DBCO\Enum\Models\TestResultResult;
use MinVWS\DBCO\Enum\Models\TestResultSource;
use MinVWS\DBCO\Enum\Models\TestResultType;
use MinVWS\DBCO\Enum\Models\TestResultTypeOfTest;

use function app;
use function is_null;

/**
 * @property int $id
 * @property string $uuid
 * @property int $schema_version
 * @property string $organisation_uuid
 * @property ?int $person_id
 * @property ?string $case_uuid
 * @property TestResultType $type
 * @property TestResultResult $result
 * @property TestResultSource $source
 * @property ?string $source_id
 * @property ?string $monster_number
 * @property CarbonImmutable $date_of_test
 * @property ?CarbonImmutable $date_of_symptom_onset
 * @property CarbonImmutable $received_at
 * @property CarbonImmutable $created_at
 * @property CarbonImmutable $updated_at
 * @property ?CarbonImmutable $deleted_at
 * @property string $message_id
 * @property TestResultTypeOfTest $type_of_test
 * @property string $custom_type_of_test
 * @property ?CarbonImmutable $date_of_result
 * @property string $sample_location
 * @property string $loboratory
 *
 * @property EloquentCase $covidCase
 * @property General $general
 * @property EloquentOrganisation $organisation
 * @property Person $person
 * @property TestResultRaw $raw
 */
class TestResult extends Model implements SchemaObject, SchemaProvider, TestResultCommon, CountableCaseRelation
{
    use CamelCaseAttributes;
    use HasFactory;
    use HasSchema;
    use SoftDeletes;
    use GeneratesUuid;
    use HasFragment;

    protected $table = 'test_result';

    protected $casts = [
        'uuid' => EfficientUuid::class,
        'type' => TestResultType::class,
        'source' => TestResultSource::class,
        'type_of_test' => TestResultTypeOfTest::class,
        'result' => TestResultResult::class,
        'date_of_test' => 'datetime',
        'date_of_symptom_onset' => 'datetime',
        'received_at' => 'datetime',
        'date_of_result' => 'datetime',
    ];

    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class);

        $schema->setUseVersionedClasses(true);
        $schema->setVersionedNamespace('App\\Models\\Versions\\TestResult');
        $schema->setCurrentVersion(1);

        $schema->add(UUIDType::createField('uuid'))->setAllowsNull(false);
        $schema->add(StringType::createField('messageId'))->setAllowsNull(false);
        $schema->add(EloquentOrganisation::getSchema()->getVersion(1)->createField('organisation'))->setAllowsNull(false);
        $schema->add(Person::getSchema()->getVersion(1)->createField('person'))->setAllowsNull(true);
        $schema->add(TestResultRaw::getSchema()->getVersion(1)->createField('raw'))->setAllowsNull(true);
        $schema->add(TestResultType::getVersion(1)->createField('type'))->setAllowsNull(false);
        $schema->add(TestResultSource::getVersion(1)->createField('source'))->setAllowsNull(false);
        $schema->add(StringType::createField('sourceId'))->setAllowsNull(true);
        $schema->add(StringType::createField('monsterNumber'))->setAllowsNull(true);
        $schema->add(DateTimeType::createField('dateOfTest'))->setAllowsNull(false);
        $schema->add(DateTimeType::createField('dateOfSymptomOnset'))->setAllowsNull(true);
        $schema->add(General::getSchema()->getVersion(1)->createField('general'))->setAllowsNull(false);
        $schema->add(DateTimeType::createField('receivedAt'))->setAllowsNull(false);
        $schema->add(DateTimeType::createField('createdAt'))->setAllowsNull(false);
        $schema->add(DateTimeType::createField('updatedAt'))->setAllowsNull(false);
        $schema->add(TestResultTypeOfTest::getVersion(1)->createField('typeOfTest'))->setAllowsNull(true);
        $schema->add(StringType::createField('customTypeOfTest'))->setAllowsNull(true);
        $schema->add(DateTimeType::createField('dateOfResult'))->setAllowsNull(false);
        $schema->add(StringType::createField('sampleLocation'))->setAllowsNull(true);
        $schema->add(TestResultResult::getVersion(1)->createField('result'))
            ->setDefaultValue(TestResultResult::unknown())
            ->setAllowsNull(false);
        $schema->add(StringType::createField('laboratory'))->setAllowsNull(true);

        return app(PurposeSpecificationBinder::class)->bind($schema);
    }

    protected static function boot(): void
    {
        parent::boot();

        self::observe(CaseRelationCountObserver::class);
    }

    public function setTypeOfTest(TestResultTypeOfTest $type, ?string $customType): void
    {
        if ($type === TestResultTypeOfTest::custom() && is_null($customType)) {
            throw new DomainException("This type needs custom data");
        }
        if ($type !== TestResultTypeOfTest::custom() && !is_null($customType)) {
            throw new DomainException("This type cannot handle custom data");
        }

        $this->type_of_test = $type;
        $this->customTypeOfTest = $customType;
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(EloquentOrganisation::class, 'organisation_uuid');
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function general(): HasOne
    {
        return $this->hasOneFragment(General::class);
    }

    public function raw(): HasOne
    {
        return $this->hasOne(TestResultRaw::class);
    }

    public function covidCase(): BelongsTo
    {
        return $this->belongsTo(EloquentCase::class, 'case_uuid');
    }

    public function getCaseUuid(): ?string
    {
        return $this->case_uuid;
    }

    public function getCaseRelationCount(): int
    {
        return $this->covidCase?->testResults()->count() ?? 0;
    }

    public function getConfigKey(): string
    {
        return self::class;
    }
}

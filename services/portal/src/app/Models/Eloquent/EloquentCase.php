<?php

declare(strict_types=1);

namespace App\Models\Eloquent;

use App\Models\CovidCase\Abroad;
use App\Models\CovidCase\AlternateContact;
use App\Models\CovidCase\AlternateResidency;
use App\Models\CovidCase\AlternativeLanguage;
use App\Models\CovidCase\Communication;
use App\Models\CovidCase\Contact;
use App\Models\CovidCase\Contacts;
use App\Models\CovidCase\Deceased;
use App\Models\CovidCase\EduDaycare;
use App\Models\CovidCase\ExtensiveContactTracing;
use App\Models\CovidCase\General;
use App\Models\CovidCase\GeneralPractitioner;
use App\Models\CovidCase\GroupTransport;
use App\Models\CovidCase\Hospital;
use App\Models\CovidCase\Housemates;
use App\Models\CovidCase\Immunity;
use App\Models\CovidCase\Index;
use App\Models\CovidCase\Job;
use App\Models\CovidCase\Medication;
use App\Models\CovidCase\Pregnancy;
use App\Models\CovidCase\PrincipalContextualSettings;
use App\Models\CovidCase\RecentBirth;
use App\Models\CovidCase\RiskLocation;
use App\Models\CovidCase\SourceEnvironments;
use App\Models\CovidCase\Symptoms;
use App\Models\CovidCase\Test;
use App\Models\CovidCase\UnderlyingSuffering;
use App\Models\CovidCase\Vaccination;
use App\Models\Eloquent\Traits\CamelCaseAttributes;
use App\Models\Fields\IdFieldsHelper;
use App\Models\Fields\PurposeSpecificationBinder;
use App\Models\Policy\PolicyVersion;
use App\Models\Versions\CovidCase\CovidCaseCommon;
use App\Observers\CaseStatusChangeObserver;
use App\Observers\EloquentCaseObserver;
use App\Schema\Eloquent\Relations\HasOneFragment;
use App\Schema\Eloquent\Traits\HasFragment;
use App\Schema\Eloquent\Traits\HasSchema;
use App\Schema\Fields\ArrayField;
use App\Schema\Fields\Field;
use App\Schema\Schema;
use App\Schema\SchemaObject;
use App\Schema\SchemaProvider;
use App\Schema\SchemaVersion;
use App\Schema\Types\DateTimeType;
use App\Schema\Types\IntType;
use App\Schema\Types\SchemaType;
use App\Schema\Types\StringType;
use App\Scopes\CaseAuthScope;
use App\Scopes\CaseListAuthScope;
use App\Scopes\OrganisationAuthScope;
use App\Services\BcoNumber\BcoNumberService;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\JoinClause;
use MinVWS\Codable\EncodingContext;
use MinVWS\DBCO\Encryption\Security\Sealed;
use MinVWS\DBCO\Encryption\Security\StorageTerm;
use MinVWS\DBCO\Enum\Models\AutomaticAddressVerificationStatus;
use MinVWS\DBCO\Enum\Models\BCOPhase;
use MinVWS\DBCO\Enum\Models\BCOStatus;
use MinVWS\DBCO\Enum\Models\ContactTracingStatus;
use MinVWS\DBCO\Enum\Models\IndexStatus;
use MinVWS\DBCO\Enum\Models\Priority;
use MinVWS\DBCO\Enum\Models\TaskGroup;
use RuntimeException;

use function app;
use function array_filter;
use function auth;
use function collect;
use function config;
use function is_numeric;
use function join;
use function mb_strtoupper;
use function sprintf;
use function trim;

/**
 * @property string $uuid
 * @property ?string $owner
 * @property ?string $policy_version_uuid
 * @property ?CarbonInterface $date_of_symptom_onset
 * @property ?CarbonImmutable $tested_at
 * @property CarbonImmutable $created_at
 * @property CarbonImmutable $updated_at
 * @property BCOStatus $bco_status
 * @property IndexStatus $index_status
 * @property ?string $source
 * @property ?string $case_id
 * @property ?string $hpzone_number
 * @property ?string $index_submitted_at
 * @property ?string $seen_at
 * @property string $organisation_uuid
 * @property ?string $current_organisation_uuid
 * @property ?string $organisation_label
 * @property ?string $assigned_user_uuid
 * @property ?string $assigned_organisation_uuid
 * @property ?string $assigned_organisation_label
 * @property ?string $assigned_case_list_uuid
 * @property ?CarbonImmutable $pairing_expires_at
 * @property ?CarbonImmutable $window_expires_at
 * @property ?string $export_id
 * @property ?string $exported_at
 * @property ?string $copied_at
 * @property ?CarbonImmutable $date_of_test
 * @property ?string $test_monster_number
 * @property ?bool $symptomatic
 * @property ?string $index_bsn
 * @property ?string $index_bsn_hash
 * @property ?string $index_bsn_ends_with
 * @property Symptoms $symptoms
 * @property Test $test
 * @property Vaccination $vaccination
 * @property UnderlyingSuffering $underlying_suffering
 * @property Pregnancy $pregnancy
 * @property Medication $medication
 * @property GeneralPractitioner $general_practitioner
 * @property General $general
 * @property ?string $index_submitted_symptoms
 * @property GroupTransport $group_transport
 * @property ?ContactTracingStatus $status_index_contact_tracing
 * @property string $status_explanation
 * @property ?string $index_submitted_date_of_symptom_onset
 * @property ?string $index_submitted_date_of_test
 * @property ?CarbonInterface $completed_at
 * @property ?string $pseudo_bsn_guid
 * @property ?string $search_date_of_birth
 * @property ?string $search_email
 * @property ?string $search_phone
 * @property ?CarbonImmutable $deleted_at
 * @property BCOPhase $bco_phase
 * @property int $schema_version
 * @property Immunity $immunity
 * @property Priority $priority
 * @property ?bool $is_approved
 * @property string $organisation_planner_view
 * @property string $current_organisation_planner_view
 * @property string $case_list_planner_view
 * @property ?int $osiris_number
 * @property ?CarbonInterface $episode_start_date
 * @property ?int $index_age
 * @property ?int $index_age_calculator_key
 * @property AutomaticAddressVerificationStatus $automatic_address_verification_status
 *
 * @property ?EloquentOrganisation $assignedOrganisation
 * @property ?CaseList $assignedCaseList
 * @property ?EloquentUser $assignedUser
 * @property BcoNumber $bcoNumber
 * @property Collection<int, CaseLabel> $caseLabels
 * @property ?EloquentCaseLock $caseLock
 * @property Collection<int, CaseUpdate> $caseUpdates
 * @property Collection<int, Chore> $chores
 * @property Communication $communication
 * @property Collection<int, Context> $contexts
 * @property ?EloquentUser $createdBy
 * @property Index $index
 * @property Collection<int, Note> $notes
 * @property EloquentOrganisation $organisation
 * @property Collection<int, OsirisNotification> $osirisNotifications
 * @property ?PolicyVersion $policyVersion
 * @property CovidCaseSearch $search
 * @property Collection<int, EloquentSituation> $situations
 * @property Collection<int, CaseStatusHistory> $statusHistory
 * @property Collection<int, EloquentTask> $tasks
 * @property Collection<int, TestResult> $testResults
 * @property Collection<int, Timeline> $timeline
 *
 * @property ?string $caseId
 * @property ?string $name
 * @property ?string $hpzoneNumber
 */
class EloquentCase extends EloquentBaseModel implements SchemaObject, SchemaProvider, CovidCaseCommon
{
    use HasFactory;
    use CamelCaseAttributes;
    use SoftDeletes;
    use HasSchema;
    use HasFragment;

    protected $table = 'covidcase';

    protected $casts = [
        'date_of_symptom_onset' => 'date',
        'tested_at' => 'date',
        'bco_status' => BCOStatus::class,
        'index_status' => IndexStatus::class,
        'organisation_label' => Sealed::class . ':' . StorageTerm::LONG . ',created_at',
        'assigned_organisation_label' => Sealed::class . ':' . StorageTerm::LONG . ',created_at',
        'pairing_expires_at' => 'datetime',
        'window_expires_at' => 'datetime',
        'date_of_test' => 'date',
        'symptoms' => Symptoms::class . ':' . StorageTerm::LONG . ',created_at',
        'test' => Test::class . ':' . StorageTerm::LONG . ',created_at',
        'vaccination' => Vaccination::class . ':' . StorageTerm::LONG . ',created_at',
        'underlying_suffering' => UnderlyingSuffering::class . ':' . StorageTerm::LONG . ',created_at',
        'pregnancy' => Pregnancy::class . ':' . StorageTerm::LONG . ',created_at',
        'medication' => Medication::class . ':' . StorageTerm::LONG . ',created_at',
        'general_practitioner' => GeneralPractitioner::class . ':' . StorageTerm::LONG . ',created_at',
        'general' => General::class . ':' . StorageTerm::LONG . ',created_at',
        'index_submitted_symptoms' => Sealed::class . ':' . StorageTerm::LONG . ',created_at',
        'group_transport' => GroupTransport::class . ':' . StorageTerm::LONG . ',created_at',
        'status_index_contact_tracing' => ContactTracingStatus::class,
        'index_submitted_date_of_symptom_onset' => Sealed::class . ':' . StorageTerm::LONG . ',created_at',
        'index_submitted_date_of_test' => Sealed::class . ':' . StorageTerm::LONG . ',created_at',
        'completed_at' => 'datetime',
        'bco_phase' => BCOPhase::class,
        'immunity' => Immunity::class . ':' . StorageTerm::LONG . ',created_at',
        'priority' => Priority::class,
        'is_approved' => 'bool',
        'episode_start_date' => 'date',
        'automatic_address_verification_status' => AutomaticAddressVerificationStatus::class,
    ];

    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class);

        $schema->setUseVersionedClasses(true);
        $schema->setName('CovidCase');
        $schema->setVersionedNamespace('App\\Models\\Versions\\CovidCase');

        $schema->setCurrentVersion(self::getCurrentSchemaVersion(defaultVersion: 8));

        // Common fields
        IdFieldsHelper::addIdFieldsToSchema($schema);
        $schema->add(StringType::createField('caseId'));
        $schema->add(StringType::createField('hpzoneNumber'));
        $schema->add(EloquentOrganisation::getSchema()->getVersion(1)->createField('organisation'))->setAllowsNull(false);
        $schema->add(EloquentOrganisation::getSchema()->getVersion(1)->createField('assignedOrganisation'));
        $schema->add(CaseList::getSchema()->getVersion(1)->createField('assignedCaseList'));
        $schema->add(EloquentUser::getSchema()->getVersion(1)->createField('assignedUser'));
        $schema->add(BCOStatus::getVersion(1)->createField('bcoStatus'))->setAllowsNull(false);
        $schema->add(BCOPhase::getVersion(1)->createField('bcoPhase'));
        $schema->add(IndexStatus::getVersion(1)->createField('indexStatus'));
        $schema->add(EloquentUser::getSchema()->getVersion(1)->createField('createdBy'))->setExcluded();
        $schema->add(DateTimeType::createField('createdAt'))->setAllowsNull(false);
        $schema->add(DateTimeType::createField('updatedAt'))->setAllowsNull(false);
        $schema->add(DateTimeType::createField('completedAt'));
        $schema->add(DateTimeType::createField('deletedAt'));
        $schema->add(AutomaticAddressVerificationStatus::getVersion(1)->createField('automaticAddressVerificationStatus'))
            ->setAllowsNull(false);

        $schema->add(Abroad::getSchema()->getVersion(1)->createField('abroad'))->setAllowsNull(false);
        $schema->add(AlternateContact::getSchema()->getVersion(1)->createField('alternateContact'))->setAllowsNull(false);

        $schema->add(Contact::getSchema()->getVersion(1)->createField('contact'))->setAllowsNull(false);

        $schema->add(Contacts::getSchema()->getVersion(1)->createField('contacts'))->setAllowsNull(false)->setMaxVersion(1);
        $schema->add(Contacts::getSchema()->getVersion(2)->createField('contacts'))->setAllowsNull(false)->setMinVersion(2);

        $schema->add(Deceased::getSchema()->getVersion(1)->createField('deceased'))->setAllowsNull(false);
        $schema->add(Job::getSchema()->getVersion(1)->createField('job'))->setAllowsNull(false);
        $schema->add(Hospital::getSchema()->getVersion(1)->createField('hospital'))->setAllowsNull(false);
        $schema->add(Housemates::getSchema()->getVersion(1)->createField('housemates'))->setAllowsNull(false);
        $schema->add(AlternativeLanguage::getSchema()->getVersion(1)->createField('alternativeLanguage'))->setAllowsNull(false);
        $schema->add(AlternateResidency::getSchema()->getVersion(1)->createField('alternateResidency'))->setAllowsNull(false);
        $schema->add(GeneralPractitioner::getSchema()->getVersion(1)->createField('generalPractitioner'))->setAllowsNull(false);
        $schema->add(PrincipalContextualSettings::getSchema()->getVersion(1)->createField('principalContextualSettings'))->setAllowsNull(
            false,
        );
        $schema->add(GroupTransport::getSchema()->getVersion(1)->createField('groupTransport'))->setAllowsNull(false);
        $schema->add(Medication::getSchema()->getVersion(1)->createField('medication'))->setAllowsNull(false);
        $schema->add(IntType::createField('osirisNumber'))->setAllowsNull(true);

        $schema->add(CaseUpdate::getSchema()->getVersion(1)->createArrayField('caseUpdates'))->setExcluded()->setAllowsNull(false);

        // The tasks and contexts fields are not used directly, but do allow us to associate different versions of tasks and context
        // schemas with different versions of the case schema. Tasks and context should be instantiated with the
        // `createTask` and `createContext` methods defined below.
        $schema->add(Context::getSchema()->getVersion(1)->createArrayField('contexts'))
            ->setExcluded()
            ->setIncludedInEncode(true, EncodingContext::MODE_EXPORT)
            ->setAllowsNull(false);

        // Fields up to version 1
        $schema->add(ExtensiveContactTracing::getSchema()->getVersion(1)->createField('extensiveContactTracing'))->setAllowsNull(
            false,
        )->setMaxVersion(1);
        $schema->add(Vaccination::getSchema()->getVersion(1)->createField('vaccination'))->setAllowsNull(false)->setMaxVersion(1);
        $schema->add(self::createTasksField(taskSchemaVersion: 1, maxVersion: 1));

        // Fields starting from version 2
        $schema->add(ExtensiveContactTracing::getSchema()->getVersion(2)->createField('extensiveContactTracing'))->setAllowsNull(
            false,
        )->setMinVersion(2)->setMaxVersion(4);
        $schema->add(Vaccination::getSchema()->getVersion(2)->createField('vaccination'))->setAllowsNull(false)->setMinversion(
            2,
        )->setMaxVersion(3);
        $schema->add(self::createTasksField(taskSchemaVersion: 2, minVersion: 2, maxVersion: 2));

        // Fields up to version 2
        $schema->add(Test::getSchema()->getVersion(1)->createField('test'))->setAllowsNull(false)->setMaxVersion(2);
        $schema->add(General::getSchema()->getVersion(1)->createField('general'))->setAllowsNull(false)->setMaxVersion(2);
        $schema->add(Communication::getSchema()->getVersion(1)->createField('communication'))->setAllowsNull(false)->setMaxVersion(2);

        // Fields starting from version 3
        $schema->add(Test::getSchema()->getVersion(2)->createField('test'))->setAllowsNull(false)->setMinVersion(3)->setMaxVersion(4);
        $schema->add(General::getSchema()->getVersion(2)->createField('general'))->setAllowsNull(false)->setMinVersion(3);
        $schema->add(Communication::getSchema()->getVersion(2)->createField('communication'))->setAllowsNull(false)->setMinVersion(
            3,
        )->setMaxVersion(4);
        $schema->add(self::createTasksField(taskSchemaVersion: 3, minVersion: 3, maxVersion: 3));

        // Fields up to version 3
        $schema->add(UnderlyingSuffering::getSchema()->getVersion(1)->createField('underlyingSuffering'))->setAllowsNull(
            false,
        )->setMaxVersion(3);

        // Fields starting from version 4
        $schema->add(Index::getSchema()->getVersion(1)->createField('index'))->setAllowsNull(false)->setMaxVersion(4);
        $schema->add(Vaccination::getSchema()->getVersion(3)->createField('vaccination'))->setAllowsNull(false)->setMinVersion(4);
        $schema->add(UnderlyingSuffering::getSchema()->getVersion(2)->createField('underlyingSuffering'))->setAllowsNull(
            false,
        )->setMinVersion(4);
        $schema->add(self::createTasksField(taskSchemaVersion: 4, minVersion: 4, maxVersion: 4));

        // Fields up to version 4
        $schema->add(EduDaycare::getSchema()->getVersion(1)->createField('eduDaycare'))->setAllowsNull(false)->setMaxVersion(4);
        $schema->add(Immunity::getSchema()->getVersion(1)->createField('immunity'))->setAllowsNull(false)->setMaxVersion(4);
        $schema->add(Pregnancy::getSchema()->getVersion(1)->createField('pregnancy'))->setAllowsNull(false)->setMaxVersion(4);
        $schema->add(RecentBirth::getSchema()->getVersion(1)->createField('recentBirth'))->setAllowsNull(false)->setMaxVersion(4);
        $schema->add(RiskLocation::getSchema()->getVersion(1)->createField('riskLocation'))->setAllowsNull(false)->setMaxVersion(4);
        $schema->add(SourceEnvironments::getSchema()->getVersion(1)->createField('sourceEnvironments'))->setAllowsNull(
            false,
        )->setMaxVersion(4);

        // Fields starting from version 5
        $schema->add(Index::getSchema()->getVersion(2)->createField('index'))->setAllowsNull(false)->setMinVersion(5);
        $schema->add(Communication::getSchema()->getVersion(3)->createField('communication'))->setAllowsNull(false)->setMinVersion(5);
        $schema->add(EduDaycare::getSchema()->getVersion(2)->createField('eduDaycare'))->setAllowsNull(false)->setMinVersion(5);
        $schema->add(ExtensiveContactTracing::getSchema()->getVersion(3)->createField('extensiveContactTracing'))->setAllowsNull(
            false,
        )->setMinVersion(5);
        $schema->add(Immunity::getSchema()->getVersion(2)->createField('immunity'))->setAllowsNull(false)->setMinVersion(5);
        $schema->add(Pregnancy::getSchema()->getVersion(2)->createField('pregnancy'))->setAllowsNull(false)->setMinVersion(5);
        $schema->add(RecentBirth::getSchema()->getVersion(2)->createField('recentBirth'))->setAllowsNull(false)->setMinVersion(5);
        $schema->add(RiskLocation::getSchema()->getVersion(2)->createField('riskLocation'))->setAllowsNull(false)->setMinVersion(5);
        $schema->add(SourceEnvironments::getSchema()->getVersion(2)->createField('sourceEnvironments'))->setAllowsNull(
            false,
        )->setMinVersion(5);
        $schema->add(Test::getSchema()->getVersion(3)->createField('test'))->setAllowsNull(false)->setMinVersion(5)->setMaxVersion(5);
        $schema->add(self::createTasksField(taskSchemaVersion: 5, minVersion: 5, maxVersion: 5));
        $schema->add(TestResult::getSchema()->getVersion(1)->createArrayField('testResults'))
            ->setExcluded()
            ->setIncludedInEncode(true, EncodingContext::MODE_EXPORT)
            ->setAllowsNull(false)
            ->setMinVersion(4);

        // Fields up to version 6
        $schema->add(Symptoms::getSchema()->getVersion(1)->createField('symptoms'))->setAllowsNull(false)->setMaxVersion(6);

        // Fields starting from version 6
        $schema->add(self::createTasksField(taskSchemaVersion: 6, minVersion: 6, maxVersion: 6));
        $schema->add(Test::getSchema()->getVersion(4)->createField('test'))->setAllowsNull(false)->setMinVersion(6);

        // Fields starting from version 7
        $schema->add(self::createTasksField(taskSchemaVersion: 7, minVersion: 7));
        $schema->add(Symptoms::getSchema()->getVersion(2)->createField('symptoms'))->setAllowsNull(false)->setMinVersion(7);

        return app(PurposeSpecificationBinder::class)->bind($schema);
    }

    protected static function boot(): void
    {
        parent::boot();

        self::observe(EloquentCaseObserver::class);
        self::observe(CaseStatusChangeObserver::class);
    }

    public function alternateResidency(): HasOneFragment
    {
        return $this->hasOneFragment(AlternateResidency::class, 'case_uuid');
    }

    public function alternativeLanguage(): HasOneFragment
    {
        return $this->hasOneFragment(AlternativeLanguage::class, 'case_uuid');
    }

    public function deceased(): HasOneFragment
    {
        return $this->hasOneFragment(Deceased::class, 'case_uuid');
    }

    public function communication(): HasOneFragment
    {
        return $this->hasOneFragment(Communication::class, 'case_uuid');
    }

    public function contacts(): HasOneFragment
    {
        return $this->hasOneFragment(Contacts::class, 'case_uuid');
    }

    public function contact(): HasOneFragment
    {
        return $this->hasOneFragment(Contact::class, 'case_uuid');
    }

    public function abroad(): HasOneFragment
    {
        return $this->hasOneFragment(Abroad::class, 'case_uuid');
    }

    public function alternateContact(): HasOneFragment
    {
        return $this->hasOneFragment(AlternateContact::class, 'case_uuid');
    }

    public function eduDaycare(): HasOneFragment
    {
        return $this->hasOneFragment(EduDaycare::class, 'case_uuid');
    }

    public function hospital(): HasOneFragment
    {
        return $this->hasOneFragment(Hospital::class, 'case_uuid');
    }

    public function principalContextualSettings(): HasOneFragment
    {
        return $this->hasOneFragment(PrincipalContextualSettings::class, 'case_uuid');
    }

    public function extensiveContactTracing(): HasOneFragment
    {
        return $this->hasOneFragment(ExtensiveContactTracing::class, 'case_uuid');
    }

    public function housemates(): HasOneFragment
    {
        return $this->hasOneFragment(Housemates::class, 'case_uuid');
    }

    public function index(): HasOneFragment
    {
        return $this->hasOneFragment(Index::class, 'case_uuid');
    }

    public function job(): HasOneFragment
    {
        return $this->hasOneFragment(Job::class, 'case_uuid');
    }

    public function recentBirth(): HasOneFragment
    {
        return $this->hasOneFragment(RecentBirth::class, 'case_uuid');
    }

    public function riskLocation(): HasOneFragment
    {
        return $this->hasOneFragment(RiskLocation::class, 'case_uuid');
    }

    public function sourceEnvironments(): HasOneFragment
    {
        return $this->hasOneFragment(SourceEnvironments::class, 'case_uuid');
    }

    public function search(): HasMany
    {
        return $this->hasMany(CovidCaseSearch::class);
    }

    protected static function booted(): void
    {
        if (config('authorization.roles.admin') !== null) {
            static::addGlobalScope(app()->make(CaseAuthScope::class));
        }

        static::creating(static function (self $case): void {
            if ($case->case_id !== null) {
                return;
            }

            /** @var BcoNumberService $bcoNumberService */
            $bcoNumberService = app(BcoNumberService::class);
            $case->case_id = $bcoNumberService->makeUniqueNumber()->bco_number;
        });
    }

    public function caseLabels(): BelongsToMany
    {
        return $this->belongsToMany(CaseLabel::class, 'case_case_label', 'case_uuid')
            ->join('covidcase', 'case_case_label.case_uuid', '=', 'covidcase.uuid')
            ->join('case_label_organisation', static function (JoinClause $joinClause): void {
                $joinClause->on('case_label_organisation.case_label_uuid', '=', 'case_label.uuid');
                $joinClause->on('case_label_organisation.organisation_uuid', '=', 'covidcase.organisation_uuid');
            })
            ->orderByDesc('case_label_organisation.sortorder');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(EloquentTask::class, 'case_uuid')->orderBy('created_at');
    }

    public function caseUpdates(): HasMany
    {
        return $this->hasMany(CaseUpdate::class, 'case_uuid')->orderBy('received_at');
    }

    public function contexts(): HasMany
    {
        return $this->hasMany(Context::class, 'covidcase_uuid');
    }

    public function testResults(): HasMany
    {
        return $this->hasMany(TestResult::class, 'case_uuid')->orderBy('received_at');
    }

    public function situations(): BelongsToMany
    {
        return $this->belongsToMany(EloquentSituation::class, 'situation_case', 'case_uuid', 'situation_uuid');
    }

    public function organisation(): BelongsTo
    {
        return
            $this->belongsTo(EloquentOrganisation::class)
            ->withoutGlobalScope(OrganisationAuthScope::class);
    }

    public function assignedOrganisation(): BelongsTo
    {
        return
            $this->belongsTo(EloquentOrganisation::class, 'assigned_organisation_uuid')
            ->withoutGlobalScope(OrganisationAuthScope::class);
    }

    public function assignedCaseList(): BelongsTo
    {
        return
            $this->belongsTo(CaseList::class, 'assigned_case_list_uuid')
            ->withoutGlobalScope(CaseListAuthScope::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(EloquentUser::class, 'owner');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(EloquentUser::class, 'assigned_user_uuid');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(Note::class, 'case_uuid');
    }

    public function timeline(): HasMany
    {
        return $this->hasMany(Timeline::class, 'case_uuid');
    }

    public function bcoNumber(): MorphOne
    {
        return $this->morphOne(BcoNumber::class, 'bcoNumberable');
    }

    public function chores(): MorphMany
    {
        return $this->morphMany(Chore::class, 'resource');
    }

    public function osirisNotifications(): HasMany
    {
        return $this->hasMany(OsirisNotification::class, 'case_uuid')->orderBy('notified_at');
    }

    public function policyVersion(): BelongsTo
    {
        return $this->belongsTo(PolicyVersion::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(CaseStatusHistory::class);
    }

    public function isWaitingForApproval(): bool
    {
        return $this->is_approved === null && $this->bcoStatus === BCOStatus::completed();
    }

    public function getGroupedTasks(): array
    {
        $groupedTasks = [];
        foreach (TaskGroup::allValues() as $value) {
            $groupedTasks[$value] = collect();
        }

        foreach ($this->tasks as $task) {
            $groupedTasks[$task->taskGroup->value][] = $task;
        }

        return $groupedTasks;
    }

    /**
     * Returns the task schema version associated with this case's schema version.
     *
     * @return SchemaVersion<EloquentTask>
     */
    public function getTaskSchemaVersion(): SchemaVersion
    {
        /** @var ArrayField $field */
        $field = $this->getSchemaVersion()->getField('tasks');
        /** @var SchemaType $type */
        $type = $field->getElementType();
        return $type->getSchemaVersion();
    }

    /**
     * Create task for this case.
     *
     * Makes sure the task uses the correct schema version based on the case schema version.
     */
    public function createTask(): EloquentTask
    {
        /** @var EloquentTask $task $task */
        $task = $this->getTaskSchemaVersion()->newInstance();
        $task->covidCase()->associate($this);
        return $task;
    }

    /**
     * Returns the context schema version associated with this case's schema version.
     *
     * @return SchemaVersion<Context>
     */
    public function getContextSchemaVersion(): SchemaVersion
    {
        /** @var ArrayField $field */
        $field = $this->getSchemaVersion()->getField('contexts');
        /** @var SchemaType $type */
        $type = $field->getElementType();
        return $type->getSchemaVersion();
    }

    /**
     * Create context for this case.
     *
     * Makes sure the context uses the correct schema version based on the case schema version.
     */
    public function createContext(): Context
    {
        /** @var Context $context $task */
        $context = $this->getContextSchemaVersion()->newInstance();
        $context->case()->associate($this);
        return $context;
    }

    public function getNameAttribute(): ?string
    {
        $initials = $this->index->initials;
        $formattedInitials = isset($initials) ? mb_strtoupper($initials) : null;
        if ($this->index->firstname && $formattedInitials) {
            $formattedInitials = "($formattedInitials)";
        }
        return trim(join(' ', array_filter([$this->index->firstname, $formattedInitials, $this->index->lastname])));
    }

    public function setHpzoneNumberAttribute(?string $value): void
    {
        $newHpzoneNumberEqualToOldCaseId = isset($this->attributes['case_id'], $this->attributes['hpzone_number'])
            && $value !== null
            && $this->attributes['case_id'] === $this->attributes['hpzone_number'];

        if ($newHpzoneNumberEqualToOldCaseId) {
            $this->attributes['case_id'] = $value;
        }
        $this->attributes['hpzone_number'] = $value;
    }

    public function setCaseIdAttribute(?string $value): void
    {
        $caseIdIsOverwritten = isset($this->attributes['case_id']) && $this->attributes['case_id'] !== $value;
        if ($caseIdIsOverwritten) {
            throw new RuntimeException(
                sprintf('Overwriting case_id is not allowed for case %s!', $this->attributes['uuid']),
            );
        }

        $this->attributes['case_id'] = $value;
    }

    public function isClosable(?EloquentUser $eloquentUser = null): bool
    {
        /** @var EloquentUser $user */
        $user = $eloquentUser ?? auth()->user();

        if ($this->bcoStatus->value === BCOStatus::archived()->value) {
            return false;
        }

        if ($this->assigned_organisation_uuid !== null) {
            return $user->getRequiredOrganisation()->uuid === $this->assigned_organisation_uuid;
        }

        return $user->getRequiredOrganisation()->uuid === $this->organisation_uuid;
    }

    public function isReopenable(): bool
    {
        $isArchived = $this->bcoStatus->value === BCOStatus::archived()->value;
        $isCompleted = $this->bcoStatus->value === BCOStatus::completed()->value;

        return !$this->trashed() && ($isArchived || $isCompleted);
    }

    public function isAssignable(?EloquentUser $eloquentUser = null): bool
    {
        /** @var EloquentUser $user */
        $user = $eloquentUser ?? auth()->user();

        if ($this->bco_status->value === BCOStatus::archived()->value) {
            return false;
        }

        return $this->assignedOrganisation === null || $user->isInOrganisation($this->assignedOrganisation->uuid);
    }

    public function isOutsourceable(?EloquentUser $eloquentUser = null): bool
    {
        /** @var EloquentUser $user */
        $user = $eloquentUser ?? auth()->user();

        if ($user->isInOrganisation($this->organisation->uuid)) {
            $isOutsourced = isset($this->assignedOrganisation);
            $isAssignedToUser = isset($this->assignedUser);
            return !($isOutsourced && $isAssignedToUser);
        }

        return true;
    }

    public function canChangeOrganisation(?EloquentUser $eloquentUser = null): bool
    {
        /** @var EloquentUser $user */
        $user = $eloquentUser ?? auth()->user();

        return $user->getRequiredOrganisation()->uuid === $this->organisation_uuid;
    }

    public function isListable(?EloquentUser $eloquentUser = null): bool
    {
        return false;
    }

    public function getVersionedResourceType(): string
    {
        $version = $this->getSchemaVersion()->getVersion();

        return 'covid-case' . ($version ? "-v{$version}" : '');
    }

    /**
     * @computed attribute
     */
    public function getReportNumber(): ?string
    {
        if ($this->hpzone_number !== null) {
            return $this->hpzone_number;
        }

        return $this->case_id;
    }

    private static function createTasksField(int $taskSchemaVersion, int $minVersion = 1, ?int $maxVersion = null): Field
    {
        return EloquentTask::getSchema()
            ->getVersion($taskSchemaVersion)
            ->createArrayField('tasks')
            ->setExcluded()
            ->setMinVersion($minVersion)
            ->setMaxVersion($maxVersion)
            ->setIncludedInEncode(true, EncodingContext::MODE_EXPORT)
            ->setAllowsNull(false);
    }

    private static function getCurrentSchemaVersion(int $defaultVersion): int
    {
        return self::getOverrideCaseVersion() ?? $defaultVersion;
    }

    private static function getOverrideCaseVersion(): ?int
    {
        if (!app()->has('config')) {
            return null;
        }

        /** @var ?numeric-string $overrideCaseVersion */
        $overrideCaseVersion = config('schema.overrideCaseVersion');
        if (is_numeric($overrideCaseVersion)) {
            return (int) $overrideCaseVersion;
        }

        return null;
    }
}

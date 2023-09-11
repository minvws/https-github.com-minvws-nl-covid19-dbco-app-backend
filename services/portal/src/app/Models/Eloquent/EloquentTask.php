<?php

declare(strict_types=1);

namespace App\Models\Eloquent;

use App\Models\Answer;
use App\Models\Eloquent\Traits\CamelCaseAttributes;
use App\Models\Fields\IdFieldsHelper;
use App\Models\Fields\PurposeSpecificationBinder;
use App\Models\Task;
use App\Models\Versions\Task\TaskCommon;
use App\Observers\CaseRelationCountObserver;
use App\Observers\EloquentTaskObserver;
use App\Observers\EloquentTaskSearchHashObserver;
use App\Schema\Eloquent\Traits\HasSchema;
use App\Schema\Schema;
use App\Schema\SchemaObject;
use App\Schema\SchemaProvider;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use MinVWS\DBCO\Encryption\Security\Sealed;
use MinVWS\DBCO\Encryption\Security\StorageTerm;
use MinVWS\DBCO\Enum\Models\ContactCategory;
use MinVWS\DBCO\Enum\Models\InformedBy;
use MinVWS\DBCO\Enum\Models\InformStatus;
use MinVWS\DBCO\Enum\Models\TaskGroup;

use function app;
use function trim;

/**
 * @property string $uuid
 * @property string $case_uuid
 * @property string $task_type
 * @property string $source
 * @property ?string $label
 * @property ?string $derived_label
 * @property ?string $task_context
 * @property ?string $nature
 * @property ?ContactCategory $category
 * @property ?CarbonInterface $date_of_last_exposure
 * @property ?InformedBy $communication
 * @property CarbonInterface $created_at
 * @property CarbonInterface $updated_at
 * @property ?string $questionnaire_uuid
 * @property ?string $export_id
 * @property ?CarbonInterface $exported_at
 * @property ?CarbonInterface $informed_by_index_at
 * @property ?CarbonInterface $informed_by_staff_at
 * @property ?CarbonInterface $copied_at
 * @property string $status
 * @property ?string $task_group
 * @property ?string $contact_case_uuid
 * @property ?bool $is_source
 * @property ?CarbonInterface $deleted_at
 * @property ?string $dossier_number
 * @property Task\General $general
 * @property Task\Inform $inform
 * @property Task\AlternativeLanguage $altnerative_language
 * @property Task\Circumstances $circumstances
 * @property Task\Symptoms $symptoms
 * @property Task\Test $test
 * @property Task\Vaccination $vaccination
 * @property Task\PersonalDetails $personal_details
 * @property InformStatus $inform_status
 * @property Task\AlternateContact $alternate_contact
 * @property ?string $pseudo_bsn_guid
 * @property Task\Job $job
 * @property ?string $search_date_of_birth
 * @property ?string $search_email
 * @property ?string $search_phone
 * @property int $schema_version
 * @property Task\Immunity $immunity
 *
 * @property Collection<int, Answer> $answers
 * @property EloquentCase $covidCase
 * @property Collection<int, TaskSearch> $search
 *
 * @property ?string $dossierNumber
 * @property ?string $name
 * @property TaskGroup $taskGroup
 */
class EloquentTask extends EloquentBaseModel implements SchemaObject, SchemaProvider, TaskCommon, CountableCaseRelation
{
    use HasFactory;
    use SoftDeletes;
    use CamelCaseAttributes;
    use HasSchema;

    protected $table = 'task';

    protected $casts = [
        'is_source' => 'boolean',
        'task_group' => TaskGroup::class,
        'copied_at' => 'datetime',
        'date_of_last_exposure' => 'datetime',
        'exported_at' => 'datetime',
        'informed_by_index_at' => 'datetime',
        'informed_by_staff_at' => 'datetime',
        'inform_status' => InformStatus::class,
        'communication' => InformedBy::class,
        'category' => ContactCategory::class,
        'label' => Sealed::class . ':' . StorageTerm::SHORT . ',created_at',
        'derived_label' => Sealed::class . ':' . StorageTerm::SHORT . ',created_at',
        'task_context' => Sealed::class . ':' . StorageTerm::SHORT . ',created_at',
        'general' => Task\General::class . ':' . StorageTerm::SHORT . ',created_at',
        'vaccination' => Task\Vaccination::class . ':' . StorageTerm::SHORT . ',created_at',
        'test' => Task\Test::class . ':' . StorageTerm::SHORT . ',created_at',
        'job' => Task\Job::class . ':' . StorageTerm::SHORT . ',created_at',
        'personal_details' => Task\PersonalDetails::class . ':' . StorageTerm::SHORT . ',created_at',
        'symptoms' => Task\Symptoms::class . ':' . StorageTerm::SHORT . ',created_at',
        'circumstances' => Task\Circumstances::class . ':' . StorageTerm::SHORT . ',created_at',
        'alternate_contact' => Task\AlternateContact::class . ':' . StorageTerm::SHORT . ',created_at',
        'inform' => Task\Inform::class . ':' . StorageTerm::SHORT . ',created_at',
        'alternative_language' => Task\AlternativeLanguage::class . ':' . StorageTerm::SHORT . ',created_at',
        'immunity' => Task\Immunity::class . ':' . StorageTerm::SHORT . ',created_at',
    ];

    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class);

        $schema->setUseVersionedClasses(true);
        $schema->setName('Task');
        $schema->setVersionedNamespace('App\\Models\\Versions\\Task');

        $schema->setCurrentVersion(7);

        IdFieldsHelper::addIdFieldsToSchema($schema);

        // Common fields
        $schema->add(TaskGroup::getVersion(1)->createField('taskGroup')->setAllowsNull(false));
        $schema->add(Task\General::getSchema()->getVersion(1)->createField('general')->setAllowsNull(false));
        $schema->add(Task\Job::getSchema()->getVersion(1)->createField('job')->setAllowsNull(false));
        $schema->add(Task\Symptoms::getSchema()->getVersion(1)->createField('symptoms')->setAllowsNull(false));
        $schema->add(Task\Circumstances::getSchema()->getVersion(1)->createField('circumstances')->setAllowsNull(false));
        $schema->add(Task\AlternateContact::getSchema()->getVersion(1)->createField('alternateContact')->setAllowsNull(false));
        $schema->add(Task\AlternativeLanguage::getSchema()->getVersion(1)->createField('alternativeLanguage')->setAllowsNull(false));

        // Fields up to version 1
        $schema->add(Task\Vaccination::getSchema()->getVersion(1)->createField('vaccination'))->setAllowsNull(false)->setMaxVersion(1);
        $schema->add(Task\Vaccination::getSchema()->getVersion(2)->createField('vaccination'))->setAllowsNull(false)->setMinVersion(
            2,
        )->setMaxVersion(3);
        $schema->add(Task\Vaccination::getSchema()->getVersion(3)->createField('vaccination'))->setAllowsNull(false)->setMinVersion(4);

        // Fields up to version 2
        $schema->add(Task\Inform::getSchema()->getVersion(1)->createField('inform')->setAllowsNull(false))->setMaxVersion(2);

        // Fields starting from version 3
        $schema->add(Task\Inform::getSchema()->getVersion(2)->createField('inform')->setAllowsNull(false))->setMinVersion(3)->setMaxVersion(
            6,
        );

        // Fields up to version 4
        $schema->add(Task\PersonalDetails::getSchema()->getVersion(1)->createField('personalDetails')->setAllowsNull(false))
            ->setMaxVersion(4);
        $schema->add(Task\Immunity::getSchema()->getVersion(1)->createField('immunity')->setAllowsNull(false))->setMaxVersion(4);

        // Fields up to version 5
        $schema->add(Task\Test::getSchema()->getVersion(1)->createField('test')->setAllowsNull(false))->setMaxVersion(5);

        // Fields starting from version 5
        $schema->add(Task\PersonalDetails::getSchema()->getVersion(2)->createField('personalDetails')->setAllowsNull(false))
            ->setMinVersion(5);
        $schema->add(Task\Immunity::getSchema()->getVersion(2)->createField('immunity')->setAllowsNull(false))->setMinVersion(5);

        // Fields starting from version 6
        $schema->add(Task\Test::getSchema()->getVersion(2)->createField('test'))->setAllowsNull(false)->setMinVersion(6);

        // Fields starting from version 7
        $schema->add(Task\Inform::getSchema()->getVersion(3)->createField('inform')->setAllowsNull(false))->setMinVersion(7);

        return app(PurposeSpecificationBinder::class)->bind($schema);
    }

    protected static function boot(): void
    {
        parent::boot();

        self::observe(EloquentTaskObserver::class);
        self::observe(EloquentTaskSearchHashObserver::class);
        self::observe(CaseRelationCountObserver::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(EloquentAnswer::class, 'task_uuid');
    }

    public function search(): HasMany
    {
        return $this->hasMany(TaskSearch::class);
    }

    public function answerForQuestionWithIdentifier(string $identifier): ?EloquentAnswer
    {
        return
            $this->answers()
            ->join('question', 'question.uuid', '=', 'answer.question_uuid')
            ->where('question.identifier', '=', $identifier)
            ->limit(1)
            ->first();
    }

    public function contactDetailsAnswer(): ?EloquentAnswer
    {
        return $this->answerForQuestionWithIdentifier('contactdetails');
    }

    public function birthDateAnswer(): ?EloquentAnswer
    {
        return $this->answerForQuestionWithIdentifier('birthdate');
    }

    public function relationshipAnswer(): ?EloquentAnswer
    {
        return $this->answerForQuestionWithIdentifier('relationship');
    }

    public function mentionAnswer(): ?EloquentAnswer
    {
        return $this->answerForQuestionWithIdentifier('mention');
    }

    public function remarksAnswer(): ?EloquentAnswer
    {
        return $this->answerForQuestionWithIdentifier('remarks');
    }

    public function covidCase(): BelongsTo
    {
        return $this->belongsTo(EloquentCase::class, 'case_uuid');
    }

    protected static function booted(): void
    {
        static::deleting(static function ($task): void {
            if (!$task->isForceDeleting()) {
                $task->status = Task::TASK_STATUS_DELETED;

                // SoftDeletes requires us to do an extra save() as it will only
                // update the [updated|deleted]_at columns
                $task->save();
            }
        });
    }

    public function internalReference(): string
    {
        return $this->dossier_number ?? '';
    }

    public function getNameAttribute(): ?string
    {
        if ($this->general instanceof Task\General) {
            return trim($this->general->firstname . ' ' . $this->general->lastname);
        }

        return null;
    }

    public function setTaskGroupAttribute(?TaskGroup $group): void
    {
        $this->attributes['task_group'] = $group?->value;
        if ($group === TaskGroup::contact()) {
            $this->attributes['dossier_number'] = null;
        }
    }

    public function setDossierNumberAttribute(?string $dossierNumber): void
    {
        $this->attributes['dossier_number'] = $this->taskGroup !== TaskGroup::contact() ? $dossierNumber : null;
    }

    public function getCaseUuid(): ?string
    {
        return $this->case_uuid;
    }

    public function getCaseRelationCount(): int
    {
        return $this->covidCase?->tasks()->count() ?? 0;
    }

    public function getConfigKey(): string
    {
        return self::class;
    }
}

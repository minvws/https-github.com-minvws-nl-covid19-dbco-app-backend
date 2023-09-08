<?php

declare(strict_types=1);

namespace Tests;

use App\Dto\Chore\Resource;
use App\Models\CovidCase\Index;
use App\Models\Eloquent\Assignment;
use App\Models\Eloquent\Attachment;
use App\Models\Eloquent\CallToAction;
use App\Models\Eloquent\CaseAssignmentHistory;
use App\Models\Eloquent\CaseLabel;
use App\Models\Eloquent\CaseList;
use App\Models\Eloquent\CaseStatusHistory;
use App\Models\Eloquent\CaseUpdate;
use App\Models\Eloquent\Chore;
use App\Models\Eloquent\Context;
use App\Models\Eloquent\EloquentAnswer;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentCaseLock;
use App\Models\Eloquent\EloquentMessage;
use App\Models\Eloquent\EloquentOrganisation;
use App\Models\Eloquent\EloquentQuestion;
use App\Models\Eloquent\EloquentQuestionnaire;
use App\Models\Eloquent\EloquentSituation;
use App\Models\Eloquent\EloquentTask;
use App\Models\Eloquent\EloquentUser;
use App\Models\Eloquent\ExpertQuestion;
use App\Models\Eloquent\ExpertQuestionAnswer;
use App\Models\Eloquent\Intake;
use App\Models\Eloquent\IntakeContact;
use App\Models\Eloquent\IntakeContactFragment;
use App\Models\Eloquent\IntakeFragment;
use App\Models\Eloquent\Moment;
use App\Models\Eloquent\Note;
use App\Models\Eloquent\OsirisHistory;
use App\Models\Eloquent\OsirisNotification;
use App\Models\Eloquent\Person;
use App\Models\Eloquent\Place;
use App\Models\Eloquent\PlaceCounters;
use App\Models\Eloquent\Section;
use App\Models\Eloquent\TestResult;
use App\Models\Eloquent\Zipcode;
use App\Models\Event as PortalEvent;
use App\Models\Export\ExportClient;
use App\Models\Export\ExportClientPurpose;
use App\Repositories\Intake\IntakeRepository;
use App\Schema\Fragment;
use App\Schema\FragmentModel;
use App\Schema\SchemaObject;
use App\Schema\Types\ArrayType;
use App\Schema\Types\SchemaType;
use App\Services\Chores\CallToActionService;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use DateTimeInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use JsonException;
use MinVWS\Codable\JSONEncoder;
use MinVWS\Codable\ValueTypeMismatchException;
use MinVWS\DBCO\Encryption\Security\EncryptionHelper;
use MinVWS\DBCO\Encryption\Security\StorageTerm;
use MinVWS\DBCO\Enum\Models\BCOStatus;
use MinVWS\DBCO\Enum\Models\TaskGroup;
use MinVWS\Metrics\Models\Export;
use Tests\Helpers\IntakeContactFragmentDataFaker;
use Tests\Helpers\IntakeFragmentDataFaker;
use Webmozart\Assert\Assert;

use function app;
use function array_key_exists;
use function array_merge;
use function collect;
use function count;
use function is_null;
use function sprintf;

trait ModelCreator
{
    protected function createUser(
        array $userAttributes = [],
        ?string $roles = 'user',
        array $organisationAttributes = [],
    ): EloquentUser {
        $user = $this->createUserWithoutOrganisation($userAttributes, $roles);
        $user->organisations()->attach($this->createOrganisation($organisationAttributes));

        return $user;
    }

    protected function createCaseLabel(array $caseLabelAttributes = []): CaseLabel
    {
        /** @var CaseLabel $caseLabel */
        $caseLabel = CaseLabel::factory()->create($caseLabelAttributes);

        return $caseLabel;
    }

    protected function createCaseLabelForOrganisation(
        EloquentOrganisation $organisation,
        array $caseLabelAttributes = [],
        array $pivotAttributes = [],
    ): CaseLabel {
        /** @var CaseLabel $caseLabel */
        $caseLabel = CaseLabel::factory()
            ->hasAttached($organisation, $pivotAttributes, 'organisations')
            ->create($caseLabelAttributes);

        return $caseLabel;
    }

    protected function createUserForOrganisation(
        EloquentOrganisation $organisation,
        array $userAttributes = [],
        ?string $roles = 'user',
    ): EloquentUser {
        $user = EloquentUser::factory($userAttributes)->create(['roles' => $roles]);
        $user->organisations()->attach($organisation);
        return $user;
    }

    protected function createUserWithOrganisation(
        array $userAttributes = [],
        ?string $roles = 'user',
    ): EloquentUser {
        $organisation = $this->createOrganisation();
        $user = EloquentUser::factory($userAttributes)->create(['roles' => $roles]);
        $user->organisations()->attach($organisation);
        return $user;
    }

    protected function createUserWithoutOrganisation(
        array $userAttributes = [],
        ?string $roles = 'user',
    ): EloquentUser {
        return EloquentUser::factory()->create(array_merge([
            'roles' => $roles,
        ], $userAttributes));
    }

    protected function createOrganisation(array $organisationAttributes = []): EloquentOrganisation
    {
        return EloquentOrganisation::factory()->create($organisationAttributes);
    }

    protected function createCase(array $caseAttributes = []): EloquentCase
    {
        return EloquentCase::factory()->create($caseAttributes);
    }

    protected function createCaseExportableToOsiris(array $caseAttributes = []): EloquentCase
    {
        return $this->createCase(array_merge([
            'index' => Index::newInstanceWithVersion(1, function (Index $index): void {
                $index->dateOfBirth = $this->faker->dateTime();
            }),
            'hpzone_number' => null,
            'osiris_number' => null,
        ], $caseAttributes));
    }

    protected function createCaseWithFragments(array $caseAttributes = []): EloquentCase
    {
        return EloquentCase::factory()->withFragments()->create($caseAttributes);
    }

    protected function createCaseForOrganisation(EloquentOrganisation $organisation, array $caseAttributes = []): EloquentCase
    {
        return EloquentCase::factory(array_merge([
            'organisation_uuid' => $organisation->uuid,
        ], $caseAttributes))->create();
    }

    protected function createCaseListForOrganisation(EloquentOrganisation $organisation, array $caseListAttributes = []): CaseList
    {
        return CaseList::factory(array_merge([
            'organisation_uuid' => $organisation->uuid,
        ], $caseListAttributes))->create();
    }

    protected function createCaseForUser(EloquentUser $user, array $caseAttributes = []): EloquentCase
    {
        $organisation = $user->organisations()->first();

        return $this->createCase(array_merge([
            'assigned_user_uuid' => $user->uuid,
            'organisation_uuid' => $organisation->uuid,
            'bco_phase' => $organisation->bcoPhase,
        ], $caseAttributes));
    }

    protected function createCaseList(array $caseListAttributes = []): CaseList
    {
        return CaseList::factory()->create($caseListAttributes);
    }

    protected function createCaseUpdateForCase(
        EloquentCase $case,
        array $updateAttributes = [],
    ): CaseUpdate {
        return $this->createCaseUpdate(array_merge(['case_uuid' => $case->uuid], $updateAttributes));
    }

    protected function createCaseUpdate(array $updateAttributes = []): CaseUpdate
    {
        return CaseUpdate::factory()->create($updateAttributes);
    }

    protected function createTaskForUser(
        EloquentUser $user,
        array $taskAttributes = [],
        array $caseAttributes = [],
    ): EloquentTask {
        $case = $this->createCaseForUser($user, $caseAttributes);
        return $this->createTaskForCase($case, $taskAttributes);
    }

    protected function createTaskForCase(
        EloquentCase $case,
        array $taskAttributes = [],
    ): EloquentTask {
        return $this->createTask(array_merge([
            'case_uuid' => $case->uuid,
            'schema_version' => $case->getSchemaVersion()->getField('tasks')->getExpectedType(ArrayType::class)->getExpectedElementType(
                SchemaType::class,
            )->getSchemaVersion()->getVersion(),
        ], $taskAttributes));
    }

    protected function createTask(array $taskAttributes = []): EloquentTask
    {
        return EloquentTask::factory()->create($taskAttributes);
    }

    protected function createCaseForOrganisationWithTasks(
        EloquentOrganisation $organisation,
        int $contacts,
        int $positiveSources,
        int $symptomaticSources,
        array $caseAttributes = [],
    ): EloquentCase {
        $counts = [
            TaskGroup::contact()->value => $contacts,
            TaskGroup::positiveSource()->value => $positiveSources,
            TaskGroup::symptomaticSource()->value => $symptomaticSources,
        ];

        $case = $this->createCaseForOrganisation($organisation, $caseAttributes);
        $case->save();

        foreach ($counts as $group => $count) {
            for ($i = 0; $i < $count; $i++) {
                $this->createTaskForCase($case, ['task_group' => TaskGroup::from($group)]);
            }
        }

        return $case;
    }

    protected function createCaseWithTasks(
        int $contacts,
        int $positiveSources,
        int $symptomaticSources,
        array $caseAttributes = [],
    ): EloquentCase {
        return $this->createCaseForOrganisationWithTasks(
            $this->createOrganisation(),
            $contacts,
            $positiveSources,
            $symptomaticSources,
            $caseAttributes,
        );
    }

    protected function createQuestion(
        array $questionAttributes = [],
    ): EloquentQuestion {
        return EloquentQuestion::factory()->create(array_merge([
            'questionnaire_uuid' => static function (): EloquentQuestionnaire {
                return EloquentQuestionnaire::factory()->create();
            },
        ], $questionAttributes));
    }

    protected function createQuestionForQuestionnaire(
        EloquentQuestionnaire $questionnaire,
        array $questionAttributes = [],
    ): EloquentQuestion {
        return EloquentQuestion::factory()->create(array_merge([
            'questionnaire_uuid' => $questionnaire->uuid,
        ], $questionAttributes));
    }

    protected function createAnswerForTaskWithQuestion(
        EloquentTask $task,
        EloquentQuestion $question,
        array $answerAttributes = [],
    ): EloquentAnswer {
        return EloquentAnswer::factory()->create(array_merge([
            'task_uuid' => $task->uuid,
            'question_uuid' => $question->uuid,
        ], $answerAttributes));
    }

    /**
     * @param array<Section> $sections
     */
    protected function createContext(
        array $contextAttributes = [],
        array $sections = [],
    ): Context {
        $contextFactory = Context::factory();
        if (count($sections)) {
            $contextFactory = $contextFactory->hasAttached(collect($sections));
        }
        return $contextFactory->create($contextAttributes);
    }

    /**
     * @param array<Section> $sections
     */
    protected function createContextForCase(
        EloquentCase $case,
        array $contextAttributes = [],
        array $sections = [],
    ): Context {
        return $this->createContext(array_merge(['covidcase_uuid' => $case->uuid], $contextAttributes), $sections);
    }

    protected function createSectionForPlace(
        Place $place,
        array $sectionAttributes = [],
    ): Section {
        return Section::factory()->create(array_merge(['place_uuid' => $place->uuid], $sectionAttributes));
    }

    protected function createMomentForContext(
        Context $context,
        array $momentAttributes = [],
    ): Moment {
        return Moment::factory()->create(array_merge(['context_uuid' => $context->uuid], $momentAttributes));
    }

    protected function createPlace(array $placeAttributes = []): Place
    {
        return Place::factory()->create($placeAttributes);
    }

    protected function createPlaceForOrganisation(
        EloquentOrganisation $organisation,
        array $placeAttributes = [],
    ): Place {
        return $this->createPlace(array_merge($placeAttributes, [
            'organisation_uuid' => $organisation->uuid,
        ]));
    }

    protected function createContextForPlace(
        Place $place,
        array $contextAttributes = [],
    ): Context {
        return $this->createContext(array_merge(['place_uuid' => $place->uuid], $contextAttributes));
    }

    protected function createPlaceCountersForPlace(Place $place, array $placeCountersAttributes = []): PlaceCounters
    {
        return PlaceCounters::factory()->create(
            array_merge(['place_uuid' => $place->uuid], $placeCountersAttributes),
        );
    }

    protected function createSituationForPlace(Place $place, array $situationAttributes = []): EloquentSituation
    {
        $situation = EloquentSituation::factory()->create($situationAttributes);
        $place->situations()->attach($situation);

        return $situation;
    }

    protected function createZipcode(array $zipcodeAttributes = []): Zipcode
    {
        return Zipcode::factory()->create($zipcodeAttributes);
    }

    protected function createTestResult(array $attributes = []): TestResult
    {
        return TestResult::factory()->create($attributes);
    }

    protected function createTestResultForCase(
        EloquentCase $eloquentCase,
        array $testResultAttributes = [],
    ): TestResult {
        return $this->createTestResult(array_merge($testResultAttributes, [
            'case_uuid' => $eloquentCase->uuid,
        ]));
    }

    /**
     * Quick sealed store value creation for the short term and with current datetime
     *
     * @param array<string, mixed> $values
     *
     * @throws JsonException
     * @throws ValueTypeMismatchException
     */
    protected function createShortSealedStoreValue(array $values): string
    {
        return $this->createSealedStoreValue(
            StorageTerm::short(),
            CarbonImmutable::now(),
            $values,
        );
    }

    /**
     * Quick sealed store value creation for the long term and with current datetime
     *
     * @param array<string, mixed> $values
     *
     * @throws JsonException
     * @throws ValueTypeMismatchException
     */
    protected function createLongSealedStoreValue(array $values): string
    {
        return $this->createSealedStoreValue(
            StorageTerm::long(),
            CarbonImmutable::now(),
            $values,
        );
    }

    /**
     * @param array<string, mixed> $values
     *
     * @throws JsonException
     * @throws ValueTypeMismatchException
     */
    protected function createSealedStoreValue(
        StorageTerm $storageTerm,
        CarbonInterface $referenceDate,
        array $values,
    ): string {
        $encryptionHelper = $this->app->get(EncryptionHelper::class);
        $encoder = new JSONEncoder();
        $json = $encoder->encode($values);

        return $encryptionHelper->sealStoreValue($json, $storageTerm, $referenceDate);
    }

    protected function createIntakeForOrganisation(
        EloquentOrganisation $organisation,
        array $intakeAttributes = [],
    ): Intake {
        return Intake::factory()
            ->for($organisation, 'organisation')
            ->create($intakeAttributes);
    }

    protected function createIntakeForOrganisationWithFragments(EloquentOrganisation $organisation, array $intakeAttributes = [], array $fragments = []): Intake
    {
        $intake = Intake::factory()
            ->for($organisation, 'organisation')
            ->create($intakeAttributes);

        $intakeFragmentFactory = IntakeFragment::factory();
        foreach ($fragments as $fragmentName => $fragmentData) {
            $intakeFragmentFactory
                ->for($intake, 'intake')
                ->create([
                    'name' => $fragmentName,
                    'data' => $fragmentData,
                    'version' => 1,
                    'received_at' => $intake->received_at,
                ]);
        }

        return $intake;
    }

    protected function createIntakeForOrganisationWithContacts(EloquentOrganisation $organisation, array $intakeAttributes = [], array $contacts = []): Intake
    {
        $intake = Intake::factory()
            ->for($organisation, 'organisation')
            ->create($intakeAttributes);

        /** @var IntakeRepository $intakeRepository */
        $intakeRepository = app(IntakeRepository::class);
        foreach ($contacts as $contact) {
            $intakeContact = $intakeRepository->makeIntakeContactForIntake($intake);
            $intakeRepository->saveIntakeContactForIntake($intakeContact, $intake);

            foreach ($contact as $contactFragmentName => $contactFragmentData) {
                $intakeContactFragment = $intakeRepository->makeIntakeContactFragmentForIntake($intakeContact);
                $intakeContactFragment->name = $contactFragmentName;
                $intakeContactFragment->data = $contactFragmentData;
                $intakeContactFragment->version = 1;
                $intakeRepository->saveIntakeContactFragmentForIntake($intakeContactFragment, $intakeContact);
            }
        }

        return $intake;
    }

    protected function createIntakeForOrganisationWithLabels(
        EloquentOrganisation $organisation,
        array $intakeAttributes = [],
        ?Collection $labels = null,
    ): Intake {
        $intake = Intake::factory()
            ->for($organisation, 'organisation')
            ->create($intakeAttributes);

        if (is_null($labels)) {
            return $intake;
        }

        foreach ($labels as $label) {
            $intake->caseLabels()->attach($label);
        }

        return $intake;
    }

    protected function createIntakeFragmentForIntake(
        Intake $intake,
        string $intakeFragmentType,
        array $intakeFragmentData = [],
    ): IntakeFragment {
        $randomIntakeFragmentData = IntakeFragmentDataFaker::createDataForType($intakeFragmentType);
        $intakeFragmentData = array_merge($randomIntakeFragmentData, $intakeFragmentData);
        $intakeFragmentAttributes = [
            'name' => $intakeFragmentType,
            'version' => 1,
            'data' => $intakeFragmentData,
            'received_at' => $intake->received_at,
        ];
        return IntakeFragment::factory()
            ->for($intake, 'intake')
            ->create($intakeFragmentAttributes);
    }

    protected function createIntakeContactForIntake(
        Intake $intake,
        array $intakeContactAttributes = [],
    ): IntakeContact {
        return IntakeContact::factory()
            ->for($intake)
            ->create($intakeContactAttributes);
    }

    protected function createIntakeContactFragmentForIntakeContact(
        IntakeContact $intakeContact,
        string $intakeContactFragmentType,
        array $intakeContactFragmentData = [],
    ): IntakeContactFragment {
        $randomIntakeFragmentData = IntakeContactFragmentDataFaker::createDataForType($intakeContactFragmentType);

        $intakeContactFragmentData = array_merge($randomIntakeFragmentData, $intakeContactFragmentData);
        $intakeContactFragmentAttributes = [
            'name' => $intakeContactFragmentType,
            'version' => 1,
            'data' => $intakeContactFragmentData,
            'received_at' => $intakeContact->received_at,
        ];

        return IntakeContactFragment::factory()
            ->for($intakeContact, 'intakeContact')
            ->create($intakeContactFragmentAttributes);
    }

    protected function addIntakeFragmentsToIntake(Intake $intake): void
    {
        //Not a complete list of intakeFragments yet
        $intakeFragmentsToCreate = [
            'index',
            'test',
            'symptoms',
            'underlyingSuffering',
            'abroad',
            'vaccination',
            'sourceEnvironments',
            'contacts',
            'job',
        ];
        foreach ($intakeFragmentsToCreate as $intakeFragmentType) {
            $this->createIntakeFragmentForIntake($intake, $intakeFragmentType);
        }
    }

    protected function addIntakeContactFragmentsToIntakeContact(IntakeContact $intakeContact): void
    {
        //Not a complete list of intakeContactFragments yet
        $intakeContactFragmentsToCreate = [
            'general',
        ];
        foreach ($intakeContactFragmentsToCreate as $intakeContactFragmentType) {
            $this->createIntakeContactFragmentForIntakeContact($intakeContact, $intakeContactFragmentType);
        }
    }

    protected function createNoteForCase(
        EloquentCase $case,
        array $noteAttributes = [],
    ): Note {
        return $this->createNote(array_merge([
            'case_uuid' => $case->uuid,
        ], $noteAttributes));
    }

    protected function createNote(array $noteAttributes = []): Note
    {
        return Note::factory()->create($noteAttributes);
    }

    protected function createAssignmentHistoryForCase(
        EloquentCase $case,
        array $assignmentAttributes = [],
    ): CaseAssignmentHistory {
        if (!empty($assignmentAttributes['assigned_case_list_uuid'])) {
            /** @var CaseList $list */
            $list = CaseList::withoutGlobalScopes()->find($assignmentAttributes['assigned_case_list_uuid']);
            $assignmentAttributes['assigned_case_list_name'] = $list->name;
        }

        return $this->createAssignmentHistory(array_merge([
            'covidcase_uuid' => $case->uuid,
        ], $assignmentAttributes));
    }

    protected function createAssignmentHistory(array $assignmentAttributes = []): CaseAssignmentHistory
    {
        return CaseAssignmentHistory::factory()->create($assignmentAttributes);
    }

    protected function createExpertQuestionForCase(
        EloquentCase $case,
        array $questionAttributes = [],
    ): ExpertQuestion {
        return $this->createExpertQuestion(array_merge([
            'case_uuid' => $case->uuid,
            'subject' => $this->faker->text(50),
        ], $questionAttributes));
    }

    protected function createExpertQuestionWithAnswerForCase(
        EloquentCase $case,
        array $questionAttributes = [],
        array $answerAttributes = [],
    ): ExpertQuestion {
        $expertQuestion = $this->createExpertQuestion(array_merge([
            'case_uuid' => $case->uuid,
            'subject' => $this->faker->text(50),
        ], $questionAttributes));

        $this->createExpertQuestionAnswer($expertQuestion, $answerAttributes);

        return $expertQuestion;
    }

    protected function createExpertQuestion(array $questionAttributes = []): ExpertQuestion
    {
        // phpcs:disable Generic.Commenting.Todo.TaskFound -- baseline
        /**
         * TODO: might want to alter this so it won't be magic... for now this will make sure that every test will pass
         */
        // phpcs:enable Generic.Commenting.Todo.TaskFound -- baseline
        if (!isset($questionAttributes['user_uuid']) && isset($questionAttributes['case_uuid'])) {
            $eloquentCase = EloquentCase::where('uuid', $questionAttributes['case_uuid'])->first();
            if ($eloquentCase) {
                $questionAttributes['user_uuid'] = $this->createUserForOrganisation($eloquentCase->organisation)->uuid;
            }
        }

        return ExpertQuestion::factory()->create($questionAttributes);
    }

    protected function createExpertQuestionAnswer(ExpertQuestion $expertQuestion, array $answerAttributes = []): ExpertQuestionAnswer
    {
        return ExpertQuestionAnswer::factory()->create(['expert_question_uuid' => $expertQuestion->uuid, ...$answerAttributes]);
    }

    protected function createMessage(array $messageAttributes = []): EloquentMessage
    {
        return EloquentMessage::factory()->create($messageAttributes);
    }

    protected function createMessageForCase(EloquentCase $case, array $messageAttributes = []): EloquentMessage
    {
        return $this->createMessage(array_merge([
            'case_uuid' => $case->uuid,
        ], $messageAttributes));
    }

    protected function createAttachment(array $attachmentAttributes = []): Attachment
    {
        return Attachment::factory()->create($attachmentAttributes);
    }

    protected function createChore(array $attributes = []): Chore
    {
        return Chore::factory($attributes)->create();
    }

    protected function createChoreForCaseAndOrganisation(EloquentCase $eloquentCase, EloquentOrganisation $eloquentOrganisation, array $attributes = []): Chore
    {
        return Chore::factory(array_merge([
            'resource_id' => $eloquentCase->uuid,
            'resource_type' => $this->getMorphTypeForCase($eloquentCase),
            'organisation_uuid' => $eloquentOrganisation->uuid,
        ], $attributes))->create();
    }

    private function getMorphTypeForCase(EloquentCase $case): string
    {
        return sprintf('covid-case-v%s', $case->getSchemaVersion()->getVersion());
    }

    protected function createChoreForOrganisation(EloquentOrganisation $organisation, array $attributes = []): Chore
    {
        return Chore::factory(array_merge([
            'organisation_uuid' => $organisation->uuid,
        ], $attributes))->create();
    }

    protected function createAssignment(array $attributes = []): Assignment
    {
        return Assignment::factory($attributes)->create();
    }

    protected function createAssignmentForChore(Chore $chore, array $attributes = []): Assignment
    {
        return Assignment::factory(array_merge([
            'chore_uuid' => $chore->uuid,
        ], $attributes))->create();
    }

    protected function createAssignmentWithUserForChore(EloquentUser $user, Chore $chore, array $attributes = []): Assignment
    {
        return $this->createAssignment(array_merge([
            'user_uuid' => $user->uuid,
            'chore_uuid' => $chore->uuid,
        ], $attributes));
    }

    protected function createResourceForCase(?EloquentCase $eloquentCase = null): Resource
    {
        $case = $eloquentCase ?: $this->createCase();
        return new Resource($case->getVersionedResourceType(), $case->uuid);
    }

    protected function createResourceForTask(?EloquentTask $eloquentTask = null): Resource
    {
        return new Resource('task', $eloquentTask?->uuid ?? $this->createTask()->uuid);
    }

    protected function createResourceForOrganisation(?EloquentOrganisation $eloquentOrganisation = null): Resource
    {
        return new Resource('organisation', $eloquentOrganisation?->uuid ?? $this->createOrganisation()->uuid);
    }

    protected function createResourceForCallToAction(?CallToAction $callToAction = null): Resource
    {
        return new Resource(CallToActionService::RESOURCE_TYPE_NAME, $callToAction?->uuid ?? $this->createCallToAction()->uuid);
    }

    protected function createCallToAction(array $attributes = []): CallToAction
    {
        return CallToAction::factory($attributes)->create();
    }

    protected function createPerson(array $attributes = []): Person
    {
        return Person::factory($attributes)->create();
    }

    protected function createExportClient(
        array $attributes = [],
        array $purposes = [],
        array $organisations = [],
    ): ExportClient {
        /** @var ExportClient $exportClient */
        $exportClient = ExportClient::factory($attributes)->create();

        foreach ($purposes as $purpose) {
            /** @var ExportClientPurpose $exportClientPurpose */
            $exportClientPurpose = $exportClient->purposes()->make();
            $exportClientPurpose->purpose = $purpose;
            $exportClientPurpose->save();
        }

        foreach ($organisations as $organisation) {
            $exportClient->organisations()->save($organisation);
        }

        return $exportClient;
    }

    protected function createEvent(array $attributes = []): PortalEvent
    {
        $res = $this->makeEvent($attributes);
        $res->save();
        return $res;
    }

    protected function makeEvent(array $attributes = []): PortalEvent
    {
        $case = array_key_exists('case_uuid', $attributes) ? EloquentCase::findOrFail($attributes['case_uuid']) : $this->createCase();
        $organisationUuid = $case->organisation_uuid;

        $data = [
            'actor' => 'staff',
            'caseUuid' => $case->uuid,
        ];

        $export = [
            'uuid' => Str::uuid(),
            'status' => Export::STATUS_EXPORTED,
            'created_at' => $this->faker->dateTimeBetween('-1 year'),
            'uploaded_at' => $this->faker->dateTimeBetween('-1 year'),
            'exported_at' => $this->faker->dateTimeBetween('-1 year'),
            'filename' => $this->faker->word(),
        ];

        $event = $attributes + [
            'uuid' => Str::uuid()->toString(),
            'type' => 'completed',
            'data' => $data,
            'export_data' => $data,
            'export_uuid' => $export['uuid'],
            'created_at' => $this->faker->dateTimeBetween('-1 year'),
            'organisation_uuid' => $organisationUuid,
        ];

        DB::table('export')->insert($export);

        return PortalEvent::make([
            'uuid' => $event['uuid'],
            'type' => $event['type'],
            'data' => $event['data'],
            'export_data' => $event['export_data'],
            'export_uuid' => $export['uuid'],
            'created_at' => $event['created_at'],
            'organisation_uuid' => $organisationUuid,
        ]);
    }

    protected function createCaseLock(array $attributes = []): EloquentCaseLock
    {
        return EloquentCaseLock::factory($attributes)->create();
    }

    protected function createCaseLockForCase(EloquentCase $eloquentCase, array $attributes = []): EloquentCaseLock
    {
        return $this->createCaseLock(array_merge([
            'case_uuid' => $eloquentCase->uuid,
        ], $attributes));
    }

    protected function createCaseLockForCaseAndUser(EloquentCase $eloquentCase, EloquentUser $eloquentUser, array $attributes = []): EloquentCaseLock
    {
        return $this->createCaseLock(array_merge([
            'case_uuid' => $eloquentCase->uuid,
            'user_uuid' => $eloquentUser->uuid,
        ], $attributes));
    }

    protected function createCaseStatusHistoryWithStatusForCase(
        EloquentCase $case,
        BCOStatus $status,
        DateTimeInterface $changedAt,
    ): CaseStatusHistory {
        return CaseStatusHistory::factory()
            ->for($case, 'case')
            ->state([
                'bco_status' => $status,
                'changed_at' => $changedAt,
            ])
            ->createOneQuietly();
    }

    protected function createOsirisNotificationForCase(EloquentCase $case, array $attributes = []): OsirisNotification
    {
        return OsirisNotification::factory()
            ->for($case, 'case')
            ->create($attributes);
    }

    protected function createOsirisHistoryForCase(EloquentCase $case, array $attributes = []): OsirisHistory
    {
        return OsirisHistory::factory()
            ->for($case, 'case')
            ->create($attributes);
    }

    /**
     * Creates a case and osirisNotification pair. Without giving parameters the OsirisNotification is up to date with
     * the updateAt property of the case.
     */
    protected function createCaseAndOsirisNotification(
        array $caseAttributes = [],
        array $notificationAttributes = [],
    ): EloquentCase {
        $caseAttributes['updated_at'] ??= $this->faker->dateTimeBetween('-2 days');
        $case = $this->createCaseExportableToOsiris($caseAttributes);

        $notificationAttributes['notified_at'] ??= $caseAttributes['updated_at'];
        $this->createOsirisNotificationForCase($case, $notificationAttributes);

        return $case;
    }

    /**
     * @param class-string<Fragment|FragmentModel> $fragment
     */
    protected function createFragment(string $fragment, array $attributes = [], ?int $schemaVersion = null): SchemaObject
    {
        Assert::isAnyOf(
            $fragment,
            [
                Fragment::class,
                FragmentModel::class,
            ],
            'Given string is not valid for a Fragment',
        );

        $schema = $fragment::getSchema();

        if ($schemaVersion !== null) {
            $schema->setCurrentVersion($schemaVersion);
        }

        return $schema->getTestFactory()->make($attributes);
    }
}

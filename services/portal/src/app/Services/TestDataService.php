<?php

declare(strict_types=1);

namespace App\Services;

use App\Dto\OsirisHistory\OsirisHistoryValidationResponse;
use App\Models\Eloquent\CaseAssignmentHistory;
use App\Models\Eloquent\CaseList;
use App\Models\Eloquent\Context;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentMessage;
use App\Models\Eloquent\EloquentOrganisation;
use App\Models\Eloquent\EloquentTask;
use App\Models\Eloquent\EloquentUser;
use App\Models\Eloquent\ExpertQuestion;
use App\Models\Eloquent\Moment;
use App\Models\Eloquent\Note;
use App\Models\Eloquent\OsirisHistory;
use App\Models\Eloquent\TestResult;
use App\Models\OrganisationType;
use App\Services\Osiris\SoapMessage\SoapMessageBuilder;
use Carbon\CarbonInterface;
use Closure;
use Database\Factories\Eloquent\EloquentCaseFactory;
use DateTimeImmutable;
use Exception;
use Faker\Generator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use MinVWS\DBCO\Enum\Models\BCOStatus;
use MinVWS\DBCO\Enum\Models\OsirisHistoryStatus;
use MinVWS\DBCO\Enum\Models\TaskGroup;

use function array_merge;
use function count;
use function rand;

class TestDataService
{
    protected Generator $faker;

    public function __construct(Generator $faker)
    {
        $this->faker = $faker;
    }

    public function createCase(
        EloquentOrganisation $organisation,
        EloquentUser $planner,
        ?EloquentOrganisation $assignedOrganisation = null,
        ?CaseList $assignedCaseList = null,
        ?EloquentUser $assignedUser = null,
    ): EloquentCase {
        $ownerData = [
            'organisation_uuid' => $organisation->uuid,
            'owner' => $planner->uuid,
        ];

        $assignmentData = [
            'assigned_organisation_uuid' => $assignedOrganisation->uuid ?? null,
            'assigned_user_uuid' => $assignedUser->uuid ?? null,
            'assigned_case_list_uuid' => $assignedCaseList->uuid ?? null,
        ];

        /** @var EloquentCaseFactory $caseFactory */
        $caseFactory = EloquentCase::factory();

        /** @var EloquentCase $case */
        $case = $caseFactory
            ->withFragments()
            ->withUpdate()
            ->state(fn(array $attrs) => $attrs['bco_status'] !== BCOStatus::completed() ? $assignmentData : [])
            ->create($ownerData);

        $this->createTasks($case, TaskGroup::contact(), rand(0, 3));
        $this->createCaseNotes($case, $planner, rand(0, 3));
        $this->createAssignmentHistory($case, $assignmentData);
        $this->createMessages($case, $assignedUser, rand(0, 3));
        if ($assignedUser !== null) {
            $this->createExpertQuestions($case, $assignedUser, rand(0, 3));
        }
        $this->createTestResults($case, rand(0, 3));
        $this->createCaseContextAndMoments($case);
        $this->populateOsirisHistory($case);

        return $case;
    }

    public function createTasks(EloquentCase $case, TaskGroup $group, int $amount): void
    {
        EloquentTask::factory()
            ->count($amount)
            ->create([
                'task_group' => $group,
                'case_uuid' => $case->uuid,
            ]);
    }

    private function createCaseNotes(EloquentCase $case, EloquentUser $user, int $amount): void
    {
        if ($amount === 0) {
            return;
        }

        Note::factory()
            ->count($amount)
            ->create([
                'case_uuid' => $case->uuid,
                'case_created_at' => $case->created_at,
                'user_uuid' => $user->uuid,
                'user_name' => $user->name,
                'organisation_name' => $user->getOrganisation()->name ?? '',
            ]);
    }

    private function createAssignmentHistory(EloquentCase $case, array $assignmentData): void
    {
        CaseAssignmentHistory::factory()
            ->create(array_merge([
                'covidcase_uuid' => $case->uuid,
            ], $assignmentData));
    }

    private function createMessages(EloquentCase $case, ?EloquentUser $user, int $amount): void
    {
        EloquentMessage::factory()
            ->count($amount)
            ->create([
                'case_uuid' => $case->uuid,
                'case_created_at' => $case->created_at,
                'user_uuid' => $user ? $user->uuid : null,
            ]);
    }

    private function createExpertQuestions(EloquentCase $case, EloquentUser $user, int $amount): void
    {
        if ($amount === 0) {
            return;
        }

        ExpertQuestion::factory()
            ->count($amount)
            ->create([
                'case_uuid' => $case->uuid,
                'case_created_at' => $case->created_at,
                'user_uuid' => $user->uuid,
            ]);
    }

    private function createTestResults(EloquentCase $case, int $amount): void
    {
        if ($amount === 0) {
            return;
        }

        TestResult::factory()
            ->count($amount)
            ->create([
                'case_uuid' => $case->uuid,
            ]);
    }

    /**
     * Work around to satisfy ?DateTimeInterface
     */
    public function getDateTimeImmutable(CarbonInterface $date): ?DateTimeImmutable
    {
        $dateTimeImmutable = DateTimeImmutable::createFromFormat('!Y-m-d', $date->format('Y-m-d'));

        return $dateTimeImmutable ?: null;
    }

    /**
     * @return Collection<int, EloquentOrganisation>
     *
     * @throws Exception
     */
    public function getOrganisations(array $organisationUuids): Collection
    {
        /** @var Collection<EloquentOrganisation> $organisations */
        $organisations = EloquentOrganisation::whereIn('uuid', $organisationUuids)
            ->withoutGlobalScopes()
            ->get();

        if ($organisations->isEmpty()) {
            $organisations = EloquentOrganisation::query()
                ->where('type', OrganisationType::regionalGGD()->value)
                ->get();
        }

        return $organisations;
    }

    public function getOrCreateUser(EloquentOrganisation $organisation, string $role): EloquentUser
    {
        /** @var EloquentUser|null $user */
        $user = $organisation->users()->where('roles', '=', $role)->first();
        if ($user !== null) {
            return $user;
        }

        /** @var EloquentUser $user */
        $user = EloquentUser::factory()->create(['roles' => $role]);
        $user->organisations()->save($organisation);

        return $user;
    }

    public function getOrCreateDefaultCaseList(EloquentOrganisation $organisation): CaseList
    {
        /** @var CaseList|null $caseList */
        $caseList = $organisation->caseLists()->where('is_default', '=', 1)->withoutGlobalScopes()->first();

        if ($caseList !== null) {
            return $caseList;
        }

        /** @var CaseList $caseList */
        $caseList = CaseList::factory()->create([
            'is_default' => 1,
            'is_queue' => 1,
            'name' => 'Wachtrij',
            'organisation_uuid' => $organisation->uuid,
        ]);

        return $caseList;
    }

    public function getOrCreateCaseLists(EloquentOrganisation $organisation, int $amount): array
    {
        $current = $organisation
            ->caseLists()
            ->where('is_default', '<>', 1)
            ->withoutGlobalScopes()
            ->count();

        if ($current < $amount) {
            CaseList::factory()
                ->count($amount - $current)
                ->create([
                    'is_default' => false,
                    'is_queue' => false,
                    'organisation_uuid' => $organisation->uuid,
                ]);
        }

        return $organisation
            ->caseLists()
            ->where('is_default', '<>', 1)
            ->limit($amount)
            ->withoutGlobalScopes()
            ->get()
            ->all();
    }

    public function createCases(
        EloquentOrganisation $organisation,
        int $amount,
        EloquentUser $planner,
        EloquentUser $user,
        CaseList $defaultCaseList,
        array $caseLists,
        ?Closure $onCaseCreated,
    ): void {
        $outsourceOrganisations = $organisation->outsourceOrganisations;
        for ($i = 0; $i < $amount; $i++) {
            $assignedOrganisation = !$outsourceOrganisations->isEmpty() && rand(0, 100) < 10 ? $outsourceOrganisations->random() : null;
            $assignedCaseList = $assignedOrganisation === null && rand(0, 100) < 10 ? $defaultCaseList : null;
            if ($assignedOrganisation === null && $assignedCaseList === null) {
                $assignedCaseList = rand(0, 100) < 10 ? $caseLists[rand(0, count($caseLists) - 1)] : null;
            }
            $assignedUser = $assignedOrganisation === null && rand(0, 100) < 10 ? $user : null;

            $case = $this->createCase($organisation, $planner, $assignedOrganisation, $assignedCaseList, $assignedUser);

            if ($onCaseCreated !== null) {
                $onCaseCreated($case);
            }
        }
    }

    public function createCaseContextAndMoments(
        EloquentCase $case,
    ): void {
        for ($i = 1; $i < 11; $i++) {
            /** @var Context $context */
            $context = Context::factory()
                ->create([
                    'covidcase_uuid' => $case->uuid,
                ]);

            Moment::factory()
                ->count(rand(15, 30)) // GGDs average is 18.
                ->create([
                    'context_uuid' => $context->uuid,
                ]);
        }
    }

    private function createOsirisHistoryForCase(EloquentCase $case, array $attributes): Model
    {
        return OsirisHistory::factory()
            ->for($case, 'case')
            ->create($attributes);
    }

    private function populateOsirisHistory(EloquentCase $case): void
    {
        $this->createOsirisHistoryForCase($case, [
            'status' => OsirisHistoryStatus::success(),
            'osiris_status' => SoapMessageBuilder::NOTIFICATION_STATUS_DEFINITIVE,
            'osiris_validation_response' => new OsirisHistoryValidationResponse(),
        ]);

        $this->createOsirisHistoryForCase($case, [
            'status' => OsirisHistoryStatus::success(),
            'osiris_status' => SoapMessageBuilder::NOTIFICATION_STATUS_DEFINITIVE,
            'osiris_validation_response' => new OsirisHistoryValidationResponse(warnings: (array) $this->faker->sentences()),
        ]);

        $this->createOsirisHistoryForCase($case, [
            'status' => OsirisHistoryStatus::failed(),
            'osiris_status' => SoapMessageBuilder::NOTIFICATION_STATUS_DEFINITIVE,
            'osiris_validation_response' => new OsirisHistoryValidationResponse(errors: (array) $this->faker->sentences()),
        ]);

        $this->createOsirisHistoryForCase($case, [
            'status' => OsirisHistoryStatus::validation(),
            'osiris_status' => SoapMessageBuilder::NOTIFICATION_STATUS_DEFINITIVE,
            'osiris_validation_response' => new OsirisHistoryValidationResponse(
                errors: (array) $this->faker->sentences(),
                warnings: (array) $this->faker->sentences(),
            ),
        ]);
    }
}

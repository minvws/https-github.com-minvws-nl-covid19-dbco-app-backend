<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Helpers\Config;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentUser;
use App\Models\Eloquent\ExpertQuestion;
use App\Models\Eloquent\ExpertQuestionAnswer;
use App\Models\ExpertQuestion\ListOptions;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use MinVWS\DBCO\Enum\Models\ExpertQuestionSort;
use MinVWS\DBCO\Enum\Models\ExpertQuestionType;

use function collect;

class ExpertQuestionRepository
{
    public function createExpertQuestion(
        EloquentCase $case,
        EloquentUser $user,
        ExpertQuestionType $type,
        string $subject,
        ?string $phone,
        string $questionString,
    ): ExpertQuestion {
        return ExpertQuestion::create([
            'case_created_at' => $case->createdAt,
            'case_uuid' => $case->uuid,
            'user_uuid' => $user->uuid,
            'type' => $type,
            'subject' => $subject,
            'phone' => $phone,
            'question' => $questionString,
        ]);
    }

    public function listExpertQuestions(ListOptions $options): LengthAwarePaginator
    {
        /** @var EloquentUser $user */
        $user = Auth::user();
        $days = Config::integer('misc.supervision.questions_recent_days');

        if ($options->type === null) {
            throw new InvalidArgumentException('type cannot be null');
        }
        $type = ExpertQuestionType::from($options->type);

        /** @var Builder $query */
        $query = ExpertQuestion::where('type', $type)
            ->select('expert_question.*')
            ->leftJoin('covidcase', 'expert_question.case_uuid', '=', 'covidcase.uuid')
            ->whereRelation(
                'user.organisations',
                'uuid',
                $user->getOrganisation() !== null ? $user->getOrganisation()->uuid : '',
            )
            ->where(static function (Builder $query): void {
                $query->whereNull('expert_question.assigned_user_uuid')
                    ->orWhere('expert_question.assigned_user_uuid', Auth::id());
            })
            ->whereDoesntHave('answer')
            ->whereDate('expert_question.created_at', '>=', CarbonImmutable::now()->subDays($days))
            ->whereNull('covidcase.deleted_at');

        if ($options->sort === ExpertQuestionSort::status()->value) {
            $query->orderBy('expert_question.assigned_user_uuid', $options->order ?? 'asc')
                ->orderBy('expert_question.created_at', 'asc');
        } elseif ($options->sort === ExpertQuestionSort::createdAt()->value) {
            $query->orderBy('expert_question.created_at', $options->order ?? 'asc');
        } else {
            $query->orderBy('expert_question.assigned_user_uuid', 'asc');
            $query->orderBy('expert_question.created_at', 'asc');
        }

        return $query->paginate($options->perPage, ['*'], '', $options->page);
    }

    public function getExpertQuestionById(string $questionUuid): ?ExpertQuestion
    {
        return ExpertQuestion::find($questionUuid);
    }

    public function assignExpertQuestionToExpertUser(
        ExpertQuestion $expertQuestion,
        string $expertUserUuid,
    ): ExpertQuestion {
        $expertUser = EloquentUser::where('uuid', $expertUserUuid)->first();
        $expertQuestion->assignedUser()->associate($expertUser);
        $expertQuestion->save();

        return $expertQuestion;
    }

    public function getExpertQuestionByTypeAndCaseId(string $caseId, ExpertQuestionType $type): ?ExpertQuestion
    {
        $days = Config::integer('misc.supervision.questions_recent_days');

        // return oldest unanswered, if it exists
        $unanswered = ExpertQuestion::with('case')
            ->whereHas('case', static function ($query) use ($caseId): void {
                $query->where(static function (Builder $query) use ($caseId): void {
                    $query->where('case_id', $caseId);
                    $query->orWhere('hpzone_number', $caseId); // temp? fix, check & test in DBCO-4286
                });
            })
            ->where('type', $type->value)
            ->whereDoesntHave('answer')
            ->whereDate('created_at', '>=', CarbonImmutable::now()->subDays($days))
            ->orderBy('created_at', 'ASC')
            ->first();

        if ($unanswered) {
            return $unanswered;
        }

        // return newest answered
        return ExpertQuestion::with('case')
            ->whereHas('case', static function ($query) use ($caseId): void {
                $query->where(static function (Builder $query) use ($caseId): void {
                    $query->where('case_id', $caseId);
                    $query->orWhere('hpzone_number', $caseId); // temp? fix, check & test in DBCO-4286
                });
            })
            ->where('type', $type->value)
            ->whereDate('created_at', '>=', CarbonImmutable::now()->subDays($days))
            ->orderBy('created_at', 'DESC')
            ->first();
    }

    public function getExpertQuestionsByTypeAndCaseUuid(string $caseUuid, ExpertQuestionType $type): Collection
    {
        return ExpertQuestion::where('case_uuid', $caseUuid)
            ->where('type', $type->value)
            ->orderBy('created_at', 'ASC')
            ->get();
    }

    public function unassignExpertQuestion(ExpertQuestion $expertQuestion): ExpertQuestion
    {
        $expertQuestion->assignedUser()->dissociate();
        $expertQuestion->save();

        return $expertQuestion;
    }

    public function unassignAllAssignedExpertQuestions(): int
    {
        $assignedQuestions = ExpertQuestion::whereNotNull('assigned_user_uuid');
        $unassignedCount = $assignedQuestions->count();
        DB::transaction(function () use ($assignedQuestions): void {
            $assignedQuestions->each(function ($item, $key): void {
                $this->unassignExpertQuestion($item);
            });
        });
        return $unassignedCount;
    }

    public function attachAnswer(ExpertQuestion $expertQuestion, ExpertQuestionAnswer $answer): ExpertQuestion
    {
        $expertQuestion->answer()->save($answer);

        $expertQuestion->assigned_user_uuid = null;
        $expertQuestion->save();

        return $expertQuestion;
    }

    public function hasQuestionForCaseAndTypesForExpertUser(
        string $caseUuid,
        string $expertUserUuid,
        array $types,
    ): bool {
        return ExpertQuestion::where('case_uuid', $caseUuid)
            ->where('assigned_user_uuid', $expertUserUuid)
            ->whereIn('type', collect($types)->map(static fn(ExpertQuestionType $type) => $type->value))
            ->count() > 0;
    }
}

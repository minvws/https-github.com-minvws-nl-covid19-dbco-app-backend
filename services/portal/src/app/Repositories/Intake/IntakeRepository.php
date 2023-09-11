<?php

declare(strict_types=1);

namespace App\Repositories\Intake;

use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\Intake;
use App\Models\Eloquent\IntakeContact;
use App\Models\Eloquent\IntakeContactFragment;
use App\Models\Eloquent\IntakeFragment;
use App\Models\Intake\ListOptions;
use App\Scopes\IntakeOrganisationAuthScope;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use LogicException;

use function is_array;
use function is_string;

class IntakeRepository
{
    public function saveIntake(Intake $intake): void
    {
        $intake->save();
    }

    public function makeIntakeFragmentForIntake(Intake $intake): IntakeFragment
    {
        /** @var IntakeFragment $intakeFragment */
        $intakeFragment = $intake->fragments()->make();
        $intakeFragment->intake()->associate($intake);
        $intakeFragment->received_at = $intake->received_at;
        return $intakeFragment;
    }

    public function saveIntakeFragmentForIntake(IntakeFragment $intakeFragment, Intake $intake): void
    {
        $intake->fragments()->save($intakeFragment);
    }

    public function makeIntakeContactForIntake(Intake $intake): IntakeContact
    {
        /** @var IntakeContact $intakeContact */
        $intakeContact = $intake->contacts()->make();
        $intakeContact->intake()->associate($intake);
        $intakeContact->received_at = $intake->received_at;
        return $intakeContact;
    }

    public function saveIntakeContactForIntake(IntakeContact $intakeContact, Intake $intake): void
    {
        $intake->contacts()->save($intakeContact);
    }

    public function makeIntakeContactFragmentForIntake(IntakeContact $intakeContact): IntakeContactFragment
    {
        /** @var IntakeContactFragment $intakeContactFragment */
        $intakeContactFragment = $intakeContact->fragments()->make();
        $intakeContactFragment->intakeContact()->associate($intakeContact);
        $intakeContactFragment->received_at = $intakeContact->received_at;
        return $intakeContactFragment;
    }

    public function saveIntakeContactFragmentForIntake(IntakeContactFragment $intakeContactFragment, IntakeContact $intakeContact): void
    {
        $intakeContact->fragments()->save($intakeContactFragment);
    }

    public function deleteIntake(Intake $intake): void
    {
        $intake->delete();
    }

    public function listIntakes(ListOptions $options): Paginator
    {
        $query = Intake::query()->select(['intake.*']);

        $this->addViewOrderBy($query, $options);
        $this->addFilters($query, $options);

        if ($options->includeTotal) {
            return $query->paginate($options->perPage, ['*'], 'page', $options->page);
        }

        return $query->simplePaginate($options->perPage, ['*'], 'page', $options->page);
    }

    public function getIntakesCount(): ?int
    {
        return Intake::query()->count('intake.uuid');
    }

    private function addViewOrderBy(Builder $query, ListOptions $options): void
    {
        if ($options->sort !== null) {
            $sortOrder = $options->order ?? 'asc';

            switch ($options->sort) {
                case "dateOfSymptomOnset":
                    $query->orderBy('intake.date_of_symptom_onset', $sortOrder);
                    break;
                case "dateOfTest":
                    $query->orderBy('intake.date_of_test', $sortOrder);
                    break;
                case "cat1Count":
                    $query->orderBy('intake.cat1_count', $sortOrder);
                    break;
                case "estimatedCat2Count":
                    $query->orderBy('intake.estimated_cat2_count', $sortOrder);
                    break;
            }

            return;
        }

        $query->orderByDesc('intake.received_at');
    }

    private function addFilters(Builder $query, ListOptions $options): void
    {
        foreach ($options->filter as $filterName => $filterValue) {
            switch ($filterName) {
                case 'caseLabels':
                    $this->addCaseLabelFilter($query, $filterValue);
                    break;
            }
        }
    }

    /**
     * Add filter for one or multiple caseLabels
     *
     * @param mixed $filterValue uuid of caseLabel, either single uuid or array of uuid's
     */
    private function addCaseLabelFilter(Builder $query, mixed $filterValue): void
    {
        if (empty($filterValue) || (!is_array($filterValue) && !is_string($filterValue))) {
            return;
        }

        $query
            ->join('intake_label as il', 'il.intake_uuid', '=', 'intake.uuid')
            ->join('case_label as cl', 'cl.uuid', '=', 'il.label_uuid');

        if (is_array($filterValue)) {
            $query->where(static function ($query) use ($filterValue): void {
                foreach ($filterValue as $value) {
                    $query->orWhere('cl.uuid', '=', $value);
                }
            });
        } else {
            $query->where('cl.uuid', '=', $filterValue);
        }
    }

    public function findCaseByIntake(Intake $intake): ?EloquentCase
    {
        if ($intake->identifier_type !== 'testMonsterNumber') {
            return null;
        }

        // only retrieve the uuid first so that the index can be used in the most efficient way
        $result = EloquentCase::query()
            ->select(['uuid'])
            ->where('test_monster_number', '=', $intake->identifier)
            ->get();

        if ($result->count() === 1) {
            $caseUuid = $result->pluck('uuid')->first();
            $case = is_string($caseUuid) ? EloquentCase::query()->find($caseUuid) : null;
            return $case instanceof EloquentCase ? $case : null;
        }

        if ($result->count() > 1) {
            throw new LogicException('Multiple cases found when matching Intake with cases');
        }

        // Not found
        return null;
    }

    public function findIntakeByCase(EloquentCase $case): ?Intake
    {
        $result = Intake::query()
            ->withoutGlobalScope(IntakeOrganisationAuthScope::class)
            ->where('identifier', '=', $case->testMonsterNumber)
            ->where('identifier_type', '=', 'testMonsterNumber')
            ->get();

        if ($result->count() === 1) {
            return $result->first();
        }

        if ($result->count() > 1) {
            throw new LogicException('Multiple intakes found when matching with cases');
        }

        // Not found
        return null;
    }
}
